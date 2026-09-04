<?php

namespace App\Http\Controllers;

use App\Models\HrmActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HrmActionLogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->type !== 'super-admin') {
            return redirect()->back()->with('error', __('Permission denied. Logs are available only for Super Admin.'));
        }

        $modules = HrmActionLog::modules();

        $query = HrmActionLog::where('created_by', $user->creatorId())
            ->orderByDesc('created_at');

        if ($request->filled('module') && isset($modules[$request->module])) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('actor_name', 'like', '%' . $search . '%')
                    ->orWhere('employee_name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        $actions = HrmActionLog::where('created_by', $user->creatorId())
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('hrm_logs.index', compact('logs', 'modules', 'actions'));
    }
}
