@extends('layouts.app')
@section('title', $resident->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('residents.index') }}">Residents</a></li>
    <li class="breadcrumb-item active">{{ $resident->name }}</li>
@endsection

@section('page-actions')
    @can('update', $resident)
        <a href="{{ route('residents.edit', $resident) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
    @endcan
    @can('delete', $resident)
        <form method="POST" action="{{ route('residents.destroy', $resident) }}" class="d-inline"
              data-confirm="Delete resident {{ $resident->name }}?">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
    @endcan
@endsection

@section('content')
<div class="row g-3">
    {{-- Profile card --}}
    <div class="col-lg-4">
        <div class="card shadow-sm text-center mb-3"><div class="card-body">
            @if ($resident->photo)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($resident->photo) }}"
                     class="rounded-circle mb-3" width="80" height="80" style="object-fit:cover" alt="Photo">
            @else
                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto mb-3"
                     style="width:80px;height:80px">
                    <i class="bi bi-person-fill text-white fs-3"></i>
                </div>
            @endif
            <h2 class="h5 mb-1">{{ $resident->name }}</h2>
            <span class="badge text-bg-{{ $resident->status === 'active' ? 'success' : 'secondary' }} text-capitalize mb-2">
                {{ str_replace('_', ' ', $resident->status) }}
            </span>
            <dl class="row small text-start mt-2 mb-0">
                <dt class="col-5">Type</dt><dd class="col-7 text-capitalize">{{ str_replace('_', ' ', $resident->type) }}</dd>
                <dt class="col-5">Flat</dt><dd class="col-7">{{ $resident->flat?->number ?? '—' }}</dd>
                <dt class="col-5">Email</dt><dd class="col-7">{{ $resident->email ?? '—' }}</dd>
                <dt class="col-5">Phone</dt><dd class="col-7">{{ $resident->phone ?? '—' }}</dd>
                <dt class="col-5">Move-in</dt><dd class="col-7">{{ $resident->move_in_date?->format('d M Y') ?? '—' }}</dd>
                <dt class="col-5">Move-out</dt><dd class="col-7">{{ $resident->move_out_date?->format('d M Y') ?? '—' }}</dd>
                <dt class="col-5">User</dt><dd class="col-7">{{ $resident->user?->name ?? '—' }}</dd>
                <dt class="col-5">Primary</dt><dd class="col-7">{{ $resident->is_primary ? 'Yes' : 'No' }}</dd>
            </dl>
        </div></div>

        {{-- Emergency contacts --}}
        <div class="card shadow-sm mb-3"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="h6 mb-0">Emergency Contacts</h3>
                @can('update', $resident)
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                @endcan
            </div>
            @forelse ($resident->emergencyContacts as $contact)
                <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                    <div>
                        <div class="fw-semibold small">{{ $contact->name }}
                            @if ($contact->is_primary)<span class="badge text-bg-warning ms-1">Primary</span>@endif
                        </div>
                        <div class="text-muted small">{{ $contact->phone }}{{ $contact->relation ? ' · '.$contact->relation : '' }}</div>
                    </div>
                    @can('update', $resident)
                        <form method="POST"
                              action="{{ route('residents.emergency-contacts.destroy', [$resident, $contact]) }}"
                              data-confirm="Remove this contact?" class="ms-2">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    @endcan
                </div>
            @empty
                <p class="text-muted small mb-0">No emergency contacts.</p>
            @endforelse
        </div></div>
    </div>

    <div class="col-lg-8">
        {{-- Family members --}}
        <div class="card shadow-sm mb-3"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="h6 mb-0">Family Members</h3>
                @can('create', App\Models\Resident::class)
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addFamilyModal">
                        <i class="bi bi-plus-lg"></i> Add
                    </button>
                @endcan
            </div>
            @if ($resident->familyMembers->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Name</th><th>Relation</th><th>Phone</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($resident->familyMembers as $member)
                            <tr>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->relation ?? '—' }}</td>
                                <td>{{ $member->phone ?? '—' }}</td>
                                <td><span class="badge text-bg-{{ $member->status === 'active' ? 'success' : 'secondary' }} text-capitalize">{{ $member->status }}</span></td>
                                <td class="text-end">
                                    @can('delete', $member)
                                        <form method="POST"
                                              action="{{ route('residents.family.destroy', [$resident, $member]) }}"
                                              data-confirm="Remove {{ $member->name }} from family?" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted small mb-0">No family members linked.</p>
            @endif
        </div></div>

        {{-- Vehicles --}}
        <div class="card shadow-sm"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="h6 mb-0">Vehicles</h3>
                @can('create', App\Models\Vehicle::class)
                    <a href="{{ route('vehicles.create') }}?resident_id={{ $resident->id }}"
                       class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg"></i> Add</a>
                @endcan
            </div>
            @if ($resident->vehicles->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Type</th><th>Registration</th><th>Make / Model</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($resident->vehicles as $vehicle)
                            <tr>
                                <td><span class="badge text-bg-light text-capitalize">{{ $vehicle->type }}</span></td>
                                <td class="fw-semibold">{{ $vehicle->registration_number }}</td>
                                <td>{{ trim(($vehicle->make ?? '').' '.($vehicle->model ?? '')) ?: '—' }}</td>
                                <td><span class="badge text-bg-{{ $vehicle->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($vehicle->status) }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted small mb-0">No vehicles registered.</p>
            @endif
        </div></div>
    </div>
</div>

{{-- Add family member modal --}}
@can('create', App\Models\Resident::class)
<div class="modal fade" id="addFamilyModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Family Member</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="{{ route('residents.family.store', $resident) }}">
            @csrf
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Relation</label>
                    <input type="text" name="relation" class="form-control" placeholder="spouse, child, parent…">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <input type="hidden" name="type" value="family_member">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Add Member</button>
            </div>
        </form>
    </div></div>
</div>
@endcan

{{-- Add emergency contact modal --}}
@can('update', $resident)
<div class="modal fade" id="addContactModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Emergency Contact</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="{{ route('residents.emergency-contacts.store', $resident) }}">
            @csrf
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Relation</label>
                    <input type="text" name="relation" class="form-control" placeholder="parent, sibling…">
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_primary" value="1" class="form-check-input" id="contactPrimary">
                    <label class="form-check-label" for="contactPrimary">Primary emergency contact</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Add Contact</button>
            </div>
        </form>
    </div></div>
</div>
@endcan
@endsection
