@extends('layouts.app')
@section('title', 'Escalation Matrix')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('helpdesk.index') }}">Helpdesk</a></li>
    <li class="breadcrumb-item active">Escalation Matrix</li>
@endsection

@section('page-actions')
    @can('update', new App\Models\SupportTicket)
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRuleModal">
            <i class="bi bi-plus-lg"></i> Add Rule
        </button>
    @endcan
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>Level</th>
                        <th>Name</th>
                        <th>Trigger After</th>
                        <th>Notify Role</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($rules as $rule)
                    <tr>
                        <td><span class="badge text-bg-secondary">L{{ $rule->level }}</span></td>
                        <td>{{ $rule->name }}</td>
                        <td>{{ $rule->after_hours }} hrs</td>
                        <td>{{ $rule->notify_role ? ucfirst(str_replace('-', ' ', $rule->notify_role)) : '—' }}</td>
                        <td>
                            @if ($rule->is_active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @can('update', new App\Models\SupportTicket)
                                <button class="btn btn-sm btn-outline-secondary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editRuleModal{{ $rule->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST"
                                      action="{{ route('helpdesk.escalation.destroy', $rule) }}"
                                      class="d-inline"
                                      data-confirm="Delete this escalation rule?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>

                    {{-- Edit Modal --}}
                    @can('update', new App\Models\SupportTicket)
                    <div class="modal fade" id="editRuleModal{{ $rule->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('helpdesk.escalation.update', $rule) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Escalation Rule</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Level</label>
                                            <input type="number" name="level" value="{{ $rule->level }}" class="form-control" min="1" max="10" required>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="name" value="{{ $rule->name }}" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">After Hours</label>
                                            <input type="number" name="after_hours" value="{{ $rule->after_hours }}" class="form-control" min="1" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Notify Role</label>
                                            <select name="notify_role" class="form-select">
                                                <option value="">— None —</option>
                                                <option value="society-admin" @selected($rule->notify_role === 'society-admin')>Society Admin</option>
                                                <option value="sub-admin" @selected($rule->notify_role === 'sub-admin')>Sub Admin</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active_{{ $rule->id }}" @checked($rule->is_active)>
                                                <label class="form-check-label" for="active_{{ $rule->id }}">Active</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endcan
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No escalation rules configured.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Rule Modal --}}
@can('update', new App\Models\SupportTicket)
<div class="modal fade" id="addRuleModal" tabindex="-1" aria-labelledby="addRuleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('helpdesk.escalation.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addRuleModalLabel">Add Escalation Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Level</label>
                        <input type="number" name="level" class="form-control" min="1" max="10" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. L1 – Auto-escalate" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">After Hours</label>
                        <input type="number" name="after_hours" class="form-control" min="1" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Notify Role</label>
                        <select name="notify_role" class="form-select">
                            <option value="">— None —</option>
                            <option value="society-admin">Society Admin</option>
                            <option value="sub-admin">Sub Admin</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="new_is_active" checked>
                            <label class="form-check-label" for="new_is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Add Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
