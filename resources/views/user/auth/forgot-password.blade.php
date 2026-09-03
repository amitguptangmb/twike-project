@extends('layouts.auth')

@section('title', 'Forgot Password')
@section('heading', 'Forgot Password?')
@section('subtitle', "Enter your email and we'll help you reset it.")

@section('content')
    <form method="POST" action="{{ route('user.forgot-password.submit') }}" autocomplete="off">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" autocomplete="off" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Submit</button>
    </form>
    <div class="text-center mt-3">
        <a class="small" href="{{ route('user.login') }}">Back to login</a>
    </div>
@endsection
