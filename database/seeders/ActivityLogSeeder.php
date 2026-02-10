<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        
        $activities = [
            ['action' => 'create', 'model_type' => 'Division', 'description' => 'Membuat divisi baru Mobile Apps'],
            ['action' => 'create', 'model_type' => 'Division', 'description' => 'Membuat divisi baru QA'],
            ['action' => 'create', 'model_type' => 'Employee', 'description' => 'Menambah karyawan baru Budi Santoso'],
            ['action' => 'create', 'model_type' => 'Employee', 'description' => 'Menambah karyawan baru Siti Nurhaliza'],
            ['action' => 'update', 'model_type' => 'Employee', 'description' => 'Update posisi Budi Santoso menjadi Senior'],
            ['action' => 'delete', 'model_type' => 'Employee', 'description' => 'Menghapus karyawan'],
            ['action' => 'login', 'model_type' => 'User', 'description' => 'User login ke sistem'],
            ['action' => 'logout', 'model_type' => 'User', 'description' => 'User logout dari sistem'],
            ['action' => 'create', 'model_type' => 'Attendance', 'description' => 'Membuat data kehadiran baru'],
            ['action' => 'update', 'model_type' => 'Attendance', 'description' => 'Update jam pulang karyawan'],
            ['action' => 'view', 'model_type' => 'Report', 'description' => 'Melihat laporan kehadiran'],
            ['action' => 'export', 'model_type' => 'Report', 'description' => 'Export laporan ke format Excel'],
            ['action' => 'create', 'model_type' => 'Division', 'description' => 'Membuat divisi baru Backend'],
            ['action' => 'update', 'model_type' => 'Division', 'description' => 'Update nama divisi Frontend'],
            ['action' => 'create', 'model_type' => 'Employee', 'description' => 'Menambah karyawan Andi Wijaya'],
            ['action' => 'view', 'model_type' => 'Employee', 'description' => 'Melihat detail karyawan'],
        ];

        foreach ($activities as $index => $data) {
            ActivityLog::create(array_merge($data, [
                'user_id' => $users->random()->id ?? null,
                'ip_address' => '192.168.' . random_int(0, 255) . '.' . random_int(1, 254),
                'model_id' => null,
                'old_values' => null,
                'new_values' => null,
            ]));
        }
    }
}
