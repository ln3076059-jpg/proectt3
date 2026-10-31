<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Disease;
use App\Models\DiseaseSymptom;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\ServiceSymptom;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    // Hiển thị trang chủ với danh sách khoa, bác sĩ và dịch vụ
    public function welcome()
    {
        $departments = Department::all();
        $doctors = Doctor::with(['user', 'department'])->where('is_active', true)->get();
        $services = Service::with('department')->get();

        return view('welcome', compact('departments', 'doctors', 'services'));
    }

    // Trang tư vấn theo triệu chứng (hỗ trợ ngôn ngữ tự nhiên tiếng Việt, cảnh báo cấp cứu & nhóm đối tượng)
    public function advisorPage(Request $request)
    {
        // 1. Lấy triệu chứng và nhóm đối tượng từ request
        $symptomQuery = trim((string) ($request->input('symptom') ?: $request->input('q')));
        $patientGroup = (string) $request->input('patient_group', 'adult');

        // 2. Lấy danh sách tất cả khoa
        $departments = Department::all();

        // 3. Gọi method xử lý tìm kiếm thông minh
        $suggestions = $this->getSuggestionsBySymptoms($symptomQuery, null, $patientGroup);

        // 4. Phát hiện dấu hiệu cấp cứu (Red Flags)
        $redFlagList = [
            'khó thở', 'đau tức ngực', 'đau thắt ngực', 'bất tỉnh', 'ngất xỉu',
            'co giật', 'nôn ra máu', 'ho ra máu', 'đột quỵ', 'liệt nửa người',
            'méo miệng', 'sốt cao trên 39', 'chảy máu không cầm', 'sốc phản vệ', 'tím tái',
        ];
        $matchedRedFlags = collect($redFlagList)->filter(function ($flag) use ($symptomQuery) {
            return $symptomQuery !== '' && mb_stripos($symptomQuery, $flag, 0, 'UTF-8') !== false;
        })->values();

        // 5. Trả về view với dữ liệu
        return view('home.advisor.index', array_merge([
            'symptomQuery' => $symptomQuery,
            'patientGroup' => $patientGroup,
            'hasRedFlag' => $matchedRedFlags->isNotEmpty(),
            'matchedRedFlags' => $matchedRedFlags,
            'selectedDepartmentId' => null,
            'departments' => $departments,
        ], $suggestions));
    }

    // Trang danh sách bác sĩ với tìm kiếm, lọc theo chuyên khoa và lịch trực thực tế 14 ngày tới
    public function doctorsPage(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $selectedDepartmentId = $request->input('department_id');
        $departments = Department::all();

        $doctors = Doctor::with(['user', 'department'])
            ->where('is_active', true)
            ->when($selectedDepartmentId, function ($query) use ($selectedDepartmentId) {
                $query->where('department_id', $selectedDepartmentId);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->whereHas('user', function ($uQuery) use ($q) {
                        $uQuery->where('name', 'like', "%{$q}%");
                    })->orWhere('specialization', 'like', "%{$q}%");
                });
            })
            ->paginate(8)
            ->appends(request()->query());

        // Lấy các ngày có ca trống trong 14 ngày tới cho các bác sĩ đang hiển thị (loại bỏ các khung giờ đã qua trong ngày hôm nay)
        $doctorIds = $doctors->getCollection()->pluck('id');
        $upcomingDatesByDoctor = [];
        if ($doctorIds->isNotEmpty()) {
            $slots = \App\Models\TimeSlot::whereIn('doctor_id', $doctorIds)
                ->whereDate('date', '>=', now()->toDateString())
                ->whereDate('date', '<=', now()->addDays(14)->toDateString())
                ->where('is_active', true)
                ->where('status', \App\Models\TimeSlot::STATUS_AVAILABLE)
                ->whereRaw('booked_count < capacity')
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get()
                ->reject(fn ($s) => $s->start_date_time->isPast());

            foreach ($slots->groupBy('doctor_id') as $docId => $docSlots) {
                $upcomingDatesByDoctor[$docId] = $docSlots
                    ->groupBy(fn ($s) => \Carbon\Carbon::parse($s->date)->toDateString())
                    ->map(function ($daySlots, $dateStr) {
                        return [
                            'date' => $dateStr,
                            'formatted_date' => \Carbon\Carbon::parse($dateStr)->format('d/m'),
                            'remaining_slots' => $daySlots->count(),
                            'first_time' => substr((string) $daySlots->first()->start_time, 0, 5),
                        ];
                    })
                    ->take(5)
                    ->values()
                    ->all();
            }
        }

        return view('home.doctors.index', compact(
            'doctors',
            'q',
            'departments',
            'selectedDepartmentId',
            'upcomingDatesByDoctor'
        ));
    }

    // Trang danh sách chuyên khoa với tìm kiếm theo tên, mô tả hoặc triệu chứng
    public function departmentsPage(Request $request)
    {
        $query = trim((string) $request->input('symptom'));
        $departments = Department::query();

        // Lọc chỉ lấy khoa đang hoạt động nếu có cột is_active
        if (Schema::hasColumn('departments', 'is_active')) {
            $departments->where('is_active', true);
        }

        // Tìm kiếm theo tên khoa, mô tả hoặc triệu chứng bệnh/dịch vụ thuộc khoa
        if ($query !== '') {
            $departments->where(function ($sub) use ($query) {
                $sub->where('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('description', 'LIKE', '%' . $query . '%')
                    ->orWhereHas('services.symptoms', fn($sq) => $sq->where('symptom_name', 'LIKE', '%' . $query . '%'))
                    ->orWhereHas('diseases.symptoms', fn($dq) => $dq->where('symptom_name', 'LIKE', '%' . $query . '%'));
            });
        }

        $departments = $departments->paginate(6)->appends(request()->query());
        $services = Service::with('department')->get();

        return view('home.departments.index', compact('departments', 'services', 'query'));
    }

    // Trang danh sách dịch vụ với tìm kiếm và lọc theo khoa
    public function servicesPage(Request $request)
    {
        // 1. Lấy triệu chứng và khoa từ request
        $symptomQuery = $request->input('symptom', $request->input('q'));
        $selectedDepartmentId = $request->input('department_id');
        $sort = (string) $request->input('sort', '');
        $departments = Department::all();

        // 2. Gọi method xử lý tìm kiếm (CÓ lọc theo khoa)
        $suggestions = $this->getSuggestionsBySymptoms($symptomQuery, $selectedDepartmentId ? (int) $selectedDepartmentId : null);

        // 3. Lấy gợi ý triệu chứng theo khoa
        $symptomSuggestions = $this->getSymptomSuggestionsByDepartment($selectedDepartmentId ? (int) $selectedDepartmentId : null);

        // 4. Nếu có triệu chứng và có kết quả → dùng kết quả gợi ý
        if ($symptomQuery && $suggestions['suggestedServices']->isNotEmpty()) {
            $services = $suggestions['suggestedServices'];
            if ($sort === 'price_asc') {
                $services = $services->sortBy('price')->values();
            } elseif ($sort === 'price_desc') {
                $services = $services->sortByDesc('price')->values();
            }
        } else {
            $services = Service::with(['department', 'symptoms'])
                ->when($selectedDepartmentId, function ($query) use ($selectedDepartmentId) {
                    $query->where('department_id', $selectedDepartmentId);
                })
                ->when($sort === 'price_asc', fn ($q) => $q->orderBy('price', 'asc'))
                ->when($sort === 'price_desc', fn ($q) => $q->orderBy('price', 'desc'))
                ->paginate(6)
                ->appends(request()->query());
        }

        // 5. Trả về view với dữ liệu
        return view('home.services.index', array_merge([
            'services' => $services,
            'symptomQuery' => $symptomQuery,
            'sort' => $sort,
            'departments' => $departments,
            'selectedDepartmentId' => $selectedDepartmentId,
            'symptomSuggestions' => $symptomSuggestions,
        ], $suggestions));
    }

    // Lấy các gợi ý (dịch vụ, bệnh, khoa, bác sĩ) dựa trên triệu chứng & nhóm đối tượng
    private function getSuggestionsBySymptoms(?string $symptomQuery, ?int $selectedDepartmentId, ?string $patientGroup = null): array
    {
        // 1. Khởi tạo các collections rỗng để lưu kết quả
        $suggestedServices = collect();
        $suggestedDepartments = collect();
        $suggestedDoctors = collect();
        $suggestedDiseases = collect();

        // 2. Kiểm tra nếu không có triệu chứng → trả về rỗng
        if (empty($symptomQuery)) {
            return [
                'suggestedServices' => $suggestedServices,
                'suggestedDepartments' => $suggestedDepartments,
                'suggestedDoctors' => $suggestedDoctors,
                'suggestedDiseases' => $suggestedDiseases,
            ];
        }

        // 3. Chuẩn hóa từ khóa (hỗ trợ cả câu văn tự nhiên tiếng Việt) và tìm các ID khớp
        $keywords = $this->normalizeKeywords($symptomQuery);

        // 4. Tìm các ID dịch vụ và bệnh khớp
        $serviceIds = $this->findMatchingServiceIds($keywords);
        $diseaseIds = $this->findMatchingDiseaseIds($keywords);

        // 5. Lấy danh sách dịch vụ gợi ý
        if ($serviceIds->isNotEmpty()) {
            $suggestedServices = $this->getSuggestedServices($serviceIds, $keywords, $selectedDepartmentId);
            $departmentIds = $suggestedServices->pluck('department_id')->unique();

            if ($departmentIds->isNotEmpty()) {
                $suggestedDepartments = Department::whereIn('id', $departmentIds)->get();
                $suggestedDoctors = Doctor::with(['user', 'department'])
                    ->where('is_active', true)
                    ->whereIn('department_id', $departmentIds)
                    ->get();
            }
        }

        // 7. Lấy danh sách bệnh gợi ý
        if ($diseaseIds->isNotEmpty()) {
            $suggestedDiseases = $this->getSuggestedDiseases($diseaseIds, $keywords, $selectedDepartmentId);
        }

        // 8. Ưu tiên khoa từ bệnh, nếu không có thì dùng khoa từ dịch vụ
        if ($suggestedDiseases->isNotEmpty()) {
            $departmentIds = $suggestedDiseases->pluck('department_id')->filter()->unique()->values();
        } elseif ($suggestedServices->isNotEmpty()) {
            $departmentIds = $suggestedServices->pluck('department_id')->filter()->unique()->values();
        } else {
            $departmentIds = collect();
        }

        // Hỗ trợ ưu tiên chuyên khoa theo nhóm đối tượng (Trẻ em -> Nhi, Thai phụ -> Sản)
        if ($patientGroup === 'child') {
            $pedsDeptIds = Department::where('name', 'LIKE', '%Nhi%')->pluck('id');
            $departmentIds = $pedsDeptIds->merge($departmentIds)->unique()->values();
        } elseif ($patientGroup === 'pregnant') {
            $obgynDeptIds = Department::where('name', 'LIKE', '%Sản%')->orWhere('name', 'LIKE', '%Phụ%')->pluck('id');
            $departmentIds = $obgynDeptIds->merge($departmentIds)->unique()->values();
        }

        // 9. Lấy lại danh sách khoa và bác sĩ theo department IDs
        if ($departmentIds->isNotEmpty()) {
            $suggestedDepartments = Department::whereIn('id', $departmentIds)->take(4)->get();
            $suggestedDoctors = Doctor::with(['user', 'department'])
                ->where('is_active', true)
                ->whereIn('department_id', $departmentIds)
                ->take(8)
                ->get();

            // Lọc lại dịch vụ theo khoa đã gợi ý
            if ($suggestedServices->isNotEmpty()) {
                $suggestedServices = $suggestedServices
                    ->filter(fn($service) => $departmentIds->contains($service->department_id))
                    ->sortByDesc('matched_symptoms_count')
                    ->take(6)
                    ->values();
            }
        }

        return [
            'suggestedServices' => $suggestedServices,
            'suggestedDepartments' => $suggestedDepartments,
            'suggestedDoctors' => $suggestedDoctors,
            'suggestedDiseases' => $suggestedDiseases,
        ];
    }

    // Chuẩn hóa từ khóa tìm kiếm: hỗ trợ tách cả dấu phẩy lẫn câu văn tự nhiên tiếng Việt
    private function normalizeKeywords(string $symptomQuery): Collection
    {
        // Tách theo dấu câu và các hư từ nối trong câu mô tả triệu chứng tự nhiên
        $parts = preg_split(
            '/[,;\.\n\r]+|\b(?:và|kèm theo|kèm|hoặc|với|bị|có triệu chứng|triệu chứng|tôi|em|cháu|bé|mình|đang|gặp|cảm thấy|thấy|nhiều ngày|mấy ngày|hôm nay|từ hôm qua)\b/iu',
            $symptomQuery
        ) ?: [];

        $tokens = collect($parts)
            ->map(fn($keyword) => trim((string) $keyword))
            ->filter(fn($keyword) => $keyword !== '' && mb_strlen($keyword) >= 2)
            ->values();

        // Đối chiếu ngược các cụm triệu chứng đã có trong CSDL nếu người dùng viết câu liền mạch
        $knownSymptoms = ServiceSymptom::pluck('symptom_name')
            ->merge(DiseaseSymptom::pluck('symptom_name'))
            ->filter()
            ->unique();

        foreach ($knownSymptoms as $known) {
            $cleanKnown = trim((string) $known);
            if ($cleanKnown !== '' && mb_strlen($cleanKnown) >= 2 && mb_stripos($symptomQuery, $cleanKnown, 0, 'UTF-8') !== false) {
                $tokens->push($cleanKnown);
            }
        }

        return $tokens
            ->map(fn($k) => trim($k))
            ->filter(fn($k) => mb_strlen($k) >= 2)
            ->unique(fn($k) => mb_strtolower($k, 'UTF-8'))
            ->values();
    }

    // Tìm các ID dịch vụ khớp với từ khóa triệu chứng hoặc tên/mô tả dịch vụ
    private function findMatchingServiceIds(Collection $keywords): Collection
    {
        $serviceIds = collect();

        foreach ($keywords as $keyword) {
            $kw = mb_strtolower($keyword, 'UTF-8');
            $matchedSymptomIds = ServiceSymptom::whereRaw('LOWER(symptom_name) LIKE ?', ['%' . $kw . '%'])
                ->pluck('service_id');
            $matchedDirectIds = Service::whereRaw('LOWER(name) LIKE ?', ['%' . $kw . '%'])
                ->orWhereRaw('LOWER(description) LIKE ?', ['%' . $kw . '%'])
                ->pluck('id');
            $serviceIds = $serviceIds->merge($matchedSymptomIds)->merge($matchedDirectIds);
        }

        return $serviceIds->unique();
    }

    // Tìm các ID bệnh khớp với từ khóa triệu chứng hoặc tên/mô tả bệnh
    private function findMatchingDiseaseIds(Collection $keywords): Collection
    {
        $diseaseIds = collect();

        foreach ($keywords as $keyword) {
            $kw = mb_strtolower($keyword, 'UTF-8');
            $matchedSymptomIds = DiseaseSymptom::whereRaw('LOWER(symptom_name) LIKE ?', ['%' . $kw . '%'])
                ->pluck('disease_id');
            $matchedDirectIds = Disease::whereRaw('LOWER(name) LIKE ?', ['%' . $kw . '%'])
                ->orWhereRaw('LOWER(description) LIKE ?', ['%' . $kw . '%'])
                ->pluck('id');
            $diseaseIds = $diseaseIds->merge($matchedSymptomIds)->merge($matchedDirectIds);
        }

        return $diseaseIds->unique();
    }

    // Tính số lượng triệu chứng khớp với từ khóa
    private function calculateMatchedSymptomsCount(Collection $symptoms, Collection $keywords): int
    {
        return $symptoms->filter(function ($symptom) use ($keywords) {
            // Kiểm tra nếu triệu chứng khớp với từ khóa
            foreach ($keywords as $keyword) {
                if (mb_stripos((string) $symptom->symptom_name, (string) $keyword, 0, 'UTF-8') !== false) {
                    return true; //trả về true nếu khớp
                }
            }

            return false; //trả về false nếu không khớp          
        })->count();
    }

    // Lấy danh sách dịch vụ gợi ý dựa trên triệu chứng hoặc từ khóa
    private function getSuggestedServices(Collection $serviceIds, Collection $keywords, ?int $selectedDepartmentId): Collection
    {
        return Service::with(['department', 'symptoms'])
            ->whereIn('id', $serviceIds)
            ->when($selectedDepartmentId, function ($query) use ($selectedDepartmentId) {
                $query->where('department_id', $selectedDepartmentId);
            })
            ->get()
            ->map(function ($service) use ($keywords) {
                $score = $this->calculateMatchedSymptomsCount($service->symptoms, $keywords);
                foreach ($keywords as $keyword) {
                    if (mb_stripos($service->name ?? '', (string) $keyword, 0, 'UTF-8') !== false || mb_stripos($service->description ?? '', (string) $keyword, 0, 'UTF-8') !== false) {
                        $score += 1;
                    }
                }
                $service->matched_symptoms_count = $score;

                return $service;
            })
            ->filter(fn($service) => ($service->matched_symptoms_count ?? 0) > 0)
            ->sortByDesc('matched_symptoms_count')
            ->take(6)
            ->values();
    }

    // Lấy danh sách bệnh gợi ý dựa trên triệu chứng hoặc từ khóa
    private function getSuggestedDiseases(Collection $diseaseIds, Collection $keywords, ?int $selectedDepartmentId): Collection
    {
        return Disease::with(['department', 'symptoms'])
            ->whereIn('id', $diseaseIds)
            ->when($selectedDepartmentId, function ($query) use ($selectedDepartmentId) {
                $query->where('department_id', $selectedDepartmentId);
            })
            ->get()
            ->map(function ($disease) use ($keywords) {
                $score = $this->calculateMatchedSymptomsCount($disease->symptoms, $keywords);
                foreach ($keywords as $keyword) {
                    if (mb_stripos($disease->name ?? '', (string) $keyword, 0, 'UTF-8') !== false || mb_stripos($disease->description ?? '', (string) $keyword, 0, 'UTF-8') !== false) {
                        $score += 1;
                    }
                }
                $disease->matched_symptoms_count = $score;

                return $disease;
            })
            ->filter(fn($disease) => ($disease->matched_symptoms_count ?? 0) > 0)
            ->sortByDesc('matched_symptoms_count')
            ->take(6)
            ->values();
    }

    // Lấy danh sách triệu chứng gợi ý theo khoa
    private function getSymptomSuggestionsByDepartment(?int $departmentId): Collection
    {
        // Kiểm tra nếu không có khoa → trả về rỗng
        if (!$departmentId) {
            return collect();
        }

        // Lấy danh sách service IDs thuộc khoa
        $serviceIds = Service::where('department_id', $departmentId)->pluck('id'); //lấy service_id

        // Kiểm tra nếu không có dịch vụ → trả về rỗng
        if ($serviceIds->isEmpty()) {
            return collect();
        }

        // Lấy triệu chứng từ các dịch vụ và nhóm lại
        return ServiceSymptom::whereIn('service_id', $serviceIds) //tìm trong bảng service_symptoms với service_id
            ->select('symptom_name')
            ->get() //lấy triệu chứng theo service_id
            ->groupBy(function ($item) {
                return mb_strtolower(trim($item->symptom_name));
            }) //nhóm triệu chứng theo symptom_name
            ->map(function ($group) {
                return [
                    'name' => $group->first()->symptom_name, //lấy symptom_name đầu tiên
                    'count' => $group->count(), //đếm số lần xuất hiện
                ];
            }) //chuyển về mảng
            ->sortByDesc('count') //sắp xếp theo số lần xuất hiện giảm dần
            ->values() //chuyển về mảng
            ->take(12); //lấy 12 triệu chứng đầu tiên
    }

    // Kiểm tra xem FULLTEXT index có tồn tại cho cột cụ thể không
    private function hasFulltextIndex(string $table, string $column): bool
    {
        try {
            $indexes = DB::select(
                "SHOW INDEX FROM {$table} WHERE Column_name = ? AND Index_type = 'FULLTEXT'",
                [$column]
            );

            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
