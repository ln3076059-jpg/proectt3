<div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
    <div>
        <p class="text-muted small mb-0">
            Khi xóa tài khoản, toàn bộ thông tin đăng nhập cá nhân sẽ bị hủy. Lưu ý: Các tài khoản đã phát sinh bệnh án điện tử sẽ được hệ thống bảo lưu theo quy định lưu trữ Y tế.
        </p>
    </div>
    <div class="flex-shrink-0">
        <button type="button"
                class="btn btn-outline-danger btn-sm rounded-3 px-3 py-2 fw-semibold"
                data-bs-toggle="modal"
                data-bs-target="#confirmUserDeletion">
            <i class="fas fa-trash-alt me-2"></i> Yêu cầu xóa tài khoản
        </button>
    </div>
</div>

{{-- Confirmation Modal --}}
<div class="modal fade"
     id="confirmUserDeletion"
     tabindex="-1"
     aria-labelledby="confirmUserDeletionLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white border-0 py-3 px-4">
                <h6 class="modal-title fw-bold mb-0" id="confirmUserDeletionLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i> Xác nhận xóa tài khoản
                </h6>
                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <div class="modal-body p-4">
                    <p class="text-dark fw-semibold mb-2">
                        Bạn có chắc chắn muốn xóa tài khoản tại Phòng khám Thái Thịnh?
                    </p>
                    <p class="text-muted small mb-3">
                        Vui lòng nhập mật khẩu hiện tại của bạn để xác nhận yêu cầu xóa tài khoản vĩnh viễn.
                    </p>

                    <div class="mb-2">
                        <label for="delete_account_password" class="tiki-form-label">
                            Mật khẩu xác nhận <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-secondary">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password"
                                   class="form-control tiki-form-control border-start-0"
                                   id="delete_account_password"
                                   name="password"
                                   required
                                   placeholder="Nhập mật khẩu của bạn"
                                   autocomplete="current-password">
                            <button class="btn btn-outline-secondary border-start-0"
                                    type="button"
                                    onclick="toggleProfilePassword('delete_account_password', this)">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>

                        @error('password', 'userDeletion')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-3 px-4">
                    <button type="button"
                            class="btn btn-tiki-outline btn-sm px-3"
                            data-bs-dismiss="modal">
                        Hủy bỏ
                    </button>
                    <button type="submit"
                            class="btn btn-danger btn-sm rounded-3 px-3 py-2 fw-bold">
                        <i class="fas fa-trash-alt me-1"></i> Xác nhận xóa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
