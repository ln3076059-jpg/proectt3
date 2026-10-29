@extends('home.frontend')

@section('title', 'Hệ thống Chuyên khoa · Phòng khám Đa khoa 125 Thái Thịnh')

@section('content')
<!-- Page Title -->
<div class="page-title" data-aos="fade">
  <nav class="breadcrumbs">
    <div class="container">
      <ol>
        <li><a href="{{ url('/') }}">Trang chủ</a></li>
        <li class="current">Hệ thống Chuyên khoa</li>
      </ol>
    </div>
  </nav>
</div><!-- End Page Title -->

<section class="tiki-hero-section py-4">
    <div class="ts-frame">
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                <span class="tiki-section-eyebrow">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Hệ thống Chuyên khoa Đồng bộ</span>
                </span>
                <h1 class="tiki-hero-title mb-2">
                    Hệ thống Chuyên khoa tại 125 Thái Thịnh
                </h1>
                <p class="tiki-section-desc">
                    Đáp ứng đầy đủ các chuyên khoa từ Nội tổng quát, Tim mạch, Tai Mũi Họng, Nhi khoa đến Sản phụ khoa và Chẩn đoán hình ảnh công nghệ cao.
                </p>
            </div>

            <div class="col-lg-5">
                <div class="tiki-search-card">
                    <form action="{{ route('departments.index') }}" method="GET" class="d-flex flex-column gap-2">
                        <label for="symptom" class="tiki-form-label mb-0">Tìm chuyên khoa theo triệu chứng lâm sàng</label>
                        <div class="d-flex gap-2">
                            <input type="text"
                                   id="symptom"
                                   name="symptom"
                                   value="{{ $query ?? '' }}"
                                   class="tiki-form-control w-100"
                                   placeholder="Ví dụ: đau tức ngực, ù tai, sốt...">
                            <button type="submit" class="ts-btn flex-shrink-0">
                                <i class="bi bi-search"></i>
                                <span>Tìm khoa</span>
                            </button>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                            <span class="text-muted" style="font-size: 11.5px;"><i class="bi bi-lightning-charge-fill text-warning"></i> Gợi ý nhanh:</span>
                            @foreach(['Đau tức ngực', 'Đau đầu', 'Sốt trẻ em', 'Khám thai', 'Ù tai', 'Đau dạ dày'] as $chip)
                                <a href="{{ route('departments.index', ['symptom' => $chip]) }}"
                                   class="badge rounded-pill text-decoration-none {{ ($query ?? '') === $chip ? 'bg-primary text-white' : 'bg-light text-secondary border' }}"
                                   style="font-size: 11px; font-weight: 500; padding: 4px 9px;">
                                    {{ $chip }}
                                </a>
                            @endforeach
                        </div>
                        @if($query ?? null)
                            <div>
                                <a href="{{ route('departments.index') }}" class="ts-link-action">
                                    <i class="bi bi-x-circle"></i>
                                    <span>Xóa lọc "{{ $query }}"</span>
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="tiki-section">
    <div class="ts-frame">
        @if(isset($departments) && $departments->count())
            @php
                $deptPhotos = [
                    asset('assets/img/departments-1.jpg'),
                    asset('assets/img/departments-2.jpg'),
                    asset('assets/img/departments-3.jpg'),
                    asset('assets/img/departments-4.jpg'),
                    asset('assets/img/departments-5.jpg'),
                ];
                $floors = ['Tầng 2 · Khu A', 'Tầng 2 · Khu B', 'Tầng 3 · Khu A', 'Tầng 3 · Khu B', 'Tầng 4 · Khu A', 'Tầng 5 · Khu A'];
            @endphp
            <div class="row g-4">
                @foreach($departments as $idx => $dept)
                    @php
                        $deptServices = isset($services) ? $services->where('department_id', $dept->id) : collect();
                        $photoUrl = !empty($dept->image) ? asset('storage/'.$dept->image) : $deptPhotos[$idx % count($deptPhotos)];
                        $floorLabel = $floors[$idx % count($floors)];
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <article class="tiki-item-card">
                            <div class="tiki-card-thumb">
                                <img src="{{ $photoUrl }}"
                                     alt="{{ $dept->name }}"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src='{{ $deptPhotos[$idx % count($deptPhotos)] }}';">
                                <div class="tiki-card-badges">
                                    <span class="tiki-badge tiki-badge-primary">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <span>{{ $floorLabel }}</span>
                                    </span>
                                    <span class="tiki-badge tiki-badge-emerald">
                                        <i class="bi bi-shield-check"></i>
                                        <span>BHYT</span>
                                    </span>
                                </div>
                            </div>

                            <div class="tiki-card-body">
                                <h2 class="tiki-card-title">{{ $dept->name }}</h2>
                                <p class="tiki-card-desc">
                                    {{ $dept->description ? \Illuminate\Support\Str::limit($dept->description, 120) : 'Chuyên khoa cung cấp dịch vụ thăm khám lâm sàng, chẩn đoán hình ảnh và điều trị chuyên sâu tiêu chuẩn Bộ Y Tế.' }}
                                </p>

                                <div class="tiki-card-meta-list">
                                    <div class="tiki-card-meta-item">
                                        <i class="bi bi-card-checklist"></i>
                                        <span class="ts-num"><strong>{{ max($deptServices->count(), 6) }}</strong> danh mục dịch vụ &amp; xét nghiệm</span>
                                    </div>
                                    <div class="tiki-card-meta-item">
                                        <i class="bi bi-clock"></i>
                                        <span class="ts-num">Tiếp đón: <strong>07:30 – 20:00</strong> (Thứ 2 – CN)</span>
                                    </div>
                                </div>

                                @if($deptServices->count() > 0)
                                    <div class="mt-2 pt-2 border-top">
                                        <div class="text-muted fw-semibold mb-1" style="font-size: 11.5px;">
                                            <i class="bi bi-stars text-primary me-1"></i>Dịch vụ tiêu biểu tại khoa:
                                        </div>
                                        <div class="d-flex flex-column gap-1">
                                            @foreach($deptServices->take(2) as $srv)
                                                <div class="d-flex justify-content-between align-items-center small" style="font-size: 12px;">
                                                    <span class="text-truncate text-secondary pe-2" style="max-width: 68%;">{{ $srv->name }}</span>
                                                    <span class="fw-bold text-primary flex-shrink-0">{{ number_format($srv->price ?? 0, 0, ',', '.') }}đ</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="tiki-card-footer">
                                    <a href="{{ route('services.index', ['department_id' => $dept->id]) }}" class="ts-btn-secondary ts-btn-sm">
                                        <span>Bảng giá khoa</span>
                                    </a>
                                    <a href="{{ route('appointments.create', ['department_id' => $dept->id]) }}" class="ts-btn ts-btn-sm">
                                        <i class="bi bi-calendar2-plus"></i>
                                        <span>Đặt lịch khám</span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>

            @if($departments instanceof \Illuminate\Pagination\AbstractPaginator)
                <div class="d-flex justify-content-center mt-5">
                    {{ $departments->onEachSide(1)->links() }}
                </div>
            @endif
        @else
            <div class="ts-surface-card p-5 text-center">
                <p class="mb-0">Không tìm thấy chuyên khoa nào.</p>
            </div>
        @endif
    </div>
</section>
@endsection
