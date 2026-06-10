@extends('layouts.app')
@section('title', 'Edit Staff Member')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('society-staff.index') }}">Staff</a></li>
    <li class="breadcrumb-item"><a href="{{ route('society-staff.show', $staff) }}">{{ $staff->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('society-staff.update', $staff) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('staff._form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
                <a href="{{ route('society-staff.show', $staff) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
