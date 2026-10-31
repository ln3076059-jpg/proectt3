@extends('home.frontend')

@section('title', 'Tư vấn Triệu chứng Lâm sàng · Phòng khám 125 Thái Thịnh')

@section('content')
<!-- Page Title -->
<div class="page-title" data-aos="fade">
  <nav class="breadcrumbs">
    <div class="container">
      <ol>
        <li><a href="{{ url('/') }}">Trang chủ</a></li>
        <li class="current">Tư vấn Triệu chứng</li>
      </ol>
    </div>
  </nav>
</div><!-- End Page Title -->

<section class="tiki-hero-section py-4">
    <div class="ts-frame">
        <div class="p-3 rounded-3 mb-4 d-flex align-items-center gap-3" style="background-color: var(--status-danger-bg); color: var(--status-danger-ink); border: 1px solid #FECACA;">
            <i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0"></i>
            <div style="font-size: 0.88rem;">
                <strong>Lưu ý cấp cứu y tế:</strong> Hệ thống tra cứu giúp định hướng chuyên khoa ban đầu, không thay thế chẩn đoán trực tiếp của bác sĩ. Nếu có dấu hiệu khó thở dữ dội, đau thắt ngực hoặc bất tỉnh, vui lòng gọi ngay <a href="tel:115" class="fw-bold" style="color: var(--status-danger-ink);">Cấp cứu 115</a> hoặc Tổng đài <a href="tel:19001234" class="fw-bold" style="color: var(--status-danger-ink);">1900 1234</a>.
            </div>
        </div>

        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <span class="tiki-section-eyebrow">
                    <i class="bi bi-clipboard2-pulse"></i>
                    <span>Hệ thống Phân loại Triệu chứng Lâm sàng</span>
                </span>
                <h1 class="tiki-hero-title mb-2">
                    Tra cứu triệu chứng &amp; Gợi ý đúng chuyên khoa
                </h1>
                <p class="tiki-section-desc mb-3">
                    Chọn hoặc nhập các triệu chứng bạn đang gặp phải để hệ thống đối chiếu bệnh lý liên quan, đề xuất đúng chuyên khoa, bác sĩ phụ trách và gói khám phù hợp.
                </p>
                <div class="rounded-3 overflow-hidden" style="border: 1px solid var(--border-color); aspect-ratio: 16 / 9;">
                    <img src="{{ asset('image/clinic/doctor-female-ent.jpg') }}" class="w-100 h-100" style="object-fit: cover; object-position: top center;" alt="Tư vấn định hướng chuyên khoa tại 125 Thái Thịnh">
                </div>
            </div>

            <div class="col-lg-6">
                <div class="tiki-search-card p-4">
                    <form action="{{ route('advisor.index') }}" method="GET" id="advisor-form">
                        <input type="hidden" name="patient_group" id="patientGroupInput" value="{{ $patientGroup ?? 'adult' }}">

                        <label class="tiki-form-label mb-2">1. Đối tượng người bệnh cần tư vấn:</label>
                        <div class="d-flex flex-wrap gap-2 mb-3" id="patientGroupContainer">
                            @php
                                $groups = [
                                    'adult' => ['label' => 'Người lớn', 'icon' => 'bi-person'],
                                    'child' => ['label' => 'Trẻ em (< 15 tuổi)', 'icon' => 'bi-emoji-smile'],
                                    'pregnant' => ['label' => 'Thai phụ / Sản khoa', 'icon' => 'bi-heart-pulse'],
                                    'elderly' => ['label' => 'Người cao tuổi', 'icon' => 'bi-person-check'],
                                ];
                                $currentGroup = $patientGroup ?? 'adult';
                            @endphp
                            @foreach($groups as $gKey => $gMeta)
                                <button type="button"
                                        class="ts-filter-pill py-1 px-3 patient-group-btn {{ $currentGroup === $gKey ? 'is-active' : '' }}"
                                        style="font-size: 0.82rem;"
                                        data-group="{{ $gKey }}">
                                    <i class="bi {{ $gMeta['icon'] }}"></i>
                                    <span>{{ $gMeta['label'] }}</span>
                                </button>
                            @endforeach
                        </div>

                        <label for="symptomInput" class="tiki-form-label">2. Mô tả triệu chứng tự nhiên hoặc chọn bên dưới</label>
                        <div class="d-flex gap-2 mb-2">
                            <input type="text"
                                   name="symptom"
                                   id="symptomInput"
                                   value="{{ $symptomQuery ?? '' }}"
                                   class="tiki-form-control w-100"
                                   placeholder="Nhập tự nhiên: Tôi bị đau đầu kèm sốt cao và buồn nôn..."
                                   autofocus>
                            <button type="submit" class="ts-btn flex-shrink-0">
                                <i class="bi bi-search"></i>
                                <span>Phân tích</span>
                            </button>
                        </div>

                        {{-- Live Red-Flag Emergency Triage Alert --}}
                        <div id="liveRedFlagAlert" class="p-3 rounded-3 mb-3 {{ ($hasRedFlag ?? false) ? '' : 'd-none' }}" style="background-color: #FEF2F2; color: #991B1B; border: 1.5px solid #EF4444;">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-shield-exclamation fs-5 text-danger flex-shrink-0 mt-1"></i>
                                <div style="font-size: 0.85rem;">
                                    <strong class="d-block text-uppercase">Cảnh báo Dấu hiệu Nguy cấp (Triage Cấp độ Đỏ):</strong>
                                    <span>Hệ thống phát hiện triệu chứng nguy hiểm (<strong id="redFlagMatchedText">{{ isset($matchedRedFlags) && $matchedRedFlags->isNotEmpty() ? $matchedRedFlags->implode(', ') : 'Khó thở / Đau tức ngực' }}</strong>). Khuyến cáo người bệnh đến ngay Khoa Cấp cứu gần nhất hoặc gọi <a href="tel:115" class="fw-bold text-danger text-decoration-underline">115</a> thay vì chờ lịch hẹn thông thường.</span>
                                </div>
                            </div>
                        </div>

                        <p class="ts-small fw-semibold mb-2">Chọn nhanh triệu chứng thường gặp (Click để thêm/bớt):</p>
                        <div class="d-flex flex-wrap gap-2" id="symptomChipContainer">
                            @foreach(['Sốt cao', 'Đau đầu', 'Ho khan', 'Đau rát họng', 'Khó thở', 'Đau dạ dày', 'Ợ chua', 'Buồn nôn', 'Chóng mặt', 'Đau mỏi vai gáy', 'Đau lưng', 'Mẩn ngứa', 'Mất ngủ', 'Đau tức ngực'] as $chip)
                                <button type="button"
                                        class="ts-filter-pill py-1 px-3 symptom-chip-btn"
                                        style="font-size: 0.82rem;"
                                        data-chip="{{ $chip }}">
                                    {{ $chip }}
                                </button>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@if($symptomQuery ?? null)
    @php
        $hasResults = (isset($suggestedDiseases) && $suggestedDiseases->isNotEmpty()) ||
                      (isset($suggestedDepartments) && $suggestedDepartments->isNotEmpty()) ||
                      (isset($suggestedDoctors) && $suggestedDoctors->isNotEmpty()) ||
                      (isset($suggestedServices) && $suggestedServices->isNotEmpty());
        $bookingNote = 'Triệu chứng tra cứu: ' . $symptomQuery;
    @endphp
    <section class="tiki-section">
        <div class="ts-frame">
            @if(!$hasResults)
                <div class="ts-surface-card p-5 text-center">
                    <h2 class="h5 fw-bold mb-2">Chưa tìm thấy chẩn đoán khớp trực tiếp với "{{ $symptomQuery }}"</h2>
                    <p class="mb-4 ts-small">Bạn có thể đặt lịch khám Nội tổng quát để bác sĩ thăm khám lâm sàng trực tiếp.</p>
                    <a href="{{ route('appointments.create', ['note' => $bookingNote]) }}" class="ts-btn">
                        <i class="bi bi-calendar2-plus"></i>
                        <span>Đặt lịch khám Đa khoa kèm triệu chứng này</span>
                    </a>
                </div>
            @else
                @if(isset($suggestedDepartments) && $suggestedDepartments->isNotEmpty())
                    <div class="mb-5">
                        <h2 class="tiki-section-title mb-3">Chuyên khoa đề xuất ưu tiên</h2>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($suggestedDepartments as $dept)
                                <a href="{{ route('appointments.create', ['department_id' => $dept->id, 'note' => $bookingNote]) }}" class="ts-btn">
                                    <i class="bi bi-hospital"></i>
                                    <span>{{ $dept->name }} · Đặt lịch ngay</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(isset($suggestedDoctors) && $suggestedDoctors->isNotEmpty())
                    <div class="mb-5">
                        <h2 class="tiki-section-title mb-3">Đội ngũ Bác sĩ chuyên khoa phù hợp</h2>
                        <div class="row g-4">
                            @foreach($suggestedDoctors->take(4) as $doc)
                                @php
                                    $docName = $doc->user->name ?? 'BS.CKII Chuyên khoa';
                                    $docDept = $doc->department->name ?? 'Đa khoa';
                                    $docAvatar = !empty($doc->avatar) ? asset('storage/'.$doc->avatar) : asset('image/clinic/doctor-male-senior.jpg');
                                @endphp
                                <div class="col-md-6 col-lg-3">
                                    <div class="tiki-item-card p-3 h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex align-items-center gap-3 mb-2">
                                                <img src="{{ $docAvatar }}" alt="{{ $docName }}" class="rounded-3 object-fit-cover" style="width: 52px; height: 60px; border: 1px solid var(--border-color);" onerror="this.onerror=null;this.src='{{ asset('image/clinic/doctor-male-senior.jpg') }}';">
                                                <div>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary mb-1">{{ $docDept }}</span>
                                                    <h3 class="h6 fw-bold mb-0">{{ $docName }}</h3>
                                                </div>
                                            </div>
                                            <p class="ts-small mb-3" style="color: var(--ink-secondary);">
                                                {{ \Illuminate\Support\Str::limit($doc->specialization ?? 'Bác sĩ điều trị chuyên sâu tại 125 Thái Thịnh', 75) }}
                                            </p>
                                        </div>
                                        <a href="{{ route('appointments.create', ['doctor_id' => $doc->id, 'department_id' => $doc->department_id, 'note' => $bookingNote]) }}" class="ts-btn ts-btn-sm w-100 justify-content-center">
                                            <i class="bi bi-calendar2-plus"></i>
                                            <span>Đặt lịch với Bác sĩ</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(isset($suggestedDiseases) && $suggestedDiseases->isNotEmpty())
                    <div class="mb-5">
                        <h2 class="tiki-section-title mb-3">Bệnh lý lâm sàng có thể liên quan</h2>
                        <div class="row g-4">
                            @foreach($suggestedDiseases as $disease)
                                <div class="col-md-6">
                                    <div class="tiki-item-card p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h3 class="tiki-card-title mb-0">{{ $disease->name }}</h3>
                                            @if($disease->department)
                                                <span class="tiki-badge tiki-badge-primary">{{ $disease->department->name }}</span>
                                            @endif
                                        </div>
                                        @if(!empty($disease->description))
                                            <p class="tiki-card-desc mb-3">{{ \Illuminate\Support\Str::limit($disease->description, 150) }}</p>
                                        @endif
                                        @if($disease->department_id)
                                            <div class="mt-auto pt-2" style="border-top: 1px dashed var(--border-color);">
                                                <a href="{{ route('appointments.create', ['department_id' => $disease->department_id, 'note' => $bookingNote . ' (Nghi ngờ: ' . $disease->name . ')']) }}" class="ts-link-action">
                                                    <span>Đặt khám chuyên khoa này →</span>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(isset($suggestedServices) && $suggestedServices->isNotEmpty())
                    <div>
                        <h2 class="tiki-section-title mb-3">Gói khám &amp; Xét nghiệm đề xuất</h2>
                        <div class="row g-4">
                            @foreach($suggestedServices as $service)
                                <div class="col-md-6 col-lg-4">
                                    <div class="tiki-item-card p-4">
                                        <span class="badge bg-primary bg-opacity-10 text-primary align-self-start mb-2">
                                            {{ $service->department->name ?? 'Đa khoa' }}
                                        </span>
                                        <h3 class="tiki-card-title">{{ $service->name }}</h3>
                                        <p class="tiki-card-desc">Đặt lịch khám kèm triệu chứng đã phân tích để bác sĩ chuẩn bị trước hồ sơ.</p>
                                        <div class="tiki-card-footer">
                                            <div class="tiki-price-block">
                                                <span class="tiki-price-label">Giá niêm yết</span>
                                                <span class="tiki-price-value ts-num">{{ number_format($service->price ?? 300000, 0, ',', '.') }}đ</span>
                                            </div>
                                            <a href="{{ route('appointments.create', ['department_id' => $service->department_id, 'service_id' => $service->id, 'note' => $bookingNote]) }}" class="ts-btn ts-btn-sm">
                                                <i class="bi bi-calendar2-plus"></i>
                                                <span>Đặt khám</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </section>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const symptomInput = document.getElementById('symptomInput');
    const chips = document.querySelectorAll('.symptom-chip-btn');
    const groupBtns = document.querySelectorAll('.patient-group-btn');
    const groupInput = document.getElementById('patientGroupInput');
    const redFlagAlert = document.getElementById('liveRedFlagAlert');
    const redFlagText = document.getElementById('redFlagMatchedText');

    const redFlagKeywords = [
        'khó thở', 'đau tức ngực', 'đau thắt ngực', 'bất tỉnh', 'ngất xỉu',
        'co giật', 'nôn ra máu', 'ho ra máu', 'đột quỵ', 'liệt nửa người',
        'méo miệng', 'sốt cao trên 39', 'chảy máu không cầm', 'sốc phản vệ', 'tím tái'
    ];

    groupBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            groupBtns.forEach(function (b) { b.classList.remove('is-active'); });
            this.classList.add('is-active');
            if (groupInput) {
                groupInput.value = this.getAttribute('data-group') || 'adult';
            }
        });
    });

    function getNormalizedTokens() {
        if (!symptomInput) return [];
        return symptomInput.value.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
    }

    function checkRedFlagsLive() {
        if (!symptomInput || !redFlagAlert) return;
        const val = symptomInput.value.toLowerCase();
        const matched = redFlagKeywords.filter(function (kw) {
            return val.indexOf(kw) !== -1;
        });
        if (matched.length > 0) {
            redFlagAlert.classList.remove('d-none');
            if (redFlagText) redFlagText.textContent = matched.join(', ');
        } else {
            redFlagAlert.classList.add('d-none');
        }
    }

    function syncChipStates() {
        const rawLower = symptomInput ? symptomInput.value.toLowerCase() : '';
        chips.forEach(function (btn) {
            const chipText = btn.getAttribute('data-chip');
            const isSelected = rawLower.indexOf(chipText.toLowerCase()) !== -1;
            btn.classList.toggle('is-active', isSelected);
        });
        checkRedFlagsLive();
    }

    chips.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const chipText = this.getAttribute('data-chip');
            let tokens = getNormalizedTokens();
            const lowerTokens = tokens.map(function (t) { return t.toLowerCase(); });
            const idx = lowerTokens.indexOf(chipText.toLowerCase());
            if (idx > -1) {
                tokens.splice(idx, 1);
            } else {
                tokens.push(chipText);
            }
            symptomInput.value = tokens.join(', ');
            syncChipStates();
            symptomInput.focus();
        });
    });

    if (symptomInput) {
        symptomInput.addEventListener('input', syncChipStates);
        syncChipStates();
    }
});
</script>
@endpush
@endsection
