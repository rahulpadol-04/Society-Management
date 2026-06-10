<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceHead;
use App\Models\Role;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The society "master" configuration hub: general preferences, billing
 * configuration (defaults that feed the billing engine), billing components
 * (maintenance heads), and the dynamic Roles & Permissions matrix. All values
 * live in the tenant-scoped settings table (config defaults act as fallbacks).
 */
class SettingsController extends Controller
{
    /** The settings keys we manage, grouped by tab, with config fallbacks. */
    protected function generalDefaults(): array
    {
        return [
            'general.currency_symbol' => setting('general.currency_symbol', '₹'),
            'general.timezone'        => setting('general.timezone', current_society()?->timezone ?? 'Asia/Kolkata'),
            'general.date_format'     => setting('general.date_format', 'd M Y'),
            'general.week_start'      => setting('general.week_start', 'monday'),
            'general.support_email'   => setting('general.support_email', current_society()?->email),
            'general.support_phone'   => setting('general.support_phone', current_society()?->phone),
        ];
    }

    protected function billingDefaults(): array
    {
        $b = config('communityos.billing');

        return [
            'billing.type'                => setting('billing.type', 'fixed'),
            'billing.fixed_amount'        => setting('billing.fixed_amount', 1500),
            'billing.rate_per_sqft'       => setting('billing.rate_per_sqft', 2.5),
            'billing.cycle'               => setting('billing.cycle', 'monthly'),
            'billing.gst_percentage'      => setting('billing.gst_percentage', $b['gst_percentage']),
            'billing.late_fee_type'       => setting('billing.late_fee_type', 'percentage'),
            'billing.late_fee_percentage' => setting('billing.late_fee_percentage', $b['late_fee_percentage']),
            'billing.late_fee_flat'       => setting('billing.late_fee_flat', 100),
            'billing.late_fee_grace_days' => setting('billing.late_fee_grace_days', $b['late_fee_grace_days']),
            'billing.invoice_prefix'      => setting('billing.invoice_prefix', $b['invoice_prefix']),
            'billing.receipt_prefix'      => setting('billing.receipt_prefix', $b['receipt_prefix']),
        ];
    }

    public function index(): View
    {
        $this->authorize('viewAny', Setting::class);

        return view('settings.general', ['values' => $this->generalDefaults()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('update', Setting::class);

        $data = $request->validate([
            'general.currency_symbol' => ['required', 'string', 'max:5'],
            'general.timezone'        => ['required', 'string', 'max:64'],
            'general.date_format'     => ['required', 'string', 'max:20'],
            'general.week_start'      => ['required', 'in:monday,sunday'],
            'general.support_email'   => ['nullable', 'email', 'max:150'],
            'general.support_phone'   => ['nullable', 'string', 'max:20'],
        ]);

        foreach ($data['general'] as $key => $value) {
            Setting::put("general.{$key}", $value, 'string', 'general');
        }

        return back()->with('success', 'General settings saved.');
    }

    public function billing(): View
    {
        $this->authorize('viewAny', Setting::class);

        return view('settings.billing', [
            'values'     => $this->billingDefaults(),
            'components' => MaintenanceHead::orderBy('name')->get(),
            'overview'   => $this->societyOverview(),
        ]);
    }

    public function updateBilling(Request $request): RedirectResponse
    {
        $this->authorize('update', Setting::class);

        $data = $request->validate([
            'billing.type'                => ['required', 'in:fixed,flat_type,area,percentage,formula'],
            'billing.fixed_amount'        => ['nullable', 'numeric', 'min:0'],
            'billing.rate_per_sqft'       => ['nullable', 'numeric', 'min:0'],
            'billing.cycle'               => ['required', 'in:monthly,quarterly,half_yearly,yearly'],
            'billing.gst_percentage'      => ['required', 'numeric', 'min:0', 'max:100'],
            'billing.late_fee_type'       => ['required', 'in:none,percentage,flat'],
            'billing.late_fee_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'billing.late_fee_flat'       => ['nullable', 'numeric', 'min:0'],
            'billing.late_fee_grace_days' => ['required', 'integer', 'min:0', 'max:90'],
            'billing.invoice_prefix'      => ['required', 'string', 'max:10'],
            'billing.receipt_prefix'      => ['required', 'string', 'max:10'],
        ]);

        foreach ($data['billing'] as $key => $value) {
            Setting::put("billing.{$key}", $value ?? '', 'string', 'billing');
        }

        return back()->with('success', 'Billing configuration saved.');
    }

    public function roles(): View
    {
        $this->authorize('roles', Setting::class);

        $roles = Role::query()
            ->where(fn ($q) => $q->where('society_id', current_society_id())->orWhereNull('society_id'))
            ->withCount('permissions')
            ->withCount('users')
            ->orderByDesc('level')
            ->get();

        return view('settings.roles', ['roles' => $roles]);
    }

    public function editRole(Role $role): View
    {
        $this->authorize('permissions', Setting::class);

        abort_unless($role->society_id === current_society_id() || is_null($role->society_id), 404);

        $modules = collect(config('communityos.modules'))
            ->reject(fn ($def) => ($def['group'] ?? null) === 'Platform')
            ->groupBy('group', true);

        return view('settings.role-edit', [
            'role'    => $role,
            'modules' => $modules,
            'granted' => $role->permissions->pluck('slug')->all(),
        ]);
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('permissions', Setting::class);

        abort_unless($role->society_id === current_society_id(), 403, 'System roles cannot be edited here.');

        $slugs = (array) $request->input('permissions', []);
        $ids = \App\Models\Permission::whereIn('slug', $slugs)->pluck('id');

        $role->permissions()->sync($ids);

        // Bust cached permission sets for every user holding this role.
        $role->users()->get()->each->forgetCachedPermissions();

        return redirect()->route('settings.roles')->with('success', "Permissions updated for {$role->name}.");
    }

    protected function societyOverview(): array
    {
        $flats = \App\Models\Flat::query();

        return [
            'total_units' => (clone $flats)->count(),
            'avg_area'    => round((float) (clone $flats)->avg('carpet_area'), 0),
            'by_type'     => (clone $flats)->selectRaw('COALESCE(type, "Unspecified") as t, COUNT(*) as c')
                ->groupBy('t')->pluck('c', 't')->all(),
        ];
    }
}
