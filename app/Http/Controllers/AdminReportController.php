<?php

namespace App\Http\Controllers;

use App\Models\AdminRecord;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    public function index(): View
    {
        $totals = AdminRecord::where('module', 'sales')->get()->sum(fn (AdminRecord $record) => (float) ($record->data['total_amount'] ?? 0));
        $expenses = AdminRecord::where('module', 'expenses')->get()->sum(fn (AdminRecord $record) => (float) ($record->data['amount'] ?? 0));

        return view('admin.reports.index', [
            'modules' => config('admin_modules'),
            'counts' => AdminRecord::query()->selectRaw('module, count(*) as aggregate')->groupBy('module')->pluck('aggregate', 'module'),
            'revenue' => $totals,
            'expenses' => $expenses,
            'recentRecords' => AdminRecord::latest()->limit(10)->get(),
        ]);
    }
}
