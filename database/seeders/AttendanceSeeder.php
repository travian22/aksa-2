<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        $statuses = ['present', 'absent', 'late', 'sick', 'permit'];
        
        // Generate attendance data for the last 30 days
        $startDate = now()->subDays(30);
        
        foreach ($employees as $employee) {
            for ($i = 0; $i < 30; $i++) {
                $date = $startDate->copy()->addDays($i);
                
                // Skip weekends (Saturday & Sunday)
                if ($date->isWeekend()) {
                    continue;
                }
                
                $status = $statuses[array_rand($statuses)];
                $clockIn = null;
                $clockOut = null;
                
                if ($status === 'present' || $status === 'late') {
                    // Generate random clock in time (7:00 - 10:00)
                    $hour = random_int(7, 10);
                    $minute = random_int(0, 59);
                    $clockIn = sprintf('%02d:%02d:00', $hour, $minute);
                    
                    // Generate random clock out time (16:00 - 19:00)
                    $hour = random_int(16, 19);
                    $minute = random_int(0, 59);
                    $clockOut = sprintf('%02d:%02d:00', $hour, $minute);
                }
                
                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'status' => $status,
                    'notes' => $status === 'sick' ? 'Sakit perut' : ($status === 'permit' ? 'Ijin keluarga' : null),
                ]);
            }
        }
    }
}
