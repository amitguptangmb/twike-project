@extends('layouts.auth')

@section('title', 'Reset Password')
@section('heading', 'Reset Password')
@section('subtitle', 'Enter your new password below.')

@section('content')
    <form method="POST" action="{{ route('user.password.reset.submit') }}" autocomplete="off">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="code" value="{{ $code }}">
        <input type="hidden" name="flag" value="{{ $flag }}">

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="text" class="form-control" value="{{ $email }}" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" autocomplete="off" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" autocomplete="off" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Update Password</button>
    </form>
@endsection
