@extends('layouts.app')
@section('title', 'New Vendor')

@section('content')
<div class="row"><div class="col-lg-8">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('vendors.store') }}">
            @csrf
            @include('vendors._form')
            <button class="btn btn-primary">Create Vendor</button>
            <a href="{{ route('vendors.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div></div>
</div></div>
@endsection
