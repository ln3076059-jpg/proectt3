@extends('home.frontend')

@section('title', 'Phòng khám Đa khoa 125 Thái Thịnh — Hệ thống Đặt lịch Khám Chuyên khoa Hà Nội')

@section('content')
  @php
    $featuredDepts = $departments->take(5)->values();
    $featuredServices = $services->take(6)->values();
    $featuredDoctors = $doctors->take(4)->values();
    $serviceIcons = [
        'fa-heartbeat',
        'fa-pills',
        'fa-hospital-user',
        'fa-dna',
        'fa-wheelchair',
        'fa-notes-medical',
    ];
    $medilabDeptImgs = [
        asset('assets/img/departments-1.jpg'),
        asset('assets/img/departments-2.jpg'),
        asset('assets/img/departments-3.jpg'),
        asset('assets/img/departments-4.jpg'),
        asset('assets/img/departments-5.jpg'),
    ];
    $medilabDoctorImgs = [
        asset('assets/img/doctors/doctors-1.jpg'),
        asset('assets/img/doctors/doctors-2.jpg'),
        asset('assets/img/doctors/doctors-3.jpg'),
        asset('assets/img/doctors/doctors-4.jpg'),
    ];
  @endphp

  <!-- Hero Section -->
  <section id="hero" class="hero section light-background">

    <img src="{{ asset('assets/img/hero-bg.jpg') }}" alt="Phòng khám Đa khoa 125 Thái Thịnh" data-aos="fade-in">

    <div class="container position-relative">

      <div class="welcome position-relative" data-aos="fade-down" data-aos-delay="100" style="position: relative; z-index: 15;">
        <h2 style="font-weight: 800; letter-spacing: -0.5px;">CHÀO MỪNG ĐẾN VỚI 125 THÁI THỊNH</h2>
        <p style="color: #374151; font-weight: 500;">Hệ thống Đặt lịch Khám Chuyên khoa &amp; Hồ sơ Bệnh án Điện tử Tiêu chuẩn Bộ Y Tế</p>
      </div><!-- End Welcome -->

      <!-- Quick Search Bar inside Medilab Hero -->
      <div class="row mt-3 mb-4" data-aos="fade-up" data-aos-delay="150" style="position: relative; z-index: 20;">
        <div class="col-lg-10 col-xl-8">
          <div class="p-4 rounded-4 position-relative" style="background: #ffffff; border: 1px solid rgba(25, 119, 204, 0.22); box-shadow: 0 12px 36px -4px rgba(15, 23, 42, 0.09); z-index: 20;">
            <form action="{{ route('services.index') }}" method="GET" class="d-flex flex-column flex-sm-row gap-2" autocomplete="off">
              <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-primary ps-3 pe-2"><i class="bi bi-search fs-6"></i></span>
                <input
                  type="text"
                  name="symptom"
                  id="heroSearchInput"
                  class="form-control border-start-0 py-3"
                  placeholder="Tìm gói khám, bác sĩ, chuyên khoa hoặc triệu chứng lâm sàng..."
                  aria-label="Tìm kiếm dịch vụ khám"
                  style="font-size: 15px;"
                >
              </div>
              <button type="submit" class="btn btn-primary rounded-pill px-4 flex-shrink-0 fw-semibold" style="background-color: var(--accent-color); border-color: var(--accent-color);">
                Tìm dịch vụ
              </button>
            </form>

            <div id="heroSearchDropdown" class="d-none rounded-3 p-3" style="position: absolute; left: 0; right: 0; top: calc(100% + 8px); z-index: 1060; background: #fff; border: 1px solid #c7dff3; box-shadow: 0 16px 40px rgba(0,0,0,0.14); max-height: 360px; overflow-y: auto;">
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-2 pt-1" style="font-size: 13px;">
              <span class="fw-semibold text-secondary">Tìm nhanh:</span>
              <a href="{{ route('services.index', ['symptom' => 'Tổng quát']) }}" class="badge rounded-pill text-bg-light border text-decoration-none py-2 px-3">Khám tổng quát</a>
              <a href="{{ route('services.index', ['symptom' => 'Siêu âm']) }}" class="badge rounded-pill text-bg-light border text-decoration-none py-2 px-3">Siêu âm thai 5D</a>
              <a href="{{ route('services.index', ['symptom' => 'Tim mạch']) }}" class="badge rounded-pill text-bg-light border text-decoration-none py-2 px-3">Tim mạch</a>
              <a href="{{ route('advisor.index') }}" class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle text-decoration-none py-2 px-3">
                <i class="bi bi-clipboard2-pulse me-1"></i>Tư vấn triệu chứng
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="content row gy-4">
        <div class="col-lg-4 d-flex align-items-stretch">
          <div class="why-box" data-aos="zoom-out" data-aos-delay="200">
            <h3>Tại sao chọn 125 Thái Thịnh?</h3>
            <p>
              Hơn 25 năm chăm sóc sức khỏe cộng đồng tại thủ đô Hà Nội với đội ngũ Phó Giáo sư, Tiến sĩ, Bác sĩ Chuyên khoa I &amp; II giàu kinh nghiệm, hệ thống xét nghiệm tự động chuẩn ISO 15189 và tiếp đón ưu tiên bằng mã QR không chờ đợi.
            </p>
            <div class="text-center">
              <a href="{{ route('appointments.create') }}" class="more-btn"><span>Đặt lịch ngay</span> <i class="bi bi-chevron-right"></i></a>
            </div>
          </div>
        </div><!-- End Why Box -->

        <div class="col-lg-8 d-flex align-items-stretch">
          <div class="d-flex flex-column justify-content-center">
            <div class="row gy-4">

              <div class="col-xl-4 d-flex align-items-stretch">
                <div class="icon-box" data-aos="zoom-out" data-aos-delay="300">
                  <i class="bi bi-clipboard-data"></i>
                  <h4>Check-in QR Ưu Tiên</h4>
                  <p>Nhận mã QR định danh ngay sau khi đặt lịch, vào thẳng buồng khám chuyên khoa theo đúng giờ hẹn.</p>
                </div>
              </div><!-- End Icon Box -->

              <div class="col-xl-4 d-flex align-items-stretch">
                <div class="icon-box" data-aos="zoom-out" data-aos-delay="400">
                  <i class="bi bi-gem"></i>
                  <h4>Chuyên gia Đầu ngành</h4>
                  <p>Quy tụ hơn {{ max($doctors->count(), 45) }} PGS, TS, BSCK I &amp; II từ Bạch Mai, Việt Đức, Phụ sản TW và Nhi TW.</p>
                </div>
              </div><!-- End Icon Box -->

              <div class="col-xl-4 d-flex align-items-stretch">
                <div class="icon-box" data-aos="zoom-out" data-aos-delay="500">
                  <i class="bi bi-inboxes"></i>
                  <h4>Bệnh án Điện tử &amp; BHYT</h4>
                  <p>Tra cứu kết quả xét nghiệm ISO 15189, đơn thuốc số hóa và hưởng đồng chi trả BHYT đúng tuyến.</p>
                </div>
              </div><!-- End Icon Box -->

            </div>
          </div>
        </div>
      </div><!-- End  Content-->

    </div>

  </section><!-- /Hero Section -->

  <!-- About Section -->
  <section id="about" class="about section">

    <div class="container">

      <div class="row gy-4 gx-5">

        <div class="col-lg-6 position-relative align-self-start" data-aos="fade-up" data-aos-delay="200">
          <img src="{{ asset('assets/img/about.jpg') }}" class="img-fluid" alt="Về Phòng khám Đa khoa 125 Thái Thịnh">
          <a href="{{ route('introduces.index') }}" class="pulsating-play-btn" title="Xem giới thiệu chi tiết"></a>
        </div>

        <div class="col-lg-6 content" data-aos="fade-up" data-aos-delay="100">
          <h3>Về Phòng khám Đa khoa 125 Thái Thịnh</h3>
          <p>
            Thành lập từ năm 1998 tại trung tâm quận Đống Đa, Hà Nội, Phòng khám Đa khoa 125 Thái Thịnh là địa chỉ khám chữa bệnh tin cậy với hệ thống {{ max($departments->count(), 12) }} chuyên khoa đồng bộ, trang thiết bị chẩn đoán hình ảnh công nghệ cao và quy trình quản lý hồ sơ bệnh án điện tử khép kín.
          </p>
          <ul>
            <li>
              <i class="fa-solid fa-vial-circle-check"></i>
              <div>
                <h5>Trung tâm Xét nghiệm Tự động Chuẩn ISO 15189</h5>
                <p>Hệ thống xét nghiệm sinh hóa, huyết học, miễn dịch tự động hoàn toàn giúp trả kết quả nhanh và chính xác trực tiếp lên hồ sơ bệnh nhân.</p>
              </div>
            </li>
            <li>
              <i class="fa-solid fa-pump-medical"></i>
              <div>
                <h5>Chẩn đoán Hình ảnh 5D &amp; Nội soi Tiêu hóa NBI</h5>
                <p>Trang bị máy siêu âm Doppler màu 4D/5D GE Voluson Hoa Kỳ và hệ thống nội soi dải tần ánh sáng hẹp NBI Olympus Nhật Bản.</p>
              </div>
            </li>
            <li>
              <i class="fa-solid fa-heart-circle-xmark"></i>
              <div>
                <h5>Minh bạch Chi phí &amp; Hỗ trợ Thanh toán BHYT</h5>
                <p>100% danh mục khám lâm sàng và cận lâm sàng được niêm yết công khai, hỗ trợ thanh toán BHYT và bảo lãnh viện phí thương mại.</p>
              </div>
            </li>
          </ul>
        </div>

      </div>

    </div>

  </section><!-- /About Section -->

  <!-- Stats Section -->
  <section id="stats" class="stats section light-background">

    <div class="container" data-aos="fade-up" data-aos-delay="100">

      <div class="row gy-4">

        <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
          <i class="fa-solid fa-user-md"></i>
          <div class="stats-item">
            <span data-purecounter-start="0" data-purecounter-end="{{ max($doctors->count(), 45) }}" data-purecounter-duration="1" class="purecounter">{{ max($doctors->count(), 45) }}</span>
            <p>Bác sĩ Chuyên khoa</p>
          </div>
        </div><!-- End Stats Item -->

        <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
          <i class="fa-regular fa-hospital"></i>
          <div class="stats-item">
            <span data-purecounter-start="0" data-purecounter-end="{{ max($departments->count(), 12) }}" data-purecounter-duration="1" class="purecounter">{{ max($departments->count(), 12) }}</span>
            <p>Chuyên khoa Lâm sàng</p>
          </div>
        </div><!-- End Stats Item -->

        <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
          <i class="fas fa-flask"></i>
          <div class="stats-item">
            <span data-purecounter-start="0" data-purecounter-end="{{ max($services->count(), 36) }}" data-purecounter-duration="1" class="purecounter">{{ max($services->count(), 36) }}</span>
            <p>Gói khám &amp; Xét nghiệm</p>
          </div>
        </div><!-- End Stats Item -->

        <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
          <i class="fas fa-award"></i>
          <div class="stats-item">
            <span data-purecounter-start="0" data-purecounter-end="25" data-purecounter-duration="1" class="purecounter">25</span>
            <p>Năm Phụng sự Sức khỏe</p>
          </div>
        </div><!-- End Stats Item -->

      </div>

    </div>

  </section><!-- /Stats Section -->

  <!-- Services Section -->
  <section id="services" class="services section">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
      <h2>Dịch vụ &amp; Gói khám</h2>
      <p>Bảng giá dịch vụ niêm yết công khai tại Phòng khám Đa khoa 125 Thái Thịnh · Hỗ trợ thanh toán BHYT</p>
    </div><!-- End Section Title -->

    <div class="container">

      <div class="row gy-4">
        @foreach($featuredServices as $idx => $service)
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ ($idx + 1) * 100 }}">
            <div class="service-item position-relative">
              <div class="icon">
                <i class="fas {{ $serviceIcons[$idx % count($serviceIcons)] }}"></i>
              </div>
              <a href="{{ route('appointments.create', ['service_id' => $service->id, 'department_id' => $service->department_id]) }}" class="stretched-link">
                <h3>{{ $service->name }}</h3>
              </a>
              <p class="mb-2">{{ \Illuminate\Support\Str::limit($service->description ?: 'Khám lâm sàng cùng Bác sĩ Chuyên khoa, chỉ định cận lâm sàng và tư vấn phác đồ điều trị.', 110) }}</p>
              <div class="fw-bold mt-2" style="font-size: 16px;">
                {{ $service->department->name ?? 'Đa khoa' }} · {{ number_format($service->price ?? 300000, 0, ',', '.') }}đ
              </div>
            </div>
          </div><!-- End Service Item -->
        @endforeach
      </div>

      <div class="text-center mt-5">
        <a href="{{ route('services.index') }}" class="cta-btn d-inline-block" style="background: var(--accent-color); color: #fff; padding: 10px 30px; border-radius: 50px; font-weight: 500;">
          Xem toàn bộ {{ $services->count() }} dịch vụ &amp; bảng giá
        </a>
      </div>

    </div>

  </section><!-- /Services Section -->

  <!-- Appointment Section -->
  <section id="appointment" class="appointment section light-background">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
      <h2>Đặt lịch khám trực tuyến</h2>
      <p>Chọn chuyên khoa, bác sĩ phụ trách và khung giờ khám để nhận ngay Mã QR ưu tiên tại quầy tiếp đón</p>
    </div><!-- End Section Title -->

    <div class="container" data-aos="fade-up" data-aos-delay="100">

      <form action="{{ route('appointments.create') }}" method="GET" role="form" class="php-email-form">
        <div class="row">
          <div class="col-md-4 form-group">
            <select name="department_id" id="homeQuickDept" class="form-select">
              <option value="">-- Chọn Chuyên khoa --</option>
              @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4 form-group mt-3 mt-md-0">
            <select name="doctor_id" id="homeQuickDoctor" class="form-select">
              <option value="">-- Chọn Bác sĩ Chuyên khoa --</option>
              @foreach($doctors as $doc)
                <option value="{{ $doc->id }}" data-dept="{{ $doc->department_id }}">{{ $doc->user->name ?? 'BS. Chuyên khoa' }} ({{ $doc->department->name ?? 'Đa khoa' }})</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4 form-group mt-3 mt-md-0">
            <select name="service_id" id="homeQuickService" class="form-select">
              <option value="">-- Chọn Gói khám / Dịch vụ --</option>
              @foreach($services as $svc)
                <option value="{{ $svc->id }}" data-dept="{{ $svc->department_id }}">{{ $svc->name }} ({{ number_format($svc->price ?? 300000, 0, ',', '.') }}đ)</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4 form-group mt-3">
            <input type="date" name="date" class="form-control datepicker" id="homeQuickDate" value="{{ \Carbon\Carbon::tomorrow()->toDateString() }}" min="{{ \Carbon\Carbon::today()->toDateString() }}">
          </div>
          <div class="col-md-8 form-group mt-3">
            <input type="text" class="form-control" name="note" placeholder="Ghi chú triệu chứng lâm sàng hoặc yêu cầu khám (Không bắt buộc)">
          </div>
        </div>

        <div class="mt-4 text-center">
          <button type="submit">Tiếp tục chọn khung giờ &amp; Nhận mã QR</button>
        </div>
      </form>

    </div>

  </section><!-- /Appointment Section -->

  <!-- Departments Section -->
  <section id="departments" class="departments section">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
      <h2>Chuyên khoa mũi nhọn</h2>
      <p>Mỗi chuyên khoa được trang bị buồng khám riêng biệt, hệ thống chẩn đoán hình ảnh và xét nghiệm đồng bộ</p>
    </div><!-- End Section Title -->

    <div class="container" data-aos="fade-up" data-aos-delay="100">

      <div class="row">
        <div class="col-lg-3">
          <ul class="nav nav-tabs flex-column">
            @foreach($featuredDepts as $idx => $dept)
              <li class="nav-item">
                <a class="nav-link {{ $idx === 0 ? 'active show' : '' }}" data-bs-toggle="tab" href="#departments-tab-{{ $idx + 1 }}">{{ $dept->name }}</a>
              </li>
            @endforeach
          </ul>
        </div>
        <div class="col-lg-9 mt-4 mt-lg-0">
          <div class="tab-content">
            @foreach($featuredDepts as $idx => $dept)
              @php
                $deptImg = !empty($dept->image) ? asset('storage/'.$dept->image) : $medilabDeptImgs[$idx % count($medilabDeptImgs)];
                $deptDocCount = $doctors->where('department_id', $dept->id)->count() ?: 4;
                $deptSvcCount = $services->where('department_id', $dept->id)->count() ?: 6;
              @endphp
              <div class="tab-pane {{ $idx === 0 ? 'active show' : '' }}" id="departments-tab-{{ $idx + 1 }}">
                <div class="row">
                  <div class="col-lg-8 details order-2 order-lg-1">
                    <h3>{{ $dept->name }}</h3>
                    <p class="fst-italic">
                      Quy tụ {{ $deptDocCount }} Bác sĩ Chuyên khoa phụ trách cùng {{ $deptSvcCount }} danh mục khám lâm sàng &amp; cận lâm sàng chuyên sâu.
                    </p>
                    <p>
                      {{ $dept->description ?: 'Khám, tầm soát và điều trị chuyên sâu với phác đồ chuẩn Bộ Y Tế cùng trang thiết bị hiện đại nhập khẩu từ Hoa Kỳ, Nhật Bản và CHLB Đức.' }}
                    </p>
                    <div class="mt-3 d-flex gap-2">
                      <a href="{{ route('appointments.create', ['department_id' => $dept->id]) }}" class="btn btn-sm btn-primary rounded-pill px-3" style="background-color: var(--accent-color); border-color: var(--accent-color);">
                        Đặt khám khoa này
                      </a>
                      <a href="{{ route('services.index', ['department_id' => $dept->id]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        Xem bảng giá khoa
                      </a>
                    </div>
                  </div>
                  <div class="col-lg-4 text-center order-1 order-lg-2">
                    <img src="{{ $deptImg }}" alt="{{ $dept->name }}" class="img-fluid rounded" onerror="this.onerror=null;this.src='{{ $medilabDeptImgs[$idx % count($medilabDeptImgs)] }}';">
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>

    </div>

  </section><!-- /Departments Section -->

  <!-- Doctors Section -->
  <section id="doctors" class="doctors section light-background">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
      <h2>Đội ngũ Bác sĩ Chuyên khoa</h2>
      <p>Quy tụ các Phó Giáo sư, Tiến sĩ, Bác sĩ Chuyên khoa I &amp; II giàu kinh nghiệm từ các bệnh viện tuyến Trung ương</p>
    </div><!-- End Section Title -->

    <div class="container">

      <div class="row gy-4">
        @foreach($featuredDoctors as $idx => $doctor)
          @php
            $docDisplayName = $doctor->user->name ?? $doctor->name ?? 'BS. Chuyên khoa I';
            $fallbackDocImg = $medilabDoctorImgs[$idx % count($medilabDoctorImgs)];
            $docPortrait = !empty($doctor->avatar) ? asset('storage/'.$doctor->avatar) : (!empty($doctor->image) ? asset('storage/'.$doctor->image) : $fallbackDocImg);
          @endphp
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="{{ ($idx + 1) * 100 }}">
            <div class="team-member d-flex align-items-start">
              <div class="pic">
                <img src="{{ $docPortrait }}" class="img-fluid" alt="{{ $docDisplayName }}" onerror="this.onerror=null;this.src='{{ $fallbackDocImg }}';">
              </div>
              <div class="member-info">
                <h4>{{ $docDisplayName }}</h4>
                <span>{{ $doctor->department->name ?? 'Nội Tổng Quát' }} · Phòng {{ $doctor->room_number ?? ('P.' . (201 + $idx)) }}</span>
                <p>{{ \Illuminate\Support\Str::limit($doctor->specialization ?: ($doctor->description ?: 'Hơn 15 năm kinh nghiệm khám và điều trị chuyên sâu nội - ngoại khoa tại các bệnh viện tuyến Trung ương Hà Nội.'), 110) }}</p>
                <div class="social">
                  <a href="{{ route('doctors.index', ['department_id' => $doctor->department_id]) }}" title="Hồ sơ bác sĩ"><i class="bi bi-person-vcard"></i></a>
                  <a href="{{ route('appointments.create', ['doctor_id' => $doctor->id, 'department_id' => $doctor->department_id]) }}" title="Đặt lịch khám"><i class="bi bi-calendar2-plus"></i></a>
                  <a href="{{ route('services.index', ['department_id' => $doctor->department_id]) }}" title="Dịch vụ chuyên khoa"><i class="bi bi-clipboard2-pulse"></i></a>
                  <a href="{{ route('advisor.index') }}" title="Tư vấn triệu chứng"><i class="bi bi-robot"></i></a>
                </div>
              </div>
            </div>
          </div><!-- End Team Member -->
        @endforeach
      </div>

      <div class="text-center mt-5">
        <a href="{{ route('doctors.index') }}" class="cta-btn d-inline-block" style="background: var(--accent-color); color: #fff; padding: 10px 30px; border-radius: 50px; font-weight: 500;">
          Xem toàn bộ {{ $doctors->count() }} bác sĩ chuyên khoa
        </a>
      </div>

    </div>

  </section><!-- /Doctors Section -->

  <!-- Faq Section -->
  <section id="faq" class="faq section">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
      <h2>Câu hỏi thường gặp</h2>
      <p>Giải đáp nhanh các thắc mắc về quy trình đặt lịch hẹn, khám Bảo hiểm Y tế và tra cứu hồ sơ bệnh án</p>
    </div><!-- End Section Title -->

    <div class="container">

      <div class="row justify-content-center">

        <div class="col-lg-10" data-aos="fade-up" data-aos-delay="100">

          <div class="faq-container">

            <div class="faq-item faq-active">
              <h3>Đặt lịch khám trực tuyến tại 125 Thái Thịnh có mất phí giữ chỗ không?</h3>
              <div class="faq-content">
                <p>Hoàn toàn miễn phí đặt lịch giữ chỗ. Sau khi hoàn tất đăng ký, hệ thống cấp ngay Mã phiếu khám kèm QR Code định danh. Bạn có thể chọn thanh toán trực tuyến qua cổng VNPay hoặc thanh toán trực tiếp tại quầy tiếp đón ưu tiên.</p>
              </div>
              <i class="faq-toggle bi bi-chevron-right"></i>
            </div><!-- End Faq item-->

            <div class="faq-item">
              <h3>Phòng khám có hỗ trợ thanh toán Bảo hiểm Y tế (BHYT) không?</h3>
              <div class="faq-content">
                <p>Có. Phòng khám Đa khoa 125 Thái Thịnh tiếp nhận khám chữa bệnh BHYT đúng tuyến và xuất hóa đơn điện tử hỗ trợ bảo lãnh viện phí thương mại. Khi đi khám, người bệnh chỉ cần mang theo CCCD gắn chip (hoặc VNeID/VssID).</p>
              </div>
              <i class="faq-toggle bi bi-chevron-right"></i>
            </div><!-- End Faq item-->

            <div class="faq-item">
              <h3>Tôi có thể đặt lịch khám cho con nhỏ hoặc bố mẹ trên cùng tài khoản không?</h3>
              <div class="faq-content">
                <p>Có. Tại màn hình Đặt lịch khám, bạn chỉ cần chọn mục "Đặt cho người thân (Con nhỏ / Bố mẹ)" và nhập họ tên, ngày sinh của người đi khám. Hệ thống lưu sẵn danh sách người thân để bạn chọn lại chỉ với 1 chạm ở các lần khám sau.</p>
              </div>
              <i class="faq-toggle bi bi-chevron-right"></i>
            </div><!-- End Faq item-->

            <div class="faq-item">
              <h3>Chưa biết triệu chứng của mình thuộc chuyên khoa nào thì làm sao?</h3>
              <div class="faq-content">
                <p>Bạn có thể sử dụng công cụ "Tư vấn triệu chứng" ngay trên thanh menu để mô tả các biểu hiện đang gặp phải. Hệ thống sẽ phân tích dấu hiệu lâm sàng và gợi ý đúng chuyên khoa, bác sĩ phụ trách cũng như gói khám phù hợp.</p>
              </div>
              <i class="faq-toggle bi bi-chevron-right"></i>
            </div><!-- End Faq item-->

          </div>

        </div><!-- End Faq Column-->

      </div>

    </div>

  </section><!-- /Faq Section -->

  <!-- Gallery Section -->
  <section id="gallery" class="gallery section light-background">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
      <h2>Hình ảnh Phòng khám &amp; Trang thiết bị</h2>
      <p>Không gian tiếp đón hiện đại, hệ thống phòng xét nghiệm ISO 15189 và buồng khám tiêu chuẩn tại 125 Thái Thịnh</p>
    </div><!-- End Section Title -->

    <div class="container-fluid" data-aos="fade-up" data-aos-delay="100">

      <div class="row g-0">
        @for($g = 1; $g <= 8; $g++)
          <div class="col-lg-3 col-md-4">
            <div class="gallery-item">
              <a href="{{ asset('assets/img/gallery/gallery-' . $g . '.jpg') }}" class="glightbox" data-gallery="images-gallery">
                <img src="{{ asset('assets/img/gallery/gallery-' . $g . '.jpg') }}" alt="Hình ảnh phòng khám {{ $g }}" class="img-fluid">
              </a>
            </div>
          </div><!-- End Gallery Item -->
        @endfor
      </div>

    </div>

  </section><!-- /Gallery Section -->

  <!-- Contact Section -->
  <section id="contact" class="contact section">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
      <h2>Liên hệ &amp; Chỉ đường</h2>
      <p>Số 125 phố Thái Thịnh, phường Thịnh Quang, quận Đống Đa, Hà Nội · Mở cửa 07:30 – 20:00 tất cả các ngày trong tuần</p>
    </div><!-- End Section Title -->

    <div class="container" data-aos="fade-up" data-aos-delay="100">

      <div class="row gy-4">

        <div class="col-lg-4">
          <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="300">
            <i class="bi bi-geo-alt flex-shrink-0"></i>
            <div>
              <h3>Địa chỉ Phòng khám</h3>
              <p>125 Thái Thịnh, Thịnh Quang, Đống Đa, Hà Nội</p>
            </div>
          </div><!-- End Info Item -->

          <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="400">
            <i class="bi bi-telephone flex-shrink-0"></i>
            <div>
              <h3>Tổng đài Đặt lịch &amp; CSKH</h3>
              <p>1900 1234 (07:30 – 20:00)</p>
            </div>
          </div><!-- End Info Item -->

          <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="500">
            <i class="bi bi-envelope flex-shrink-0"></i>
            <div>
              <h3>Email Hỗ trợ</h3>
              <p>cskh@thaithinhmedic.vn</p>
            </div>
          </div><!-- End Info Item -->

        </div>

        <div class="col-lg-8">
          <form action="{{ route('advisor.index') }}" method="GET" class="php-email-form" data-aos="fade-up" data-aos-delay="200">
            <div class="row gy-4">

              <div class="col-md-6">
                <input type="text" name="name" class="form-control" placeholder="Họ và tên người bệnh">
              </div>

              <div class="col-md-6">
                <select name="patient_group" class="form-select" style="height: 44px; border-radius: 0; font-size: 14px;">
                  <option value="adult">Đối tượng: Người lớn</option>
                  <option value="child">Đối tượng: Trẻ em (&lt; 15 tuổi)</option>
                  <option value="pregnant">Đối tượng: Thai phụ / Sản khoa</option>
                  <option value="elderly">Đối tượng: Người cao tuổi</option>
                </select>
              </div>

              <div class="col-md-12">
                <input type="text" class="form-control" name="symptom" placeholder="Nhập triệu chứng cần tư vấn chuyên khoa (VD: đau đầu, sốt cao, tức ngực...)" required>
              </div>

              <div class="col-md-12 text-center">
                <button type="submit">Phân tích Triệu chứng &amp; Gợi ý Chuyên khoa</button>
              </div>

            </div>
          </form>
        </div><!-- End Contact Form -->

      </div>

    </div>

  </section><!-- /Contact Section -->
