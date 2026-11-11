<?php

namespace Tests\Feature;

use App\Models\AdminRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_management_modules_without_sample_business_data(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-sidebar-toggle')
            ->assertSee('aria-controls="admin-sidebar"', false)
            ->assertSee('menu-toggle-lines')
            ->assertSee('Product Management')
            ->assertSee('admin-menu-icon')
            ->assertDontSee("'icon' => 'U'")
            ->assertSee('Category Management')
            ->assertSee('Sales and Point of Sale (POS)')
            ->assertSee('Inventory Management')
            ->assertSee('Purchase Management')
            ->assertSee('Customer Management')
            ->assertSee('Supplier Management')
            ->assertSee('Expense Management')
            ->assertSee('Payment Management')
            ->assertSee('Returns and Refunds')
            ->assertSee('Cash Register')
            ->assertSee('Reports and Analytics')
            ->assertSee('User and Role Management')
            ->assertSee('User Management')
            ->assertSee('Contacts')
            ->assertSee('List Product')
            ->assertSee('Add Product')
            ->assertSee('Import Product')
            ->assertSee('Units')
            ->assertSee('Import Opening Stock')
            ->assertSee('Update Price')
            ->assertSee('Brands')
            ->assertSee('Inventory Management')
            ->assertSee('List Purchases')
            ->assertSee('List Purchase Return')
            ->assertSee('All sales')
            ->assertSee('List POS')
            ->assertSee('System Settings')
            ->assertSee('Audit Logs and Backup')
            ->assertSee('No sales data yet')
            ->assertSee('No inventory data yet')
            ->assertSee('Sales Last 30 Days')
            ->assertSee('Sales Current Financial Year')
            ->assertSee('No sales data for this period yet.')
            ->assertDontSee('$45,231.89')
            ->assertDontSee('Wireless Headphones');
    }

    public function test_dashboard_sales_charts_aggregate_real_sales_by_day_and_calendar_financial_year(): void
    {
        Carbon::setTestNow('2026-11-11 12:00:00');
        try {
            $this->actingAs(User::factory()->create(['role' => 'admin']));

            AdminRecord::create([
                'module' => 'sales',
                'title' => 'Recent sale',
                'data' => ['sale_date' => '2026-11-10', 'total_amount' => '1250'],
            ]);
            AdminRecord::create([
                'module' => 'sales',
                'title' => 'Financial year sale',
                'data' => ['sale_date' => '2026-02-01', 'total_amount' => '5000'],
            ]);
            AdminRecord::create([
                'module' => 'sales',
                'title' => 'Previous year sale',
                'data' => ['sale_date' => '2025-12-31', 'total_amount' => '9000'],
            ]);
            AdminRecord::create([
                'module' => 'sales',
                'title' => 'Sale without date',
                'data' => ['total_amount' => '75'],
            ]);

            $this->get(route('dashboard'))
                ->assertOk()
                ->assertSee('Sales Last 30 Days')
                ->assertSee('Sales Current Financial Year')
                ->assertSee('class="sales-chart-svg"', false)
                ->assertSee('Sales Last 30 Days line chart showing sales in KSh')
                ->assertViewHas('salesLast30Days', function (array $chart): bool {
                    $this->assertSame(30, count($chart['labels']));
                    $this->assertSame(1250.0, $chart['values'][28]);
                    $this->assertSame(75.0, $chart['values'][29]);

                    return true;
                })
                ->assertViewHas('salesCurrentFinancialYear', function (array $chart): bool {
                    $this->assertSame(12, count($chart['labels']));
                    $this->assertSame(5000.0, $chart['values'][1]);
                    $this->assertSame(1325.0, $chart['values'][10]);
                    $this->assertSame(0.0, $chart['values'][11]);

                    return true;
                });
        } finally {
            Carbon::setTestNow();
        }
    }
}
