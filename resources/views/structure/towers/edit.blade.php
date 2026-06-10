@extends('layouts.app')
@section('title', 'Edit '.$tower->name)

@section('content')
<div class="row"><div class="col-lg-9">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('towers.update', $tower) }}">
            @method('PUT')
            @include('structure.towers._form')
            <div class="mt-3">
                <button class="btn btn-primary">Save Changes</button>
                <a href="{{ route('towers.show', $tower) }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div></div>
</div></div>
@endsection
