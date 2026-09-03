@extends('layouts.admin')

@section('title', $page)

@section('content')
    <div class="card np-card">
        <div class="card-body text-center py-5">
            <i class="ri-time-line fs-1 text-muted"></i>
            <h5 class="mt-3">{{ $page }} isn't ported yet</h5>
            <p class="text-muted mb-0">This page is next in the conversion queue - see MIGRATION_NOTES.md for progress.</p>
        </div>
    </div>
@endsection
