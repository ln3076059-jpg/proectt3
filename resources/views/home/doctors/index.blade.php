@extends('home.frontend')

@section('title', 'Đội ngũ Bác sĩ Chuyên khoa · Medilab 125 Thái Thịnh')

@section('content')
<!-- Page Title -->
<div class="page-title" data-aos="fade">
  <div class="heading">
    <div class="container">
      <div class="row d-flex justify-content-center text-center">
        <div class="col-lg-8">
          <h1>Đội ngũ Bác sĩ Chuyên khoa</h1>
          <p class="mb-0">
            Hơn 45 Phó Giáo sư, Tiến sĩ, Bác sĩ Chuyên khoa I &amp; II từng công tác tại Bệnh viện Bạch Mai, Việt Đức, Phụ sản Trung ương và Nhi Trung ương trực tiếp thăm khám mỗi ngày.
          </p>
        </div>
      </div>
    </div>
  </div>
  <nav class="breadcrumbs">
    <div class="container">
      <ol>
        <li><a href="{{ url('/') }}">Trang chủ</a></li>
        <li class="current">Đội ngũ Bác sĩ</li>
      </ol>
    </div>
  </nav>
</div><!-- End Page Title -->

<section id="doctors" class="doctors section">
  <div class="container">
    {{-- Search & Specialty Filter Bar --}}
    <div class="tiki-search-card mb-4">
      <form action="{{ route('doctors.index') }}" method="GET" class="row g-2 align-items-end">
        @if(!empty($selectedDepartmentId))
          <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}">
        @endif
        <div class="col-md-9">
          <label for="q" class="tiki-form-label">Tra cứu theo tên bác sĩ, học hàm hoặc chuyên khoa</label>
          <input type="text"
                 id="q"
                 name="q"
                 value="{{ $q ?? '' }}"
                 class="tiki-form-control w-100"
                 placeholder="Nhập tên bác sĩ hoặc chuyên môn...">
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="ts-btn w-100">
            <i class="bi bi-search"></i>
            <span>Tìm bác sĩ</span>
          </button>
          @if(!empty($q) || !empty($selectedDepartmentId))
            <a href="{{ route('doctors.index') }}" class="ts-btn-secondary flex-shrink-0" title="Xóa bộ lọc">
              <i class="bi bi-x-lg"></i>
            </a>
          @endif
        </div>
      </form>

      @if(isset($departments) && $departments->isNotEmpty())
        <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
          <a href="{{ route('doctors.index', array_filter(['q' => $q ?? null])) }}"
             class="ts-filter-pill {{ empty($selectedDepartmentId) ? 'is-active' : '' }}">
            <i class="bi bi-people-fill"></i>
            <span>Tất cả chuyên khoa</span>
          </a>
          @foreach($departments as $dept)
            @php
              $isActiveDept = (string)($selectedDepartmentId ?? '') === (string)$dept->id;
            @endphp
            <a href="{{ route('doctors.index', array_filter(['department_id' => $dept->id, 'q' => $q ?? null])) }}"
               class="ts-filter-pill {{ $isActiveDept ? 'is-active' : '' }}">
              <span>{{ $dept->name }}</span>
            </a>
          @endforeach
        </div>
      @endif
    </div>

    @if(isset($doctors) && $doctors->count())
      @php
        $medilabDoctors = [
            asset('assets/img/doctors/doctors-1.jpg'),
            asset('assets/img/doctors/doctors-2.jpg'),
            asset('assets/img/doctors/doctors-3.jpg'),
            asset('assets/img/doctors/doctors-4.jpg'),
        ];
      @endphp
      <div class="row gy-4">
        @foreach($doctors as $idx => $doc)
          @php
            $name = $doc->user->name ?? 'BS.CKII Chuyên khoa';
            $fallbackAvatar = $medilabDoctors[$idx % count($medilabDoctors)];
            $avatar = !empty($doc->avatar) ? asset('storage/'.$doc->avatar) : $fallbackAvatar;
            $deptName = $doc->department->name ?? 'Đa khoa';
            $spec = $doc->specialization ?? 'Bác sĩ điều trị chuyên sâu tại Phòng khám Đa khoa 125 Thái Thịnh';
            $cchn = $doc->license_number ?? ('BYT-125TT-' . (100 + $doc->id));
            $docUpcomingDates = $upcomingDatesByDoctor[$doc->id] ?? [];
            $firstAvail = $docUpcomingDates[0] ?? null;
          @endphp
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="{{ (($idx % 4) + 1) * 100 }}">
            <div class="team-member d-flex align-items-start">
              <div class="pic">
                <img src="{{ $avatar }}"
                     class="img-fluid"
                     alt="Chân dung {{ $name }}"
                     loading="lazy"
                     onerror="this.onerror=null;this.src='{{ $fallbackAvatar }}';">
              </div>
              <div class="member-info w-100">
                <h4>{{ $name }}</h4>
                <span>{{ $deptName }} · CCHN: {{ $cchn }}</span>
                <p>{{ \Illuminate\Support\Str::limit($doc->description ?: $spec, 110) }}</p>

                <div class="mt-2 small">
                  @if($firstAvail)
                    <div class="text-primary fw-semibold">
                      <i class="bi bi-calendar2-check me-1"></i>Ca gần nhất: {{ $firstAvail['formatted_date'] }} ({{ $firstAvail['first_time'] }}) · Còn {{ $firstAvail['remaining_slots'] }} chỗ
                    </div>
                  @else
                    <div class="text-success fw-semibold">
                      <i class="bi bi-check-circle-fill me-1"></i>Lịch khám: Thứ 2 – Thứ 7 (Đang nhận lịch hẹn)
                    </div>
                  @endif
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                  <div class="social mt-0" style="width: auto;">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#doctorModal{{ $doc->id }}" title="Xem hồ sơ chi tiết"><i class="bi bi-person-vcard"></i></a>
                    <a href="{{ route('services.index', ['department_id' => $doc->department_id]) }}" title="Dịch vụ khoa"><i class="bi bi-clipboard2-pulse"></i></a>
                  </div>
                  <div class="d-flex gap-2">
                    <button type="button" class="ts-btn-secondary ts-btn-sm" data-bs-toggle="modal" data-bs-target="#doctorModal{{ $doc->id }}">
                      <span>Hồ sơ &amp; Lịch</span>
                    </button>
                    <a href="{{ route('appointments.create', array_filter(['doctor_id' => $doc->id, 'department_id' => $doc->department_id, 'date' => $firstAvail['date'] ?? null])) }}" class="ts-btn ts-btn-sm">
                      <i class="bi bi-calendar2-plus"></i>
                      <span>Đặt lịch</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- End Team Member -->

          {{-- Doctor Detail Modal --}}
          <div class="modal fade" id="doctorModal{{ $doc->id }}" tabindex="-1" aria-labelledby="doctorModalLabel{{ $doc->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div>
                    <span class="badge bg-primary bg-opacity-10 text-primary mb-1">{{ $deptName }}</span>
                    <h3 class="h5 fw-bold mb-0" id="doctorModalLabel{{ $doc->id }}">{{ $name }}</h3>
                  </div>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="row g-4">
                  <div class="col-md-4">
                    <div class="rounded-3 overflow-hidden" style="border: 1px solid var(--border-color);">
                      <img src="{{ $avatar }}" alt="{{ $name }}" class="w-100" style="aspect-ratio: 1/1; object-fit: cover;" onerror="this.onerror=null;this.src='{{ $fallbackAvatar }}';">
                    </div>
                  </div>
                  <div class="col-md-8">
                    <div class="p-3 rounded-3 mb-3" style="background-color: var(--surface-bg); border: 1px solid var(--border-color);">
                      <p class="mb-1"><strong>Chuyên khoa:</strong> {{ $deptName }}</p>
                      <p class="mb-1 ts-num"><strong>Số CCHN Bộ Y Tế:</strong> {{ $cchn }}</p>
                      <p class="mb-0"><strong>Chuyên môn:</strong> {{ $spec }}</p>
                    </div>
                    @if(!empty($doc->description))
                      <p class="mb-3" style="white-space: pre-line; color: var(--ink-secondary);">{{ $doc->description }}</p>
                    @endif

                    @if(!empty($docUpcomingDates))
                      <div class="p-3 rounded-3" style="background-color: #f1f7fc; border: 1px solid #d6e8f8;">
                        <div class="ts-small fw-bold mb-2" style="color: var(--accent-color);">
                          <i class="bi bi-calendar-week me-1"></i>Chọn nhanh ngày bác sĩ có lịch trực (14 ngày tới):
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                          @foreach($docUpcomingDates as $availDay)
                            <a href="{{ route('appointments.create', ['doctor_id' => $doc->id, 'department_id' => $doc->department_id, 'date' => $availDay['date']]) }}"
                               class="ts-filter-pill py-1 px-3 ts-num"
                               style="font-size: 0.82rem; background: #fff;">
                              <strong>{{ $availDay['formatted_date'] }}</strong> · Còn {{ $availDay['remaining_slots'] }} ca
                            </a>
                          @endforeach
                        </div>
                      </div>
                    @endif
                  </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                  <button type="button" class="ts-btn-secondary" data-bs-dismiss="modal">Đóng</button>
                  <a href="{{ route('appointments.create', array_filter(['doctor_id' => $doc->id, 'department_id' => $doc->department_id, 'date' => $firstAvail['date'] ?? null])) }}" class="ts-btn">
                    <i class="bi bi-calendar2-plus"></i>
                    <span>Đặt lịch với bác sĩ</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      @if($doctors instanceof \Illuminate\Pagination\AbstractPaginator)
        <div class="d-flex justify-content-center mt-5">
          {{ $doctors->onEachSide(1)->links() }}
        </div>
      @endif
    @else
      <div class="ts-surface-card p-5 text-center">
        <p class="mb-3">Không tìm thấy bác sĩ nào phù hợp.</p>
        <a href="{{ route('doctors.index') }}" class="ts-btn">Xem toàn bộ danh sách</a>
      </div>
    @endif
  </div>
</section>
@endsection
