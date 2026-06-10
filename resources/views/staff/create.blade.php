@extends('layouts.app')
@section('title', 'Add Staff Member')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('society-staff.index') }}">Staff</a></li>
    <li class="breadcrumb-item active">Add Staff Member</li>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('society-staff.store') }}" enctype="multipart/form-data">
            @csrf
            @include('staff._form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Staff Member</button>
                <a href="{{ route('society-staff.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
