<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Disease;
use App\Models\DiseaseSymptom;
use App\Models\Service;
use App\Models\ServiceSymptom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIChatController extends Controller
{
    public function chat(Request $request)
    {
        $message = trim((string) $request->input('message', ''));

        if ($message === '') {
            return response()->json([
                'reply' => 'Vui lòng mô tả triệu chứng bạn đang gặp.',
                'suggestions' => [
                    'diseases' => [],
                    'departments' => [],
                    'services' => [],
                ],
            ], 422);
        }

        // 0️⃣ SÀNG LỌC DẤU HIỆU CẤP CỨU NGUY HIỂM (Emergency Red-Flags Triage)
        $redFlagList = [
            'khó thở', 'đau tức ngực', 'đau thắt ngực', 'bất tỉnh', 'ngất xỉu',
            'co giật', 'nôn ra máu', 'ho ra máu', 'đột quỵ', 'liệt nửa người',
            'méo miệng', 'sốt cao trên 39', 'chảy máu không cầm', 'sốc phản vệ', 'tím tái',
        ];

        $matchedRedFlags = collect($redFlagList)->filter(function ($flag) use ($message) {
            return mb_stripos($message, $flag, 0, 'UTF-8') !== false;
        })->values();

        $isEmergency = $matchedRedFlags->isNotEmpty();

        // 1️⃣ ƯU TIÊN GỢI Ý TỪ DB TRIỆU CHỨNG
        $keywords = $this->extractKeywords($message);
        $diseases = $this->suggestDiseasesFromSymptomsDb($keywords);
        $services = $this->suggestServicesFromSymptomsDb($keywords);

        // 2️⃣ Nếu DB không match gì thì fallback sang AI
        if ($diseases->isEmpty() && $services->isEmpty()) {
            $aiDiseases = $this->askAIForDiseases($message);

            if (empty($aiDiseases)) {
                $fallbackReply = $isEmergency
                    ? '⚠️ CẢNH BÁO Y TẾ KHẨN CẤP: Triệu chứng có dấu hiệu cấp cứu (' . $matchedRedFlags->implode(', ') . '). Vui lòng gọi ngay Cấp cứu 115 hoặc đến viện ngay lập tức!'
                    : 'Hệ thống chưa xác định được bệnh phù hợp. Vui lòng mô tả rõ hơn triệu chứng.';

                return response()->json([
                    'reply' => $fallbackReply,
                    'is_emergency' => $isEmergency,
                    'emergency_flags' => $matchedRedFlags,
                    'emergency_hotline' => '115 / 024 3853 5522',
                    'suggestions' => [
                        'diseases' => [],
                        'departments' => [],
                        'services' => [],
                    ],
                ]);
            }

            $diseases = Disease::with('department')
                ->where(function ($q) use ($aiDiseases) {
                    foreach ($aiDiseases as $name) {
                        $q->orWhere('name', 'like', '%'.$name.'%');
                    }
                })
                ->get();
        }

        // 3️⃣ GỢI Ý KHOA
        // - Ưu tiên khoa từ bệnh match.
        // - Nếu không có bệnh nhưng có dịch vụ match thì suy ra khoa từ dịch vụ.
        if ($diseases->isNotEmpty()) {
            $departments = $diseases
                ->pluck('department')
                ->filter()
                ->unique('id')
                ->values()
                ->map(fn ($d) => $d->only(['id', 'name']));
        } else {
            $departmentIds = $services->pluck('department_id')->filter()->unique()->values();
            $departments = $departmentIds->isEmpty()
                ? collect()
                : Department::whereIn('id', $departmentIds)->get(['id', 'name']);
        }

        // 4️⃣ GỢI Ý DỊCH VỤ (đảm bảo liên quan)
        if ($diseases->isNotEmpty() && $departments->isNotEmpty()) {
            $deptIds = $departments->pluck('id');

            $services = $services
                ->whereIn('department_id', $deptIds)
                ->values();

            // Nếu không có dịch vụ match trực tiếp theo triệu chứng trong các khoa này thì fallback theo khoa
            if ($services->isEmpty()) {
                $services = Service::whereIn('department_id', $deptIds)
                    ->limit(6)
                    ->get(['id', 'name', 'price', 'department_id']);
            }
        } else {
            $services = $services->values();
        }

        // Bảo vệ dữ liệu sức khỏe người dùng: Không ghi nguyên văn thông tin bệnh lý nhạy cảm vào log thông thường
        Log::info('AIChat mapped symptoms summary', [
            'message_length' => mb_strlen($message),
            'is_emergency' => $isEmergency,
            'keywords_count' => count($keywords),
            'db_diseases_count' => $diseases->count(),
            'departments_count' => $departments->count(),
            'services_count' => $services->count(),
        ]);

        $replyMessage = $isEmergency
            ? '⚠️ CẢNH BÁO Y TẾ KHẨN CẤP: Triệu chứng của bạn có dấu hiệu nguy hiểm (' . $matchedRedFlags->implode(', ') . '). Vui lòng gọi ngay 115 hoặc đến cơ sở y tế gần nhất ngay lập tức, không nên chờ đợi đặt lịch trực tuyến!'
            : 'Dựa trên triệu chứng bạn cung cấp, hệ thống gợi ý một số bệnh nghi ngờ. Kết quả chỉ mang tính tham khảo, bạn nên đến phòng khám để được bác sĩ chẩn đoán chính xác.';

        return response()->json([
            'reply' => $replyMessage,
            'is_emergency' => $isEmergency,
            'emergency_flags' => $matchedRedFlags,
            'emergency_hotline' => '115 / 024 3853 5522',
            'suggestions' => [
                'diseases' => $diseases->map(fn ($d) => [
                    'id' => $d->id,
                    'name' => $d->name,
                    'department' => $d->department ? $d->department->name : null,
                ]),
                'departments' => $departments,
                'services' => $services,
            ],
        ]);
    }

    private function extractKeywords(string $message): array
    {
        $normalized = mb_strtolower($message);
        $normalized = preg_replace('/[^\pL\pN\s,;\.\-]+/u', ' ', $normalized);
        $parts = preg_split('/[\s,;\.\-]+/u', (string) $normalized);

        return collect($parts)
            ->filter(fn ($p) => is_string($p) && mb_strlen(trim($p)) >= 2)
            ->map(fn ($p) => trim($p))
            ->unique()
            ->take(12)
            ->values()
            ->all();
    }

    private function suggestDiseasesFromSymptomsDb(array $keywords)
    {
        if (empty($keywords)) {
            return collect();
        }

        $q = DiseaseSymptom::query()
            ->select('disease_id', DB::raw('COUNT(*) as score'))
            ->where(function ($w) use ($keywords) {
                foreach ($keywords as $kw) {
                    $w->orWhere('symptom_name', 'like', '%'.$kw.'%');
                }
            })
            ->groupBy('disease_id')
            ->orderByDesc('score')
            ->limit(8)
            ->get();

        $ids = $q->pluck('disease_id')->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $diseases = Disease::with('department')
            ->whereIn('id', $ids)
            ->get();

        $scores = $q->pluck('score', 'disease_id');

        return $diseases
            ->sortByDesc(fn ($d) => (int) ($scores[$d->id] ?? 0))
            ->values();
    }

    private function suggestServicesFromSymptomsDb(array $keywords)
    {
        if (empty($keywords)) {
            return collect();
        }

        $q = ServiceSymptom::query()
            ->select('service_id', DB::raw('COUNT(*) as score'))
            ->where(function ($w) use ($keywords) {
                foreach ($keywords as $kw) {
                    $w->orWhere('symptom_name', 'like', '%'.$kw.'%');
                }
            })
            ->groupBy('service_id')
            ->orderByDesc('score')
            ->limit(6)
            ->get();

        $ids = $q->pluck('service_id')->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $services = Service::whereIn('id', $ids)
            ->get(['id', 'name', 'price', 'department_id']);

        $scores = $q->pluck('score', 'service_id');

        return $services
            ->sortByDesc(fn ($s) => (int) ($scores[$s->id] ?? 0))
            ->values();
    }

    // Gọi AI để lấy danh sách bệnh
    private function askAIForDiseases(string $message): array
    {
        try {
            $apiKey = (string) config('services.groq.key');

            if (trim($apiKey) === '') {
                Log::warning('Groq API key missing (services.groq.key). Set GROQ_API_KEY in .env');

                return [];
            }

            $res = Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Bạn là trợ lý y tế. '.
                                'Chỉ trả về JSON: {"diseases":["Tên bệnh"]}. '.
                                'Không giải thích. Không chẩn đoán.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $message,
                        ],
                    ],
                ]);

            if (! $res->successful()) {
                Log::warning('AI disease API failed', [
                    'status' => $res->status(),
                    'body' => $res->body(),
                ]);

                return [];
            }

            $content = (string) data_get($res->json(), 'choices.0.message.content', '');
            Log::info('AI disease raw content', ['content' => $content]);

            // Một số model trả JSON kèm markdown code fences (```json ... ```)
            $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
            $content = preg_replace('/\s*```\s*$/', '', $content);
            $content = trim($content);

            $json = json_decode($content, true);

            if (! is_array($json)) {
                Log::warning('AI disease content not valid JSON', ['content' => $content]);

                return [];
            }

            return collect($json['diseases'] ?? [])
                ->filter()
                ->unique()
                ->take(5)
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::error('AI disease suggestion failed', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
