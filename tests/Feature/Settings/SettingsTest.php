<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\Role;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPlatform();
    }

    public function test_admin_can_save_general_settings(): void
    {
        $society = $this->makeSociety();

        $this->actingAs($this->admin($society))->put('/settings', [
            'general' => [
                'currency_symbol' => '$',
                'timezone'        => 'UTC',
                'date_format'     => 'Y-m-d',
                'week_start'      => 'sunday',
            ],
        ])->assertRedirect();

        $this->assertEquals('$', Setting::get('general.currency_symbol'));
    }

    public function test_admin_can_save_billing_configuration(): void
    {
        $society = $this->makeSociety();

        $this->actingAs($this->admin($society))->put('/settings/billing', [
            'billing' => [
                'type'                => 'area',
                'cycle'               => 'quarterly',
                'gst_percentage'      => 12,
                'late_fee_type'       => 'flat',
                'late_fee_grace_days' => 7,
                'invoice_prefix'      => 'BILL',
                'receipt_prefix'      => 'PAY',
            ],
        ])->assertRedirect();

        $this->assertEquals('area', Setting::get('billing.type'));
        $this->assertEquals('BILL', Setting::get('billing.invoice_prefix'));
    }

    public function test_admin_can_reconfigure_a_role_permission_matrix(): void
    {
        $society = $this->makeSociety();
        $role = Role::where('society_id', $society->id)->where('slug', 'security-guard')->first();

        $this->actingAs($this->admin($society))->put("/settings/roles/{$role->id}", [
            'permissions' => ['visitors.view', 'visitors.checkin'],
        ])->assertRedirect(route('settings.roles'));

        $this->assertEqualsCanonicalizing(
            ['visitors.view', 'visitors.checkin'],
            $role->permissions()->pluck('slug')->all()
        );
    }

    public function test_resident_cannot_access_settings(): void
    {
        $society = $this->makeSociety();
        $resident = $this->makeUser($society, 'resident');

        $this->actingAs($resident)->get('/settings')->assertForbidden();
    }
}
