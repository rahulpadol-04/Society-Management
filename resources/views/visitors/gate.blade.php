@extends('layouts.app')
@section('title', 'Gate Console')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('visitors.index') }}">Visitors</a></li>
    <li class="breadcrumb-item active">Gate Console</li>
@endsection

@section('content')
<div class="row g-3">

    {{-- QR / Code Check-in --}}
    <div class="col-lg-5">
        <div class="card shadow-sm mb-3"><div class="card-body">
            <h3 class="h6 mb-3"><i class="bi bi-qr-code-scan"></i> Check-in by Pass Code</h3>
            <form method="POST" action="{{ route('visitors.checkin.code') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label">Pass Code</label>
                    <input type="text" name="code" class="form-control font-monospace @error('code') is-invalid @enderror"
                           placeholder="VP-YYMM-XXXXX" autofocus>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Gate</label>
                    <input type="text" name="gate" class="form-control" placeholder="Main Gate, Side Gate…">
                </div>
                <button class="btn btn-primary w-100"><i class="bi bi-qr-code"></i> Validate &amp; Check In</button>
            </form>
        </div></div>

        {{-- Walk-in Check-in --}}
        <div class="card shadow-sm"><div class="card-body">
            <h3 class="h6 mb-3"><i class="bi bi-person-add"></i> Walk-in Check-in</h3>
            <form method="POST" action="{{ route('visitors.checkin') }}">
                @csrf
                <div class="row g-2">
                    <div class="col-8">
                        <label class="form-label">Visitor Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-4">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            @foreach (['guest', 'delivery', 'cab', 'service', 'vendor'] as $t)
                                <option value="{{ $t }}" @selected(old('type', 'guest') === $t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Flat</label>
                        <select name="flat_id" class="form-select">
                            <option value="">— Select —</option>
                            @foreach ($flats as $flat)
                                <option value="{{ $flat->id }}" @selected(old('flat_id') == $flat->id)>
                                    {{ $flat->label ?? $flat->number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Vehicle No.</label>
                        <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Gate</label>
                        <input type="text" name="gate" value="{{ old('gate') }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Purpose</label>
                        <input type="text" name="purpose" value="{{ old('purpose') }}" class="form-control">
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-success w-100"><i class="bi bi-box-arrow-in-right"></i> Check In</button>
                </div>
            </form>
        </div></div>
    </div>

    {{-- Currently Inside --}}
    <div class="col-lg-7">
        <div class="card shadow-sm"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h6 mb-0"><i class="bi bi-people-fill text-success"></i> Currently Inside ({{ $inside->count() }})</h3>
            </div>
            @if ($inside->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead>
                            <tr>
                                <th>Name</th><th>Type</th><th>Flat</th><th>Pass Code</th>
                                <th>Checked In</th><th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inside as $log)
                                <tr>
                                    <td class="fw-semibold">{{ $log->name }}</td>
                                    <td><span class="badge text-bg-light text-capitalize">{{ $log->type }}</span></td>
                                    <td class="small">{{ $log->flat?->number ?? '—' }}</td>
                                    <td><span class="font-monospace small">{{ $log->pass?->code ?? 'Walk-in' }}</span></td>
                                    <td class="text-muted small">{{ $log->checked_in_at->format('H:i') }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('visitors.checkout', $log) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-warning" title="Check out">
                                                <i class="bi bi-box-arrow-right"></i> Check Out
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted py-5">
                    <i class="bi bi-door-closed display-4 d-block mb-2"></i>
                    No visitors inside at the moment.
                </div>
            @endif
        </div></div>
    </div>

</div>
@endsection
