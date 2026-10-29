{{-- Email Verification Form (Hidden) --}}
<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

{{-- Profile Update Form --}}
<form method="post"
      action="{{ route('profile.update') }}"
      class="needs-validation profile-patient-form"
      novalidate
      enctype="multipart/form-data">
    @csrf
    @method('patch')

    @php
        $patient = $user->patient ?? null;
        $avatarSrc = ($patient && !empty($patient->avatar))
            ? Storage::url($patient->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=EBF5FF&color=0284C7&bold=true&size=160';
        $birthdate = $patient->birthdate ?? $user->birthdate ?? null;
        $formattedBirthdate = $birthdate
            ? (is_string($birthdate)
                ? \Carbon\Carbon::parse($birthdate)->format('Y-m-d')
                : $birthdate->format('Y-m-d'))
            : '';
    @endphp

    {{-- Sleek Clinical Avatar & Patient Identity Banner --}}
    <div class="d-flex flex-column flex-sm-row align-items-center gap-3 p-3 rounded-3 bg-light border mb-4">
        <div class="position-relative flex-shrink-0">
            <img id="avatarPreview"
                 src="{{ $avatarSrc }}"
                 alt="Ảnh đại diện"
                 class="rounded-circle border border-2 border-primary-subtle shadow-sm"
                 width="88"
                 height="88"
                 style="object-fit: cover;">
            <label for="avatar"
                   class="position-absolute bottom-0 end-0 rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center shadow-sm"
                   style="width: 28px; height: 28px; cursor: pointer; border: 2px solid #FFFFFF;"
                   title="Đổi ảnh đại diện">
                <i class="fas fa-camera" style="font-size: 11px;"></i>
            </label>
        </div>
        <div class="flex-grow-1 text-center text-sm-start">
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-sm-start gap-2 mb-1">
                <h6 class="fw-bold text-dark mb-0">{{ $patient->name ?? $user->name }}</h6>
                <span class="med-badge-spec">
                    <i class="fas fa-id-card-clip"></i> Mã BN: #BN-{{ str_pad($patient->id ?? $user->id, 5, '0', STR_PAD_LEFT) }}
                </span>
                @if(!empty($patient->insurance_number ?? $user->insurance_number))
                    <span class="med-badge-bhyt">
                        <i class="fas fa-shield-alt"></i> Đã liên kết BHYT
                    </span>
                @endif
            </div>
            <p class="text-muted small mb-2">Ảnh đại diện hồ sơ y tế điện tử (Định dạng JPG, PNG tối đa 2MB)</p>
            <div>
                <label for="avatar" class="btn btn-tiki-outline btn-sm py-1 px-3" style="font-size: 12px; cursor: pointer;">
                    <i class="fas fa-upload me-1 text-primary"></i> Chọn ảnh mới
                </label>
                <input type="file"
                       name="avatar"
                       id="avatar"
                       accept="image/*"
                       class="d-none @error('avatar') is-invalid @enderror">
                @error('avatar')
                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Full Name --}}
        <div class="col-md-6">
            <label for="name" class="tiki-form-label">
                Họ và tên bệnh nhân <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-secondary">
                    <i class="far fa-user"></i>
                </span>
                <input type="text"
                       id="name"
                       name="name"
                       class="form-control tiki-form-control border-start-0 @error('name') is-invalid @enderror"
                       value="{{ old('name', $patient->name ?? $user->name) }}"
                       required
                       autocomplete="name"
                       placeholder="Nhập họ và tên đầy đủ">
            </div>
            @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Email --}}
        <div class="col-md-6">
            <label for="email" class="tiki-form-label">
                Địa chỉ Email <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-secondary">
                    <i class="far fa-envelope"></i>
                </span>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control tiki-form-control border-start-0 @error('email') is-invalid @enderror"
                       value="{{ old('email', $user->email) }}"
                       required
                       autocomplete="username"
                       placeholder="example@email.com">
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-1 small text-warning">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    Email chưa được xác minh.
                    <button form="send-verification" class="btn btn-link p-0 ms-1 text-warning small">
                        Gửi lại email xác minh
                    </button>
                </div>
            @endif
        </div>

        {{-- Phone Number --}}
        <div class="col-md-6">
            <label for="phone" class="tiki-form-label">Số điện thoại liên hệ</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-secondary">
                    <i class="fas fa-phone-alt"></i>
                </span>
                <input type="tel"
                       id="phone"
                       name="phone"
                       class="form-control tiki-form-control border-start-0 @error('phone') is-invalid @enderror"
                       value="{{ old('phone', $patient->phone ?? $user->phone) }}"
                       placeholder="Ví dụ: 0912345678">
            </div>
            @error('phone')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Birthdate --}}
        <div class="col-md-6">
            <label for="birthdate" class="tiki-form-label">Ngày tháng năm sinh</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-secondary">
                    <i class="far fa-calendar-alt"></i>
                </span>
                <input type="date"
                       id="birthdate"
                       name="birthdate"
                       max="{{ now()->subDay()->format('Y-m-d') }}"
                       class="form-control tiki-form-control border-start-0 @error('birthdate') is-invalid @enderror"
                       value="{{ old('birthdate', $formattedBirthdate) }}">
            </div>
            @error('birthdate')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Gender --}}
        <div class="col-md-6">
            <label for="gender" class="tiki-form-label">Giới tính</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-secondary">
                    <i class="fas fa-venus-mars"></i>
                </span>
                <select id="gender"
                        name="gender"
                        class="form-select tiki-form-select border-start-0 @error('gender') is-invalid @enderror">
                    <option value="">-- Chọn giới tính --</option>
                    <option value="male" {{ old('gender', $patient->gender ?? $user->gender) == 'male' ? 'selected' : '' }}>Nam</option>
                    <option value="female" {{ old('gender', $patient->gender ?? $user->gender) == 'female' ? 'selected' : '' }}>Nữ</option>
                    <option value="other" {{ old('gender', $patient->gender ?? $user->gender) == 'other' ? 'selected' : '' }}>Khác</option>
                </select>
            </div>
            @error('gender')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Insurance Number --}}
        <div class="col-md-6">
            <label for="insurance_number" class="tiki-form-label d-flex justify-content-between align-items-center">
                <span>Mã số thẻ Bảo hiểm Y tế (BHYT)</span>
                <span id="bhytValidationHint" class="small text-muted" style="font-size: 11px;">Chuẩn 10 hoặc 15 ký tự</span>
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-secondary">
                    <i class="fas fa-shield-alt text-success"></i>
                </span>
                <input type="text"
                       id="insurance_number"
                       name="insurance_number"
                       maxlength="15"
                       class="form-control tiki-form-control border-start-0 font-monospace text-uppercase @error('insurance_number') is-invalid @enderror"
                       value="{{ old('insurance_number', $patient->insurance_number ?? $user->insurance_number) }}"
                       placeholder="VD: DN4010123456789">
            </div>
            @error('insurance_number')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Address --}}
        <div class="col-12">
            <label for="address" class="tiki-form-label">Địa chỉ thường trú / Liên hệ</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-secondary">
                    <i class="fas fa-map-marker-alt"></i>
                </span>
                <textarea id="address"
                          name="address"
                          class="form-control tiki-form-control border-start-0 @error('address') is-invalid @enderror"
                          rows="2"
                          placeholder="Số nhà, đường/phố, phường/xã, quận/huyện, tỉnh/thành phố">{{ old('address', $patient->address ?? $user->address) }}</textarea>
            </div>
            @error('address')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
        <button type="reset" class="btn btn-tiki-outline px-4">
            <i class="fas fa-undo me-1"></i> Đặt lại
        </button>
        <button type="submit" class="btn btn-tiki-primary px-4">
            <i class="fas fa-save me-1.5"></i> Lưu thông tin hồ sơ
        </button>
    </div>
