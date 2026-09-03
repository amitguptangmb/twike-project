@extends('layouts.admin')

@section('title', 'Change Password')

@section('content')
    <div class="card np-card">
        <div class="card-body">
            {{-- No server-side password strength/confirm-match validation on
                 purpose - the original admin/changepassword.aspx.cs has none
                 either (only client-side JS). See PasswordController.php. --}}
            <form method="POST" action="{{ route('admin.password.update') }}" autocomplete="off" id="changePasswordForm">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">User ID</label>
                        <input type="text" class="form-control" value="{{ $userId }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Old Password</label>
                        <input type="password" name="old_password" class="form-control" autocomplete="off" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" id="newPassword" class="form-control" autocomplete="off" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirmPassword" class="form-control" autocomplete="off" required>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Same client-side rules as the original changepassword.aspx's Allvalidate().
        document.getElementById('changePasswordForm').addEventListener('submit', function (e) {
            var oldPw = document.querySelector('[name=old_password]').value;
            var pw = document.getElementById('newPassword').value;
            var confirm = document.getElementById('confirmPassword').value;
            var rule = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            if (oldPw === '') {
                alert('* Please Old Enter Password');
                e.preventDefault();
            } else if (!rule.test(pw)) {
                alert('* Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters!');
                e.preventDefault();
            } else if (pw !== confirm) {
                alert('* confrim Password and Password do not matched');
                e.preventDefault();
            }
        });
    </script>
@endpush
