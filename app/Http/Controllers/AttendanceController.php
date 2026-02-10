<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    /**
     * Get all attendances with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Attendance::with('employee:id,name,position,division_id', 'employee.division:id,name');

        // Filter by employee
        if ($request->has('employee_id') && $request->employee_id !== '') {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->has('date') && $request->date !== '') {
            $query->whereDate('date', $request->date);
        }

        // Filter by date range
        if ($request->has('from') && $request->from !== '') {
            $query->whereDate('date', '>=', $request->from);
        }

        if ($request->has('to') && $request->to !== '') {
            $query->whereDate('date', '<=', $request->to);
        }

        // Filter by division
        if ($request->has('division_id') && $request->division_id !== '') {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('division_id', $request->division_id);
            });
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'message' => 'Data absensi berhasil diambil',
            'data' => [
                'attendances' => $attendances->map(function ($attendance) {
                    return [
                        'id' => $attendance->id,
                        'employee' => [
                            'id' => $attendance->employee->id,
                            'name' => $attendance->employee->name,
                            'position' => $attendance->employee->position,
                            'division' => $attendance->employee->division ? [
                                'id' => $attendance->employee->division->id,
                                'name' => $attendance->employee->division->name,
                            ] : null,
                        ],
                        'date' => $attendance->date instanceof \DateTimeInterface ? $attendance->date->format('Y-m-d') : (string) $attendance->date,
                        'clock_in' => $attendance->clock_in,
                        'clock_out' => $attendance->clock_out,
                        'status' => $attendance->status,
                        'notes' => $attendance->notes,
                    ];
                }),
            ],
            'pagination' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
                'from' => $attendances->firstItem(),
                'to' => $attendances->lastItem(),
                'next_page_url' => $attendances->nextPageUrl(),
                'prev_page_url' => $attendances->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Get a single attendance record.
     */
    public function show(string $id): JsonResponse
    {
        $attendance = Attendance::with('employee:id,name,position,division_id', 'employee.division:id,name')->find($id);

        if (! $attendance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data absensi tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data absensi berhasil diambil',
            'data' => [
                'attendance' => [
                    'id' => $attendance->id,
                    'employee' => [
                        'id' => $attendance->employee->id,
                        'name' => $attendance->employee->name,
                        'position' => $attendance->employee->position,
                        'division' => $attendance->employee->division ? [
                            'id' => $attendance->employee->division->id,
                            'name' => $attendance->employee->division->name,
                        ] : null,
                    ],
                    'date' => $attendance->date instanceof \DateTimeInterface ? $attendance->date->format('Y-m-d') : (string) $attendance->date,
                    'clock_in' => $attendance->clock_in,
                    'clock_out' => $attendance->clock_out,
                    'status' => $attendance->status,
                    'notes' => $attendance->notes,
                    'created_at' => $attendance->created_at,
                    'updated_at' => $attendance->updated_at,
                ],
            ],
        ]);
    }

    /**
     * Store a new attendance record.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|uuid|exists:employees,id',
            'date' => 'required|date',
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'status' => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alpha'])],
            'notes' => 'nullable|string|max:500',
        ]);

        // Check for duplicate
        $exists = Attendance::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data absensi untuk karyawan ini pada tanggal tersebut sudah ada',
            ], 422);
        }

        $attendance = Attendance::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        $employee = Employee::find($request->employee_id);

        ActivityLog::log(
            'created',
            'Attendance',
            $attendance->id,
            'Menambahkan absensi ' . $employee->name . ' tanggal ' . $request->date,
            null,
            $attendance->toArray(),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Data absensi berhasil ditambahkan',
        ], 201);
    }

    /**
     * Update an attendance record.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $attendance = Attendance::find($id);

        if (! $attendance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data absensi tidak ditemukan',
            ], 404);
        }

        $request->validate([
            'employee_id' => 'required|uuid|exists:employees,id',
            'date' => 'required|date',
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'status' => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alpha'])],
            'notes' => 'nullable|string|max:500',
        ]);

        // Check for duplicate (exclude current record)
        $exists = Attendance::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data absensi untuk karyawan ini pada tanggal tersebut sudah ada',
            ], 422);
        }

        $oldValues = $attendance->toArray();

        $attendance->update([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        ActivityLog::log(
            'updated',
            'Attendance',
            $attendance->id,
            'Mengubah absensi karyawan tanggal ' . $request->date,
            $oldValues,
            $attendance->fresh()->toArray(),
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Data absensi berhasil diubah',
        ]);
    }

    /**
     * Delete an attendance record.
     */
    public function destroy(string $id): JsonResponse
    {
        $attendance = Attendance::with('employee:id,name')->find($id);

        if (! $attendance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data absensi tidak ditemukan',
            ], 404);
        }

        $dateStr = $attendance->date instanceof \DateTimeInterface ? $attendance->date->format('Y-m-d') : (string) $attendance->date;

        ActivityLog::log(
            'deleted',
            'Attendance',
            $attendance->id,
            'Menghapus absensi ' . $attendance->employee->name . ' tanggal ' . $dateStr,
            $attendance->toArray(),
        );

        $attendance->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data absensi berhasil dihapus',
        ]);
    }

    /**
     * Get attendance summary for a specific month.
     */
    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
            'division_id' => 'nullable|uuid|exists:divisions,id',
        ]);

        $query = Employee::with('division:id,name');

        if ($request->has('division_id') && $request->division_id !== '') {
            $query->where('division_id', $request->division_id);
        }

        $employees = $query->get()->map(function ($employee) use ($request) {
            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereMonth('date', $request->month)
                ->whereYear('date', $request->year)
                ->get();

            return [
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'position' => $employee->position,
                    'division' => $employee->division->name,
                ],
                'summary' => [
                    'hadir' => $attendances->where('status', 'hadir')->count(),
                    'izin' => $attendances->where('status', 'izin')->count(),
                    'sakit' => $attendances->where('status', 'sakit')->count(),
                    'alpha' => $attendances->where('status', 'alpha')->count(),
                    'total' => $attendances->count(),
                ],
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Ringkasan absensi berhasil diambil',
            'data' => [
                'month' => (int) $request->month,
                'year' => (int) $request->year,
                'employees' => $employees,
            ],
        ]);
    }
}
