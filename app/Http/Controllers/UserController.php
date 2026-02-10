<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get all admin users.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->has('name') && $request->name !== '') {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Data admin berhasil diambil',
            'data' => [
                'users' => $users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'created_at' => $user->created_at,
                    ];
                }),
            ],
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
                'next_page_url' => $users->nextPageUrl(),
                'prev_page_url' => $users->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Delete an admin user.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data admin tidak ditemukan',
            ], 404);
        }

        // Prevent deleting yourself
        if ($user->id === $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak bisa menghapus akun sendiri',
            ], 422);
        }

        ActivityLog::log(
            'deleted',
            'User',
            $user->id,
            'Menghapus admin ' . $user->name,
            ['name' => $user->name, 'username' => $user->username, 'email' => $user->email],
        );

        // Revoke all tokens
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data admin berhasil dihapus',
        ]);
    }
}
