<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Get all activity logs with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user:id,name,username')
            ->orderBy('created_at', 'desc');

        // Filter by action type
        if ($request->has('action') && $request->action !== '') {
            $query->where('action', $request->action);
        }

        // Filter by model type
        if ($request->has('model_type') && $request->model_type !== '') {
            $query->where('model_type', $request->model_type);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id !== '') {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('from') && $request->from !== '') {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->has('to') && $request->to !== '') {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->paginate(15);

        return response()->json([
            'status' => 'success',
            'message' => 'Data activity log berhasil diambil',
            'data' => [
                'activity_logs' => $logs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'user' => $log->user ? [
                            'id' => $log->user->id,
                            'name' => $log->user->name,
                            'username' => $log->user->username,
                        ] : null,
                        'action' => $log->action,
                        'model_type' => $log->model_type,
                        'model_id' => $log->model_id,
                        'description' => $log->description,
                        'old_values' => $log->old_values,
                        'new_values' => $log->new_values,
                        'ip_address' => $log->ip_address,
                        'created_at' => $log->created_at,
                    ];
                }),
            ],
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'from' => $logs->firstItem(),
                'to' => $logs->lastItem(),
                'next_page_url' => $logs->nextPageUrl(),
                'prev_page_url' => $logs->previousPageUrl(),
            ],
        ]);
    }
}