@endsection

@php
    $heroSearchCatalog = [];
    foreach ($services as $svc) {
        $heroSearchCatalog[] = [
            'type' => 'Gói khám',
            'badge' => 'primary',
            'title' => $svc->name,
            'sub' => ($svc->department->name ?? 'Đa khoa') . ' · ' . number_format($svc->price ?? 300000, 0, ',', '.') . 'đ',
            'url' => route('appointments.create', ['department_id' => $svc->department_id, 'service_id' => $svc->id]),
        ];
    }
    foreach ($doctors as $doc) {
        $heroSearchCatalog[] = [
            'type' => 'Bác sĩ',
            'badge' => 'success',
            'title' => $doc->user->name ?? 'BS. Chuyên khoa',
            'sub' => ($doc->department->name ?? 'Đa khoa') . ' · Đặt lịch trực tiếp',
            'url' => route('appointments.create', ['doctor_id' => $doc->id, 'department_id' => $doc->department_id]),
        ];
    }
    foreach ($departments as $dept) {
        $heroSearchCatalog[] = [
            'type' => 'Chuyên khoa',
            'badge' => 'info',
            'title' => $dept->name,
            'sub' => 'Khám chuyên sâu · Hỗ trợ BHYT',
            'url' => route('appointments.create', ['department_id' => $dept->id]),
        ];
    }
