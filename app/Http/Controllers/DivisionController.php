<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Division;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    /**
     * Get all divisions with optional name filter.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Division::query();

        if ($request->has('name') && $request->name !== '') {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $divisions = $query->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Data divisi berhasil diambil',
            'data' => [
                'divisions' => $divisions->items(),
            ],
            'pagination' => [
                'current_page' => $divisions->currentPage(),
                'last_page' => $divisions->lastPage(),
                'per_page' => $divisions->perPage(),
                'total' => $divisions->total(),
                'from' => $divisions->firstItem(),
                'to' => $divisions->lastItem(),
                'next_page_url' => $divisions->nextPageUrl(),
                'prev_page_url' => $divisions->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Get a single division by ID.
     */
    public function show(string $id): JsonResponse
    {
        $division = Division::withCount('employees')->find($id);

        if (! $division) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data divisi tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data divisi berhasil diambil',
            'data' => [
                'division' => $division,
            ],
        ]);
    }

    /**
     * Store a new division.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:divisions,name',
        ]);

        $division = Division::create([
            'name' => $request->name,
        ]);

        ActivityLog::log(
            'created',
            'Division',
            $division->id,
            'Menambahkan divisi: ' . $division->name,
            null,
            $division->toArray(),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Data divisi berhasil ditambahkan',
            'data' => [
                'division' => $division,
            ],
        ], 201);
    }

    /**
     * Update an existing division.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $division = Division::find($id);

        if (! $division) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data divisi tidak ditemukan',
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:divisions,name,' . $id,
        ]);

        $oldValues = $division->toArray();

        $division->update([
            'name' => $request->name,
        ]);

        ActivityLog::log(
            'updated',
            'Division',
            $division->id,
            'Mengubah divisi: ' . $division->name,
            $oldValues,
            $division->fresh()->toArray(),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Data divisi berhasil diubah',
            'data' => [
                'division' => $division,
            ],
        ]);
    }

    /**
     * Delete a division.
     */
    public function destroy(string $id): JsonResponse
    {
        $division = Division::withCount('employees')->find($id);

        if (! $division) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data divisi tidak ditemukan',
            ], 404);
        }

        if ($division->employees_count > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Divisi tidak bisa dihapus karena masih memiliki ' . $division->employees_count . ' karyawan',
            ], 422);
        }

        ActivityLog::log(
            'deleted',
            'Division',
            $division->id,
            'Menghapus divisi: ' . $division->name,
            $division->toArray(),
        );

        $division->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data divisi berhasil dihapus',
        ]);
    }
}
