@extends('home.frontend')

@section('title', 'Bảng giá Dịch vụ & Gói khám Niêm yết · Phòng khám 125 Thái Thịnh')

@section('content')
<!-- Page Title -->
<div class="page-title" data-aos="fade">
  <nav class="breadcrumbs">
    <div class="container">
      <ol>
        <li><a href="{{ url('/') }}">Trang chủ</a></li>
        <li class="current">Dịch vụ &amp; Bảng giá</li>
      </ol>
    </div>
  </nav>
</div><!-- End Page Title -->

<section class="tiki-hero-section py-4">
    <div class="ts-frame">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <span class="tiki-section-eyebrow">
                    <i class="bi bi-receipt-cutoff"></i>
                    <span>Niêm yết công khai · Hỗ trợ BHYT</span>
                </span>
                <h1 class="tiki-hero-title mb-2">
                    Bảng giá dịch vụ &amp; Gói khám chuyên khoa
                </h1>
                <p class="tiki-section-desc">
                    Tra cứu chi phí khám lâm sàng, xét nghiệm và chẩn đoán hình ảnh công khai tại số 125 Thái Thịnh. Tìm nhanh theo triệu chứng hoặc lọc theo từng chuyên khoa.
                </p>

                @if(isset($symptomSuggestions) && $symptomSuggestions->isNotEmpty())
                    <div class="tiki-quick-tags mt-3">
                        <span class="tiki-quick-tags-label"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Triệu chứng phổ biến:</span>
                        @foreach($symptomSuggestions->take(5) as $s)
                            <a href="{{ route('services.index', ['symptom' => $s['name']]) }}" class="tiki-tag-pill">
                                {{ $s['name'] }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="col-lg-6">
                <div class="tiki-search-card">
                    <form id="service-search-form" action="{{ route('services.index') }}" method="GET" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label for="department_id" class="tiki-form-label">Chuyên khoa</label>
                            <select name="department_id" id="department_id" class="tiki-form-select w-100" onchange="this.form.submit()">
                                <option value="">Tất cả chuyên khoa</option>
                                @if(isset($departments))
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ (isset($selectedDepartmentId) && (string)$selectedDepartmentId === (string)$dept->id) ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sort" class="tiki-form-label">Sắp xếp giá</label>
                            <select name="sort" id="sort" class="tiki-form-select w-100" onchange="this.form.submit()">
                                <option value="" {{ empty($sort) ? 'selected' : '' }}>Mặc định</option>
                                <option value="price_asc" {{ ($sort ?? '') === 'price_asc' ? 'selected' : '' }}>Giá thấp → cao</option>
                                <option value="price_desc" {{ ($sort ?? '') === 'price_desc' ? 'selected' : '' }}>Giá cao → thấp</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="symptom" class="tiki-form-label">Triệu chứng / Dịch vụ</label>
                            <div class="d-flex gap-2">
                                <input type="text"
                                       name="symptom"
                                       id="symptom"
                                       class="tiki-form-control w-100"
                                       placeholder="Đau đầu, siêu âm..."
                                       value="{{ $symptomQuery ?? '' }}">
                                <button type="submit" class="ts-btn flex-shrink-0">
                                    <i class="bi bi-search"></i>
                                    <span>Tìm</span>
                                </button>
                            </div>
                        </div>
                        @if(($symptomQuery ?? null) || ($selectedDepartmentId ?? null) || ($sort ?? null))
                            <div class="col-12 mt-2">
                                <a href="{{ route('services.index') }}" class="ts-link-action">
                                    <i class="bi bi-x-circle"></i>
                                    <span>Xóa bộ lọc tìm kiếm</span>
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        @if(isset($departments) && $departments->isNotEmpty())
            <div class="d-flex flex-wrap gap-2 mt-4">
                <a href="{{ route('services.index', array_filter(['symptom' => $symptomQuery ?? null])) }}"
                   class="ts-filter-pill {{ empty($selectedDepartmentId) ? 'is-active' : '' }}">
                    <i class="bi bi-grid-fill"></i>
                    <span>Tất cả gói khám</span>
                </a>
                @foreach($departments as $dept)
                    @php
                        $isActiveDept = (string)($selectedDepartmentId ?? '') === (string)$dept->id;
                    @endphp
                    <a href="{{ route('services.index', array_filter(['department_id' => $dept->id, 'symptom' => $symptomQuery ?? null])) }}"
                       class="ts-filter-pill {{ $isActiveDept ? 'is-active' : '' }}">
                        <span>{{ $dept->name }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>

<section class="tiki-section">
    <div class="ts-frame">
        @if($symptomQuery ?? null)
            <div class="ts-surface-card p-4 mb-4">
                @if($suggestedServices->isNotEmpty())
                    <h2 class="h5 fw-bold mb-1">
                        <i class="bi bi-check2-circle text-success me-1"></i>Kết quả khớp triệu chứng "{{ $symptomQuery }}" ({{ $suggestedServices->count() }})
                    </h2>
                    @if($suggestedDepartments->isNotEmpty())
                        <p class="mb-0 ts-small">
                            Chuyên khoa đề xuất:
                            @foreach($suggestedDepartments as $dept)
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold me-1">{{ $dept->name }}</span>
                            @endforeach
                        </p>
                    @endif
                @else
                    <h2 class="h5 fw-bold mb-1">Không tìm thấy dịch vụ khớp "{{ $symptomQuery }}"</h2>
                    <a href="{{ route('services.index') }}" class="ts-link-action">Xem toàn bộ danh mục dịch vụ →</a>
                @endif
            </div>
        @endif

        @if(isset($services) && $services->count())
            @php
                $fallbackPhotos = [
                    asset('assets/img/departments-1.jpg'),
                    asset('assets/img/departments-2.jpg'),
                    asset('assets/img/departments-3.jpg'),
                    asset('assets/img/departments-4.jpg'),
                    asset('assets/img/departments-5.jpg'),
                ];
            @endphp
            <div class="row g-4">
                @foreach($services as $idx => $service)
                    @php
                        $imgUrl = !empty($service->image) ? asset('storage/'.$service->image) : $fallbackPhotos[$idx % count($fallbackPhotos)];
                        $deptName = $service->department->name ?? 'Đa khoa';
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <article class="tiki-item-card">
                            <div class="tiki-card-thumb">
                                <img src="{{ $imgUrl }}"
                                     alt="{{ $service->name }}"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src='{{ $fallbackPhotos[$idx % count($fallbackPhotos)] }}';">
                                <div class="tiki-card-badges">
                                    <span class="tiki-badge tiki-badge-primary">
                                        <i class="bi bi-hospital"></i>
                                        <span>{{ $deptName }}</span>
                                    </span>
                                    <span class="tiki-badge tiki-badge-emerald">
                                        <i class="bi bi-shield-check"></i>
                                        <span>Hỗ trợ BHYT</span>
                                    </span>
                                </div>
                            </div>

                            <div class="tiki-card-body">
                                <h2 class="tiki-card-title">{{ $service->name }}</h2>
                                <p class="tiki-card-desc">
                                    {{ $service->description ? \Illuminate\Support\Str::limit($service->description, 115) : 'Khám lâm sàng, tư vấn phác đồ chuyên sâu cùng Bác sĩ Chuyên khoa I - II tại 125 Thái Thịnh.' }}
                                </p>

                                @if($service->symptoms && $service->symptoms->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1 mb-3">
                                        @foreach($service->symptoms->take(3) as $symptom)
                                            <span class="med-symptom-tag">{{ $symptom->symptom_name }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="tiki-card-meta-list">
                                    <div class="tiki-card-meta-item">
                                        <i class="bi bi-clock-history"></i>
                                        <span>Thời gian khám dự kiến: <strong class="ts-num">30 – 45 phút</strong></span>
                                    </div>
                                </div>

                                <div class="tiki-card-footer">
                                    <div class="tiki-price-block">
                                        <span class="tiki-price-label">Giá niêm yết</span>
                                        <span class="tiki-price-value ts-num">{{ number_format($service->price ?? 300000, 0, ',', '.') }}đ</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button"
                                                class="ts-btn-secondary ts-btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#serviceModal{{ $service->id }}">
                                            <span>Chi tiết</span>
                                        </button>
                                        <a href="{{ route('appointments.create', ['department_id' => $service->department_id, 'service_id' => $service->id]) }}" class="ts-btn ts-btn-sm">
                                            <i class="bi bi-calendar2-plus"></i>
                                            <span>Đặt khám</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>

                    {{-- Service Detail Modal --}}
                    <div class="modal fade" id="serviceModal{{ $service->id }}" tabindex="-1" aria-labelledby="serviceModalLabel{{ $service->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" style="max-width: 580px;">
                            <div class="modal-content p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <span class="badge bg-primary bg-opacity-10 text-primary mb-1">{{ $deptName }}</span>
                                        <h3 class="h5 fw-bold mb-0" id="serviceModalLabel{{ $service->id }}">{{ $service->name }}</h3>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                                </div>
                                <p class="mb-3" style="white-space: pre-line; color: var(--ink-secondary); font-size: 0.92rem;">
                                    {{ $service->description ?? 'Khám lâm sàng, chỉ định cận lâm sàng và tư vấn điều trị trực tiếp cùng bác sĩ chuyên khoa.' }}
                                </p>
                                <div class="p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center" style="background-color: var(--surface-bg); border: 1px solid var(--border-color);">
                                    <div>
                                        <span class="ts-small d-block">Chuyên khoa phụ trách</span>
                                        <strong>{{ $deptName }}</strong>
                                    </div>
                                    <div class="text-end">
                                        <span class="ts-small d-block">Giá niêm yết Sở Y Tế</span>
                                        <span class="tiki-price-value ts-num">{{ number_format($service->price ?? 300000, 0, ',', '.') }}đ</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="ts-btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                    <a href="{{ route('appointments.create', ['department_id' => $service->department_id, 'service_id' => $service->id]) }}" class="ts-btn">
                                        <i class="bi bi-calendar2-plus"></i>
                                        <span>Đặt lịch ngay</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($services instanceof \Illuminate\Pagination\AbstractPaginator)
                <div class="d-flex justify-content-center mt-5">
                    {{ $services->onEachSide(1)->links() }}
                </div>
            @endif
        @else
            <div class="ts-surface-card p-5 text-center">
                <p class="mb-3">Không có dịch vụ nào phù hợp với tìm kiếm của bạn.</p>
                <a href="{{ route('services.index') }}" class="ts-btn">Xem tất cả dịch vụ</a>
            </div>
        @endif
    </div>
</section>
@endsection
