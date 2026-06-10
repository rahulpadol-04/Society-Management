@extends('layouts.app')
@section('title', 'Add Tower')

@section('content')
<div class="row"><div class="col-lg-9">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('towers.store') }}">
            @include('structure.towers._form')
            <div class="mt-3">
                <button class="btn btn-primary">Create Tower</button>
                <a href="{{ route('structure.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div></div>
</div></div>
@endsection
