<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPlatform();
    }

    // -----------------------------------------------------------------------
    // reports.index
    // -----------------------------------------------------------------------

    public function test_reports_index_renders_for_admin(): void
    {
        $society = $this->makeSociety('Reports Test Society', 'reports-admin@test.com');
        $admin   = $this->admin($society);

        $this->actingAs($admin)
            ->get('/reports')
            ->assertOk()
            ->assertSee('Visitor Report')
            ->assertSee('Billing Report')
            ->assertSee('Financial Report');
    }

    // -----------------------------------------------------------------------
    // Individual report pages
    // -----------------------------------------------------------------------

    public function test_occupancy_report_renders(): void
    {
        $society = $this->makeSociety('Occupancy Society', 'occ@test.com');
        $admin   = $this->admin($society);

        $this->actingAs($admin)
            ->get('/reports/occupancy')
            ->assertOk()
            ->assertSee('Occupancy Report');
    }

    public function test_billing_report_renders(): void
    {
        $society = $this->makeSociety('Billing Society', 'billing@test.com');
        $admin   = $this->admin($society);

        $this->actingAs($admin)
            ->get('/reports/billing')
            ->assertOk()
            ->assertSee('Billing Report');
    }

    public function test_visitor_report_renders(): void
    {
        $society = $this->makeSociety('Visitor Society', 'visitor@test.com');
        $admin   = $this->admin($society);

        $this->actingAs($admin)
            ->get('/reports/visitor')
            ->assertOk()
            ->assertSee('Visitor Report');
    }

    public function test_complaint_report_renders(): void
    {
        $society = $this->makeSociety('Complaint Society', 'complaint@test.com');
        $admin   = $this->admin($society);

        $this->actingAs($admin)
            ->get('/reports/complaint')
            ->assertOk()
            ->assertSee('Complaint Report');
    }

    public function test_financial_report_renders(): void
    {
        $society = $this->makeSociety('Finance Society', 'finance@test.com');
        $admin   = $this->admin($society);

        $this->actingAs($admin)
            ->get('/reports/financial')
            ->assertOk()
            ->assertSee('Financial Report');
    }

    // -----------------------------------------------------------------------
    // CSV export
    // -----------------------------------------------------------------------

    public function test_csv_export_returns_200_with_text_csv_content_type(): void
    {
        $society = $this->makeSociety('Export Society', 'export@test.com');
        $admin   = $this->admin($society);

        $response = $this->actingAs($admin)
            ->get('/reports/billing/export');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type') ?? '');
    }

    public function test_visitor_csv_export_returns_text_csv(): void
    {
        $society = $this->makeSociety('VisitorExport Society', 'visexp@test.com');
        $admin   = $this->admin($society);

        $response = $this->actingAs($admin)
            ->get('/reports/visitor/export');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type') ?? '');
    }

    // -----------------------------------------------------------------------
    // Authorization
    // -----------------------------------------------------------------------

    public function test_resident_without_reports_view_gets_403(): void
    {
        $society  = $this->makeSociety('Auth Society', 'auth@test.com');
        $resident = $this->makeUser($society, 'resident');

        $this->actingAs($resident)
            ->get('/reports')
            ->assertForbidden();
    }

    public function test_resident_cannot_access_individual_report(): void
    {
        $society  = $this->makeSociety('Auth2 Society', 'auth2@test.com');
        $resident = $this->makeUser($society, 'resident');

        $this->actingAs($resident)
            ->get('/reports/billing')
            ->assertForbidden();
    }

    public function test_resident_cannot_export_csv(): void
    {
        $society  = $this->makeSociety('Auth3 Society', 'auth3@test.com');
        $resident = $this->makeUser($society, 'resident');

        $this->actingAs($resident)
            ->get('/reports/billing/export')
            ->assertForbidden();
    }

    public function test_accountant_can_view_reports(): void
    {
        $society    = $this->makeSociety('Accountant Society', 'acct@test.com');
        $accountant = $this->makeUser($society, 'accountant');

        $this->actingAs($accountant)
            ->get('/reports')
            ->assertOk();
    }

    // -----------------------------------------------------------------------
    // Invalid report type
    // -----------------------------------------------------------------------

    public function test_invalid_report_type_returns_404(): void
    {
        $society = $this->makeSociety('404 Society', 'notfound@test.com');
        $admin   = $this->admin($society);

        $this->actingAs($admin)
            ->get('/reports/nonexistent')
            ->assertNotFound();
    }

    // -----------------------------------------------------------------------
    // Print view
    // -----------------------------------------------------------------------

    public function test_print_view_renders_for_admin(): void
    {
        $society = $this->makeSociety('Print Society', 'print@test.com');
        $admin   = $this->admin($society);

        $this->actingAs($admin)
            ->get('/reports/occupancy/print?noprint=1')
            ->assertOk()
            ->assertSee('Occupancy Report');
    }
}
