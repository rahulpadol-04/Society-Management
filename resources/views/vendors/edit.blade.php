@extends('layouts.app')
@section('title', 'Edit Vendor')

@section('content')
<div class="row"><div class="col-lg-8">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('vendors.update', $vendor) }}">
            @csrf @method('PUT')
            @include('vendors._form')
            <button class="btn btn-primary">Save Changes</button>
            <a href="{{ route('vendors.show', $vendor) }}" class="btn btn-link">Cancel</a>
        </form>
    </div></div>
</div></div>
@endsection
