@extends('layouts.auth')

@section('title', 'Partner Login')
@section('subtitle', 'Sign in to continue to the Partner Portal.')

@section('content')
    <form method="POST" action="{{ route('partner.login.submit') }}" autocomplete="off">
        @csrf
        <div class="mb-3">
            <label class="form-label">User ID</label>
            <input type="text" name="login" class="form-control" placeholder="Enter user id..." value="{{ old('login') }}" autocomplete="off" required>
        </div>
        <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between">
                <label class="form-label">Password</label>
                <a href="{{ route('partner.forgot-password') }}" class="small">Forgot password?</a>
            </div>
            <div class="np-pass-wrap">
                <input type="password" id="partner_password" name="password" class="form-control" placeholder="Password" autocomplete="off" required>
                <button type="button" class="np-pass-toggle" onclick="npTogglePassword(this, 'partner_password')" tabindex="-1"><i class="ri-eye-off-line"></i></button>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Captcha</label>
            <div class="d-flex align-items-center gap-2">
                <input type="text" name="captcha" class="form-control" placeholder="Captcha" autocomplete="off" required>
                <img src="{{ route('partner.captcha') }}" alt="captcha" id="captchaImg" style="height:44px;border-radius:.25rem;cursor:pointer" title="Click to refresh" onclick="this.src='{{ route('partner.captcha') }}?t='+Date.now()">
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <hr>
    <a href="{{ route('partner.login') }}" class="btn btn-light w-100 mb-2"><i class='ri-user-line'></i> Merchant Login</a>
    <a href="{{ route('admin.login') }}" class="btn btn-light w-100"><i class='ri-user-line'></i> Admin Login</a>
@endsection
