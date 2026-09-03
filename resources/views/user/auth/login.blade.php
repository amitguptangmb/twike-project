@extends('layouts.auth')

@section('title', 'User Login')
@section('subtitle', 'Sign in to continue to the User Portal.')

@section('content')
    <form method="POST" action="{{ route('user.login.submit') }}" autocomplete="off">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="text" name="email" class="form-control" placeholder="Enter email..." value="{{ old('email') }}" autocomplete="off" required>
        </div>
        <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between">
                <label class="form-label">Password</label>
                <a href="{{ route('user.forgot-password') }}" class="small">Forgot password?</a>
            </div>
            <div class="np-pass-wrap">
                <input type="password" id="user_password" name="password" class="form-control" placeholder="Password" autocomplete="off" required>
                <button type="button" class="np-pass-toggle" onclick="npTogglePassword(this, 'user_password')" tabindex="-1"><i class="ri-eye-off-line"></i></button>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Captcha</label>
            <div class="d-flex align-items-center gap-2">
                <input type="text" name="captcha" class="form-control" placeholder="Captcha" autocomplete="off" required>
                <img src="{{ route('user.captcha') }}" alt="captcha" id="captchaImg" style="height:44px;border-radius:.25rem;cursor:pointer" title="Click to refresh" onclick="this.src='{{ route('user.captcha') }}?t='+Date.now()">
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <hr>
    <a href="{{ route('admin.login') }}" class="btn btn-light w-100"><i class='ri-user-line'></i> Admin Login</a>
@endsection
