@extends('layouts.app')
@section('title', 'Billing Configuration')
@section('page-title', 'Settings')

@section('content')
<div class="row g-3">
    <div class="col-lg-3">@include('settings._nav')</div>

    <div class="col-lg-9">
        <form method="POST" action="{{ route('settings.billing.update') }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-xl-8">
                    <div class="card mb-3"><div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h2 class="h5 mb-1">Billing Configuration</h2>
                                <p class="text-muted small mb-0">Configure how maintenance bills are calculated for your society.</p>
                            </div>
                            @can('update', App\Models\Setting::class)
                                <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Save</button>
                            @endcan
                        </div>
                    </div></div>

                    {{-- Billing Type --}}
                    <div class="card mb-3"><div class="card-body">
                        <h3 class="h6"><i class="bi bi-calculator me-1 text-primary"></i> Billing Type</h3>
                        <p class="text-muted small">How maintenance charges are calculated for each unit.</p>
                        <div class="option-grid" style="grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));">
                            @foreach (['fixed' => 'Fixed', 'flat_type' => 'Flat Type', 'area' => 'Area Based', 'percentage' => 'Percentage', 'formula' => 'Formula'] as $val => $label)
                                <div class="option-tile">
                                    <input type="radio" name="billing[type]" id="bt_{{ $val }}" value="{{ $val }}" @checked($values['billing.type'] === $val)>
                                    <label for="bt_{{ $val }}" class="text-center">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div></div>

                    {{-- Rate Configuration --}}
                    <div class="card mb-3"><div class="card-body">
                        <h3 class="h6"><i class="bi bi-currency-rupee me-1 text-primary"></i> Rate Configuration</h3>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Fixed Monthly Amount (₹)</label>
                                <input type="number" step="0.01" name="billing[fixed_amount]" value="{{ $values['billing.fixed_amount'] }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rate per sq.ft (₹) — for area-based</label>
                                <input type="number" step="0.01" name="billing[rate_per_sqft]" value="{{ $values['billing.rate_per_sqft'] }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GST / Tax (%)</label>
                                <input type="number" step="0.01" name="billing[gst_percentage]" value="{{ $values['billing.gst_percentage'] }}" class="form-control">
                            </div>
                        </div>
                    </div></div>

                    {{-- Billing Cycle --}}
                    <div class="card mb-3"><div class="card-body">
                        <h3 class="h6"><i class="bi bi-calendar3 me-1 text-primary"></i> Billing Cycle</h3>
                        <p class="text-muted small">How often maintenance bills are generated.</p>
                        <div class="option-grid" style="grid-template-columns: repeat(2, 1fr);">
                            @foreach (['monthly' => 'Monthly', 'quarterly' => 'Quarterly (Jan, Apr, Jul, Oct)', 'half_yearly' => 'Half-Yearly (Jan, Jul)', 'yearly' => 'Yearly (January)'] as $val => $label)
                                <div class="option-tile">
                                    <input type="radio" name="billing[cycle]" id="bc_{{ $val }}" value="{{ $val }}" @checked($values['billing.cycle'] === $val)>
                                    <label for="bc_{{ $val }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div></div>

                    {{-- Late Fee & Penalties --}}
                    <div class="card"><div class="card-body">
                        <h3 class="h6"><i class="bi bi-exclamation-triangle me-1 text-primary"></i> Late Fee &amp; Penalties</h3>
                        <p class="text-muted small">Configure penalties for overdue bills.</p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Calculation Type</label>
                                <select name="billing[late_fee_type]" class="form-select">
                                    <option value="none" @selected($values['billing.late_fee_type']==='none')>No Late Fee</option>
                                    <option value="percentage" @selected($values['billing.late_fee_type']==='percentage')>Percentage of dues</option>
                                    <option value="flat" @selected($values['billing.late_fee_type']==='flat')>Flat amount</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Late Fee (%)</label>
                                <input type="number" step="0.01" name="billing[late_fee_percentage]" value="{{ $values['billing.late_fee_percentage'] }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Flat Fee (₹)</label>
                                <input type="number" step="0.01" name="billing[late_fee_flat]" value="{{ $values['billing.late_fee_flat'] }}" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Grace (days)</label>
                                <input type="number" name="billing[late_fee_grace_days]" value="{{ $values['billing.late_fee_grace_days'] }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Invoice Prefix</label>
                                <input name="billing[invoice_prefix]" value="{{ $values['billing.invoice_prefix'] }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Receipt Prefix</label>
                                <input name="billing[receipt_prefix]" value="{{ $values['billing.receipt_prefix'] }}" class="form-control">
                            </div>
                        </div>
                    </div></div>
                </div>

                {{-- Right rail: Society Overview + Active Billing Components --}}
                <div class="col-xl-4">
                    <div class="card mb-3"><div class="card-body">
                        <h3 class="h6 mb-3">Society Overview</h3>
                        <div class="d-flex justify-content-between border-bottom py-1"><span class="text-muted">Total Units</span><strong>{{ $overview['total_units'] }}</strong></div>
                        <div class="d-flex justify-content-between border-bottom py-1"><span class="text-muted">Avg. Area</span><strong>{{ $overview['avg_area'] }} sq ft</strong></div>
                        <div class="small text-uppercase text-muted mt-3 mb-1">Units by Type</div>
                        @forelse ($overview['by_type'] as $type => $count)
                            <div class="d-flex justify-content-between py-1"><span>{{ $type }}</span><strong>{{ $count }}</strong></div>
                        @empty
                            <div class="text-muted small">No units defined.</div>
                        @endforelse
                    </div></div>

                    <div class="card"><div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3 class="h6 mb-0">Active Billing Components</h3>
                            @if (\Route::has('maintenance.heads.index'))
                                <a href="{{ route('maintenance.heads.index') }}" class="small">Manage →</a>
                            @endif
                        </div>
                        @forelse ($components->where('is_active', true) as $head)
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span>{{ $head->name }} @if($head->is_taxable)<span class="badge text-bg-light">+GST</span>@endif</span>
                                <strong>{{ money($head->amount) }}</strong>
                            </div>
                        @empty
                            <div class="text-muted small">No billing components yet. <a href="{{ \Route::has('maintenance.heads.index') ? route('maintenance.heads.index') : '#' }}">Add one</a>.</div>
                        @endforelse
                    </div></div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
