<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();
        if ($request->filled('module')) $query->where('module', $request->module);
        if ($request->filled('action')) {
            $a = addcslashes(mb_substr(trim((string) $request->action), 0, 100), '%_\\');
            $query->where('action', 'like', "%{$a}%");
        }
        if ($request->filled('search')) {
            $s = addcslashes(mb_substr(trim((string) $request->search), 0, 100), '%_\\');
            $query->where(fn ($w) => $w->where('description', 'like', "%{$s}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")));
        }
        if ($request->filled('date')) $query->whereDate('created_at', $request->date);
        $logs = $query->paginate(50)->withQueryString();
        return view('admin.audit-logs.index', compact('logs'));
    }
}
