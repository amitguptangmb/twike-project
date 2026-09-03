@extends('layouts.user')

@section('title', 'My Account')

@section('content')
    <div class="card np-card">
        <div class="card-header p-0">
            <ul class="nav nav-tabs card-header-tabs px-2 pt-2" id="accountTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-info-btn" data-bs-toggle="tab" data-bs-target="#tab-info" type="button" role="tab">Account Info</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-password-btn" data-bs-toggle="tab" data-bs-target="#tab-password" type="button" role="tab">Change Password</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-message-btn" data-bs-toggle="tab" data-bs-target="#tab-message" type="button" role="tab">Message</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-support-btn" data-bs-toggle="tab" data-bs-target="#tab-support" type="button" role="tab">Support</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="accountTabsContent">

                {{-- Account Info --}}
                <div class="tab-pane fade show active" id="tab-info" role="tabpanel">
                    <form method="POST" action="{{ route('user.account.update') }}" style="max-width:500px">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $account->Name ?? '') }}" maxlength="200">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Business Name</label>
                            <input type="text" name="business_name" class="form-control" value="{{ old('business_name', $account->BusinessName ?? '') }}" maxlength="200">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email ID</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $account->EmailId ?? '') }}" maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $account->Mobile ?? '') }}" maxlength="15">
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>

                {{-- Change Password - reuses the existing standalone Change Password page's controller/route unchanged --}}
                <div class="tab-pane fade" id="tab-password" role="tabpanel">
                    <form method="POST" action="{{ route('user.password.update') }}" autocomplete="off" id="accountChangePasswordForm" style="max-width:700px">
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
                                <input type="password" name="new_password" id="accountNewPassword" class="form-control" autocomplete="off" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" id="accountConfirmPassword" class="form-control" autocomplete="off" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>

                {{-- Message - no screenshot of this tab's content and no ASP.NET source exists, so this
                     is a placeholder rather than an invented feature. See USER_PANEL_ANALYSIS.md. --}}
                <div class="tab-pane fade" id="tab-message" role="tabpanel">
                    <p class="text-muted mb-0">Messages aren't available yet.</p>
                </div>

                {{-- Support --}}
                <div class="tab-pane fade" id="tab-support" role="tabpanel">
                    <div class="card np-card" style="max-width:500px">
                        <div class="card-body">
                            <input type="text" class="form-control" value="Email:COINNECTED@gmail.com" readonly>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('accountChangePasswordForm').addEventListener('submit', function (e) {
            var pw = document.getElementById('accountNewPassword').value;
            var confirm = document.getElementById('accountConfirmPassword').value;
            var rule = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            if (!rule.test(pw)) {
                alert('Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters!');
                e.preventDefault();
            } else if (pw !== confirm) {
                alert('confrim Password and Password do not matched');
                e.preventDefault();
            }
        });
    </script>
@endpush
