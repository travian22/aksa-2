<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function index(): JsonResponse
    {
        $totalEmployees = Employee::count();
        $totalDivisions = Division::count();

        $employeesPerDivision = Division::withCount('employees')
            ->get()
            ->map(function ($division) {
                return [
                    'id' => $division->id,
                    'name' => $division->name,
                    'total_employees' => $division->employees_count,
                ];
            });

        $recentEmployees = Employee::with('division')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'position' => $employee->position,
                    'division' => $employee->division->name,
                    'image' => $employee->image,
                    'created_at' => $employee->created_at,
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Data dashboard berhasil diambil',
            'data' => [
                'total_employees' => $totalEmployees,
                'total_divisions' => $totalDivisions,
                'employees_per_division' => $employeesPerDivision,
                'recent_employees' => $recentEmployees,
            ],
        ]);
    }
}
