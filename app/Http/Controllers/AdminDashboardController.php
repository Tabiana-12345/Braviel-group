<?php

namespace App\Http\Controllers;

use App\Models\AdminRecord;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = CarbonImmutable::today();
        $dailyTotals = collect(range(0, 29))
            ->mapWithKeys(fn (int $offset): array => [$today->subDays(29 - $offset)->toDateString() => 0.0]);
        $financialYearStart = $today->startOfYear();
        $monthlyTotals = collect(range(0, 11))
            ->mapWithKeys(fn (int $offset): array => [$financialYearStart->addMonths($offset)->format('Y-m') => 0.0]);

        $sales = AdminRecord::where('module', 'sales')->get();
        foreach ($sales as $sale) {
            $amount = (float) ($sale->data['total_amount'] ?? 0);
            $saleDate = $this->saleDate($sale);
            $dailyKey = $saleDate->toDateString();
            $monthlyKey = $saleDate->format('Y-m');

            if ($dailyTotals->has($dailyKey)) {
                $dailyTotals[$dailyKey] += $amount;
            }
            if ($monthlyTotals->has($monthlyKey)) {
                $monthlyTotals[$monthlyKey] += $amount;
            }
        }

        return view('dashboard', [
            'revenue' => $sales->sum(fn (AdminRecord $record) => (float) ($record->data['total_amount'] ?? 0)),
            'orderCount' => $sales->count(),
            'productCount' => AdminRecord::where('module', 'products')->count(),
            'customerCount' => AdminRecord::where('module', 'customers')->count(),
            'usersCount' => User::count(),
            'recentSales' => $sales->sortByDesc('created_at')->take(5)->values(),
            'salesLast30Days' => [
                'labels' => $dailyTotals->keys()->map(fn (string $date): string => CarbonImmutable::parse($date)->format('j M'))->all(),
                'values' => $dailyTotals->values()->all(),
            ],
            'salesCurrentFinancialYear' => [
                'labels' => $monthlyTotals->keys()->map(fn (string $month): string => CarbonImmutable::parse($month.'-01')->format('M'))->all(),
                'values' => $monthlyTotals->values()->all(),
            ],
            'moduleCounts' => AdminRecord::query()->selectRaw('module, count(*) as aggregate')->groupBy('module')->pluck('aggregate', 'module'),
        ]);
    }

    private function saleDate(AdminRecord $sale): CarbonImmutable
    {
        $date = $sale->data['sale_date'] ?? null;
        if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date) === 1) {
            [$year, $month, $day] = array_map('intval', explode('-', $date));
            if (checkdate($month, $day, $year)) {
                return CarbonImmutable::create($year, $month, $day)->startOfDay();
            }
        }

        return CarbonImmutable::parse($sale->created_at)->startOfDay();
    }
}
