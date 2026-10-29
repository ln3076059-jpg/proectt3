{{-- Password Update Form --}}
<form method="post" action="{{ route('password.update') }}" class="update-password-form">
    @csrf
    @method('put')

    <div class="row g-3">
        {{-- Current Password --}}
        <div class="col-12">
            <label for="update_password_current_password" class="tiki-form-label">
                Mật khẩu hiện tại <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-secondary">
                    <i class="fas fa-lock"></i>
                </span>
                <input type="password"
                       id="update_password_current_password"
                       name="current_password"
                       class="form-control tiki-form-control border-start-0 @error('current_password','updatePassword') is-invalid @enderror"
                       autocomplete="current-password"
                       placeholder="Nhập mật khẩu hiện tại"
                       required>
                <button type="button"
                        class="btn btn-outline-secondary border-start-0"
                        onclick="toggleProfilePassword('update_password_current_password', this)">
                    <i class="far fa-eye"></i>
                </button>
            </div>
            @error('current_password','updatePassword')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- New Password --}}
        <div class="col-md-6">
            <label for="update_password_password" class="tiki-form-label">
                Mật khẩu mới <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-secondary">
                    <i class="fas fa-key"></i>
                </span>
                <input type="password"
                       id="update_password_password"
                       name="password"
                       class="form-control tiki-form-control border-start-0 @error('password','updatePassword') is-invalid @enderror"
                       autocomplete="new-password"
                       placeholder="Tối thiểu 8 ký tự"
                       required>
                <button type="button"
                        class="btn btn-outline-secondary border-start-0"
                        onclick="toggleProfilePassword('update_password_password', this)">
                    <i class="far fa-eye"></i>
                </button>
            </div>
            @error('password','updatePassword')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div class="col-md-6">
            <label for="update_password_password_confirmation" class="tiki-form-label">
                Xác nhận mật khẩu mới <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-secondary">
                    <i class="fas fa-check-double"></i>
                </span>
                <input type="password"
                       id="update_password_password_confirmation"
                       name="password_confirmation"
                       class="form-control tiki-form-control border-start-0 @error('password_confirmation','updatePassword') is-invalid @enderror"
                       autocomplete="new-password"
                       placeholder="Nhập lại mật khẩu mới"
                       required>
                <button type="button"
                        class="btn btn-outline-secondary border-start-0"
                        onclick="toggleProfilePassword('update_password_password_confirmation', this)">
                    <i class="far fa-eye"></i>
                </button>
            </div>
            @error('password_confirmation','updatePassword')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password Requirements --}}
        <div class="col-12">
            <div class="p-2.5 rounded-3 bg-light border d-flex flex-wrap align-items-center gap-3 small">
                <span class="fw-semibold text-secondary"><i class="fas fa-shield-alt text-primary me-1"></i>Độ mạnh mật khẩu:</span>
                <span id="length-req" class="text-muted"><i class="far fa-circle me-1"></i>8+ ký tự</span>
                <span id="uppercase-req" class="text-muted"><i class="far fa-circle me-1"></i>Chữ in hoa</span>
                <span id="number-req" class="text-muted"><i class="far fa-circle me-1"></i>Chữ số</span>
                <span id="special-req" class="text-muted"><i class="far fa-circle me-1"></i>Ký tự đặc biệt</span>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="d-flex justify-content-end gap-2 mt-4 border-top pt-3">
        <button type="reset" class="btn btn-tiki-outline px-4">
            <i class="fas fa-undo me-1"></i> Đặt lại
        </button>
        <button type="submit" class="btn btn-tiki-primary px-4">
            <i class="fas fa-key me-1.5"></i> Cập nhật mật khẩu
        </button>
    </div>
</form>

@push('scripts')
<script>
    document.getElementById('update_password_password')?.addEventListener('input', function() {
        const p = this.value;
        const req = {
            length: p.length >= 8,
            uppercase: /[A-Z]/.test(p),
            number: /[0-9]/.test(p),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(p)
        };
        for (const [key, ok] of Object.entries(req)) {
            const el = document.getElementById(key + '-req');
            if (el) {
                el.className = ok ? 'text-success fw-semibold' : 'text-muted';
                el.querySelector('i').className = ok ? 'fas fa-check-circle me-1' : 'far fa-circle me-1';
            }
        }
    });

    function toggleProfilePassword(id, btn) {
        const input = document.getElementById(id);
        if (!input) return;
        const icon = btn?.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon?.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon?.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
@endpush
