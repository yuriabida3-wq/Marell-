<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $action = $request->get('action');
        $userId = $request->get('user_id');

        $query = AuditLog::with('user')->latest();
        if ($action) $query->where('action', $action);
        if ($userId) $query->where('user_id', $userId);

        $logs = $query->paginate(40)->withQueryString();
        $actions = AuditLog::distinct()->pluck('action')->sort()->values();

        return view('principal.audit.index', compact('logs', 'actions', 'action', 'userId'));
    }
}