@endphp
@push('scripts')
<script>
  (function () {
    const searchInput = document.getElementById('heroSearchInput');
    const dropdown = document.getElementById('heroSearchDropdown');
    const catalog = @json($heroSearchCatalog);
    const advisorUrlBase = @json(route('advisor.index'));

    if (searchInput && dropdown) {
      searchInput.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        if (q.length < 2) {
          dropdown.classList.add('d-none');
          dropdown.innerHTML = '';
          return;
        }

        const matches = catalog.filter(function (item) {
          return item.title.toLowerCase().indexOf(q) !== -1 || item.sub.toLowerCase().indexOf(q) !== -1;
        }).slice(0, 6);

        let html = '<div class="small fw-bold text-muted mb-2">Gợi ý nhanh từ hệ thống 125 Thái Thịnh:</div>';
        matches.forEach(function (m) {
          html += '<a href="' + m.url + '" class="d-flex justify-content-between align-items-center p-2 rounded-2 text-decoration-none mb-1" style="color: var(--heading-color); border: 1px solid #e2e8f0;">' +
              '<div>' +
                  '<span class="badge bg-' + m.badge + ' bg-opacity-10 text-' + m.badge + ' me-2">' + m.type + '</span>' +
                  '<strong style="font-size: 0.9rem;">' + m.title + '</strong>' +
                  '<div class="small text-muted">' + m.sub + '</div>' +
              '</div>' +
              '<span class="small fw-semibold" style="color: var(--accent-color);">Đặt lịch →</span>' +
          '</a>';
        });

        html += '<a href="' + advisorUrlBase + '?symptom=' + encodeURIComponent(this.value.trim()) + '" class="d-flex align-items-center justify-content-between p-2 rounded-2 text-decoration-none mt-2" style="background: #f1f7fc; color: #1977cc; border: 1px dashed #1977cc;">' +
            '<span><i class="bi bi-clipboard2-pulse me-1"></i>Phân tích triệu chứng <strong>"' + this.value.trim() + '"</strong> &amp; gợi ý chuyên khoa</span>' +
            '<span class="fw-bold">Chẩn đoán →</span>' +
        '</a>';

        dropdown.innerHTML = html;
        dropdown.classList.remove('d-none');
      });

      document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
          dropdown.classList.add('d-none');
        }
      });
    }

    // Cascade Filtering cho biểu mẫu Đặt lịch nhanh (#appointment)
    const deptSelect = document.getElementById('homeQuickDept');
    const docSelect = document.getElementById('homeQuickDoctor');
    const svcSelect = document.getElementById('homeQuickService');

    function filterQuickOptionsByDept(selectedDeptId) {
      if (docSelect) {
        Array.from(docSelect.options).forEach(function (opt) {
          if (!opt.value) return;
          const match = !selectedDeptId || String(opt.getAttribute('data-dept')) === String(selectedDeptId);
          opt.hidden = !match;
          opt.disabled = !match;
        });
        const currentDocOpt = docSelect.options[docSelect.selectedIndex];
        if (currentDocOpt && currentDocOpt.disabled) {
          docSelect.value = '';
        }
      }

      if (svcSelect) {
        Array.from(svcSelect.options).forEach(function (opt) {
          if (!opt.value) return;
          const match = !selectedDeptId || String(opt.getAttribute('data-dept')) === String(selectedDeptId);
          opt.hidden = !match;
          opt.disabled = !match;
        });
        const currentSvcOpt = svcSelect.options[svcSelect.selectedIndex];
        if (currentSvcOpt && currentSvcOpt.disabled) {
          svcSelect.value = '';
        }
      }
    }

    if (deptSelect) {
      deptSelect.addEventListener('change', function () {
        filterQuickOptionsByDept(this.value);
      });
    }

    if (docSelect && deptSelect) {
      docSelect.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const docDept = opt ? opt.getAttribute('data-dept') : '';
        if (docDept && deptSelect.value !== docDept) {
          deptSelect.value = docDept;
          filterQuickOptionsByDept(docDept);
        }
      });
    }

    if (svcSelect && deptSelect) {
      svcSelect.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const svcDept = opt ? opt.getAttribute('data-dept') : '';
        if (svcDept && deptSelect.value !== svcDept) {
          deptSelect.value = svcDept;
          filterQuickOptionsByDept(svcDept);
        }
      });
    }
  })();
</script>
@endpush
