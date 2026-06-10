@extends('layouts.app')
@section('title', 'Compose Broadcast')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('communication.index') }}">Communication</a></li>
    <li class="breadcrumb-item active">Compose Broadcast</li>
@endsection

@section('content')
<div class="row"><div class="col-lg-9">
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('communication.broadcasts.store') }}">
            @csrf

            {{-- Title --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                       class="form-control @error('title') is-invalid @enderror" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Optional template selector --}}
            @if ($templates->isNotEmpty())
            <div class="mb-3">
                <label class="form-label fw-semibold">Load from template <span class="text-muted fw-normal">(optional)</span></label>
                <select id="templateSelect" class="form-select">
                    <option value="">— Select a template —</option>
                    @foreach ($templates as $tpl)
                        <option value="{{ $tpl->id }}"
                                data-body="{{ $tpl->body }}"
                                data-subject="{{ $tpl->subject }}">
                            {{ $tpl->name }} ({{ $tpl->channel }})
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Message body --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                <textarea name="message" id="messageBody" rows="6"
                          class="form-control @error('message') is-invalid @enderror"
                          required>{{ old('message') }}</textarea>
                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Channels --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Channels <span class="text-danger">*</span></label>
                <div class="d-flex flex-wrap gap-3">
                    @foreach (['email' => 'envelope', 'sms' => 'phone', 'whatsapp' => 'whatsapp', 'push' => 'bell', 'in_app' => 'app'] as $ch => $icon)
                        <div class="form-check">
                            <input type="checkbox" name="channels[]" value="{{ $ch }}"
                                   id="ch_{{ $ch }}" class="form-check-input"
                                   @checked(in_array($ch, old('channels', ['email'])))>
                            <label class="form-check-label" for="ch_{{ $ch }}">
                                <i class="bi bi-{{ $icon }} me-1"></i>{{ ucfirst(str_replace('_', ' ', $ch)) }}
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('channels')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            {{-- Audience --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Audience <span class="text-danger">*</span></label>
                <select name="audience" class="form-select @error('audience') is-invalid @enderror" required>
                    @foreach (['all' => 'Everyone', 'owners' => 'Owners (Residents)', 'tenants' => 'Tenants', 'staff' => 'Staff', 'residents' => 'Residents & Tenants'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('audience', 'all') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('audience')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Schedule (optional) --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Schedule for later <span class="text-muted fw-normal">(optional)</span></label>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                       class="form-control @error('scheduled_at') is-invalid @enderror">
                @error('scheduled_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <hr>
            <div class="d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-send me-1"></i>Save as Draft</button>
                <a href="{{ route('communication.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div></div>
</div></div>
@endsection

@push('scripts')
<script>
$(function () {
    // Populate message body from template body field.
    $('#templateSelect').on('change', function () {
        var opt = $(this).find(':selected');
        var body = opt.data('body');
        if (body) {
            $('#messageBody').val(body);
        }
    });
});
</script>
@endpush
