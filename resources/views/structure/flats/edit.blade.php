@extends('layouts.app')
@section('title', 'Edit Unit '.$flat->number)

@section('content')
<div class="row"><div class="col-lg-9">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('flats.update', $flat) }}">
            @method('PUT')
            @include('structure.flats._form')
            <div class="mt-3">
                <button class="btn btn-primary">Save Changes</button>
                <a href="{{ route('flats.show', $flat) }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div></div>
</div></div>
@endsection
