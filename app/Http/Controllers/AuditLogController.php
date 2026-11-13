<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        $logs = $query->paginate(20)->withQueryString();
        $modules = AuditLog::query()->select('module')->distinct()->orderBy('module')->pluck('module');

        return view('audit.index', compact('logs', 'modules'));
    }

    public function destroySelected(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:audit_log,id'],
        ]);

        $deleted = AuditLog::whereIn('id', $data['ids'])->delete();

        return back()->with('success', "{$deleted} riwayat berhasil dihapus.");
    }

    public function destroyAll()
    {
        $deleted = AuditLog::query()->delete();

        return redirect()->route('audit.index')->with('success', "{$deleted} riwayat berhasil dihapus.");
    }
}
