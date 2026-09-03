@extends('layouts.user')

@section('title', $page)

@section('content')
    <div class="card np-card">
        <div class="card-body text-center py-5">
            <i class="ri-time-line fs-1 text-muted"></i>
            <h5 class="mt-3">{{ $page }} isn't built yet</h5>
            <p class="text-muted mb-0">No screenshot or source exists for this page yet - see USER_PANEL_ANALYSIS.md.</p>
        </div>
    </div>
@endsection
