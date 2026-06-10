@extends('layouts.app')
@section('title', 'Message Templates')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('communication.index') }}">Communication</a></li>
    <li class="breadcrumb-item active">Templates</li>
@endsection

@section('page-actions')
    @can('create', App\Models\MessageTemplate::class)
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#templateModal">
            <i class="bi bi-plus-lg"></i> New Template
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
                        <th>Name</th>
                        <th>Channel</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($templates as $tpl)
                    <tr>
                        <td class="fw-semibold">{{ $tpl->name }}</td>
                        <td><span class="badge text-bg-light text-capitalize">{{ $tpl->channel }}</span></td>
                        <td>{{ $tpl->subject ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $tpl->is_active ? 'status-resolved' : 'status-closed' }} text-capitalize">
                                {{ $tpl->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $tpl->updated_at->format('d M Y') }}</td>
                        <td class="text-end">
                            @can('update', $tpl)
                                <button class="btn btn-sm btn-outline-secondary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#templateModal"
                                        data-id="{{ $tpl->id }}"
                                        data-name="{{ $tpl->name }}"
                                        data-channel="{{ $tpl->channel }}"
                                        data-subject="{{ $tpl->subject }}"
                                        data-body="{{ $tpl->body }}"
                                        data-active="{{ $tpl->is_active ? '1' : '0' }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            @endcan
                            @can('delete', $tpl)
                                <form method="POST"
                                      action="{{ route('communication.templates.destroy', $tpl) }}"
                                      class="d-inline"
                                      data-confirm="Delete this template?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No templates yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Create / Edit Modal --}}
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="templateForm" method="POST">
                @csrf
                <div id="methodField"></div>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">New Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="tplName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Channel <span class="text-danger">*</span></label>
                            <select name="channel" id="tplChannel" class="form-select" required>
                                @foreach (['email', 'sms', 'whatsapp', 'push', 'in_app'] as $ch)
                                    <option value="{{ $ch }}">{{ ucfirst(str_replace('_', ' ', $ch)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Subject</label>
                            <input type="text" name="subject" id="tplSubject" class="form-control" placeholder="(email only)">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Body <span class="text-danger">*</span></label>
                            <textarea name="body" id="tplBody" rows="5" class="form-control" required></textarea>
                            <div class="form-text">Use <code>@{{name}}</code>, <code>@{{amount}}</code> etc. for variables.</div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="tplActive" class="form-check-input" value="1" checked>
                                <label class="form-check-label" for="tplActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var createUrl = '{{ route('communication.templates.store') }}';

    $('#templateModal').on('show.bs.modal', function (event) {
        var btn = $(event.relatedTarget);
        var id  = btn.data('id');

        if (id) {
            // Edit mode
            $('#modalTitle').text('Edit Template');
            var url = '{{ url('communication/templates') }}/' + id;
            $('#templateForm').attr('action', url);
            $('#methodField').html('<input type="hidden" name="_method" value="PUT">');
            $('#tplName').val(btn.data('name'));
            $('#tplChannel').val(btn.data('channel'));
            $('#tplSubject').val(btn.data('subject') || '');
            $('#tplBody').val(btn.data('body'));
            $('#tplActive').prop('checked', btn.data('active') == 1);
        } else {
            // Create mode
            $('#modalTitle').text('New Template');
            $('#templateForm').attr('action', createUrl);
            $('#methodField').html('');
            $('#tplName').val('');
            $('#tplChannel').val('email');
            $('#tplSubject').val('');
            $('#tplBody').val('');
            $('#tplActive').prop('checked', true);
        }
    });
});
</script>
@endpush
