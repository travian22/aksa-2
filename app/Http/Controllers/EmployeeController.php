<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * Get all employees with optional name and division filter.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Employee::with('division');

        if ($request->has('name') && $request->name !== '') {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('division_id') && $request->division_id !== '') {
            $query->where('division_id', $request->division_id);
        }

        $employees = $query->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Data karyawan berhasil diambil',
            'data' => [
                'employees' => $employees->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'image' => $employee->image,
                        'name' => $employee->name,
                        'phone' => $employee->phone,
                        'division' => [
                            'id' => $employee->division->id,
                            'name' => $employee->division->name,
                        ],
                        'position' => $employee->position,
                    ];
                }),
            ],
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
                'from' => $employees->firstItem(),
                'to' => $employees->lastItem(),
                'next_page_url' => $employees->nextPageUrl(),
                'prev_page_url' => $employees->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Get a single employee by ID.
     */
    public function show(string $id): JsonResponse
    {
        $employee = Employee::with('division')->find($id);

        if (! $employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data karyawan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data karyawan berhasil diambil',
            'data' => [
                'employee' => [
                    'id' => $employee->id,
                    'image' => $employee->image,
                    'name' => $employee->name,
                    'phone' => $employee->phone,
                    'division' => [
                        'id' => $employee->division->id,
                        'name' => $employee->division->name,
                    ],
                    'position' => $employee->position,
                    'created_at' => $employee->created_at,
                    'updated_at' => $employee->updated_at,
                ],
            ],
        ]);
    }

    /**
     * Store a new employee.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'division' => 'required|uuid|exists:divisions,id',
            'position' => 'required|string|max:255',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('employees', 'public');
        }

        $employee = Employee::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'division_id' => $request->division,
            'position' => $request->position,
            'image' => $imagePath ? Storage::url($imagePath) : null,
        ]);

        ActivityLog::log(
            'created',
            'Employee',
            $employee->id,
            'Menambahkan karyawan: ' . $employee->name,
            null,
            $employee->toArray(),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Data karyawan berhasil ditambahkan',
        ], 201);
    }

    /**
     * Update an existing employee.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $employee = Employee::find($id);

        if (! $employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data karyawan tidak ditemukan',
            ], 404);
        }

        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'division' => 'required|uuid|exists:divisions,id',
            'position' => 'required|string|max:255',
        ]);

        $data = [
            'name' => $request->name,
            'phone' => $request->phone,
            'division_id' => $request->division,
            'position' => $request->position,
        ];

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($employee->image) {
                $oldPath = str_replace('/storage/', '', $employee->image);
                Storage::disk('public')->delete($oldPath);
            }

            $imagePath = $request->file('image')->store('employees', 'public');
            $data['image'] = Storage::url($imagePath);
        }

        $oldValues = $employee->toArray();
        $employee->update($data);

        ActivityLog::log(
            'updated',
            'Employee',
            $employee->id,
            'Mengubah data karyawan: ' . $employee->name,
            $oldValues,
            $employee->fresh()->toArray(),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Data karyawan berhasil diubah',
        ]);
    }

    /**
     * Delete an employee.
     */
    public function destroy(string $id): JsonResponse
    {
        $employee = Employee::find($id);

        if (! $employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data karyawan tidak ditemukan',
            ], 404);
        }

        if ($employee->image) {
            $oldPath = str_replace('/storage/', '', $employee->image);
            Storage::disk('public')->delete($oldPath);
        }

        ActivityLog::log(
            'deleted',
            'Employee',
            $employee->id,
            'Menghapus karyawan: ' . $employee->name,
            $employee->toArray(),
        );

        $employee->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data karyawan berhasil dihapus',
        ]);
    }

    /**
     * Bulk delete employees.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|uuid|exists:employees,id',
        ]);

        $employees = Employee::whereIn('id', $request->ids)->get();
        $names = $employees->pluck('name')->implode(', ');

        // Delete images
        foreach ($employees as $employee) {
            if ($employee->image) {
                $oldPath = str_replace('/storage/', '', $employee->image);
                Storage::disk('public')->delete($oldPath);
            }
        }

        Employee::whereIn('id', $request->ids)->delete();

        ActivityLog::log(
            'deleted',
            'Employee',
            null,
            'Menghapus ' . count($request->ids) . ' karyawan: ' . $names,
            ['ids' => $request->ids, 'names' => $names],
        );

        return response()->json([
            'status' => 'success',
            'message' => count($request->ids) . ' karyawan berhasil dihapus',
        ]);
    }

    /**
     * Get employee summary/statistics by position.
     */
    public function summary(): JsonResponse
    {
        $byPosition = Employee::selectRaw('position, count(*) as total')
            ->groupBy('position')
            ->orderByDesc('total')
            ->get();

        $byDivision = Employee::with('division:id,name')
            ->selectRaw('division_id, count(*) as total')
            ->groupBy('division_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                return [
                    'division' => $item->division ? [
                        'id' => $item->division->id,
                        'name' => $item->division->name,
                    ] : null,
                    'total' => $item->total,
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Ringkasan karyawan berhasil diambil',
            'data' => [
                'total_employees' => Employee::count(),
                'by_position' => $byPosition,
                'by_division' => $byDivision,
            ],
        ]);
    }

    /**
     * Export employees to CSV.
     */
    public function export(Request $request)
    {
        $query = Employee::with('division');

        if ($request->has('name') && $request->name !== '') {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('division_id') && $request->division_id !== '') {
            $query->where('division_id', $request->division_id);
        }

        $employees = $query->get();

        $filename = 'employees_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($employees) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Phone', 'Position', 'Division', 'Created At']);

            foreach ($employees as $employee) {
                fputcsv($file, [
                    $employee->name,
                    $employee->phone,
                    $employee->position,
                    $employee->division->name,
                    $employee->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