</form>

@push('scripts')
<script>
    document.getElementById('avatar')?.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
            reader.readAsDataURL(this.files[0]);
        }
    });

    (function() {
        const bhytInput = document.getElementById('insurance_number');
        const bhytHint = document.getElementById('bhytValidationHint');
        if (!bhytInput || !bhytHint) return;

        function checkBhyt() {
            const val = bhytInput.value.replace(/\s+/g, '').toUpperCase();
            bhytInput.value = val;
            if (!val) {
                bhytHint.className = 'small text-muted';
                bhytHint.innerHTML = 'Chuẩn 10 hoặc 15 ký tự';
                return;
            }
            if (/^[A-Z0-9]{10}$/.test(val) || /^[A-Z]{2}[1-5][0-9]{12}$/.test(val) || /^[A-Z0-9]{15}$/.test(val)) {
                bhytHint.className = 'small text-success fw-semibold';
                bhytHint.innerHTML = '<i class="fas fa-check-circle me-1"></i>Đúng chuẩn mã BHXH (' + val.length + ' ký tự)';
            } else {
                bhytHint.className = 'small text-warning fw-semibold';
                bhytHint.innerHTML = '<i class="fas fa-info-circle me-1"></i>Đã nhập ' + val.length + ' ký tự (cần 10 hoặc 15)';
            }
        }
        bhytInput.addEventListener('input', checkBhyt);
        checkBhyt();
    })();
</script>
@endpush
