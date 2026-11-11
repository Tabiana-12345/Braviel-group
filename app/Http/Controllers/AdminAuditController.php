<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\AdminRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAuditController extends Controller
{
    public function index(): View
    {
        return view('admin.audit.index', [
            'logs' => AuditLog::with('user')->latest()->paginate(20),
        ]);
    }

    public function backup(Request $request): StreamedResponse
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'module' => 'audit-backup',
            'action' => 'backup-exported',
            'description' => 'Downloaded a workspace data export.',
        ]);

        $backup = [
            'generated_at' => now()->toIso8601String(),
            'records' => AdminRecord::query()->orderBy('id')->get(['id', 'module', 'title', 'data', 'created_by', 'created_at', 'updated_at']),
            'users' => User::query()->orderBy('id')->get(['id', 'name', 'email', 'role', 'created_at']),
        ];

        return response()->streamDownload(
            static fn () => print(json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)),
            'braviel-workspace-'.now()->format('Ymd-His').'.json',
            ['Content-Type' => 'application/json']
        );
    }
}
