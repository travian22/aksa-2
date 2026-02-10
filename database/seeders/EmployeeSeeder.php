<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = Division::all();

        $employees = [
            ['name' => 'Budi Santoso', 'phone' => '081234567001', 'position' => 'Staff', 'image' => 'https://via.placeholder.com/150'],
            ['name' => 'Siti Nurhaliza', 'phone' => '081234567002', 'position' => 'Manager', 'image' => 'https://via.placeholder.com/150'],
            ['name' => 'Andi Wijaya', 'phone' => '081234567003', 'position' => 'Staff', 'image' => 'https://via.placeholder.com/150'],
            ['name' => 'Dewi Lestari', 'phone' => '081234567004', 'position' => 'Lead', 'image' => 'https://via.placeholder.com/150'],
            ['name' => 'Rudi Hartono', 'phone' => '081234567005', 'position' => 'Staff', 'image' => 'https://via.placeholder.com/150'],
            ['name' => 'Maya Sari', 'phone' => '081234567006', 'position' => 'Senior', 'image' => 'https://via.placeholder.com/150'],
            ['name' => 'Fajar Nugroho', 'phone' => '081234567007', 'position' => 'Junior', 'image' => 'https://via.placeholder.com/150'],
            ['name' => 'Rina Marlina', 'phone' => '081234567008', 'position' => 'Staff', 'image' => 'https://via.placeholder.com/150'],
            ['name' => 'Dimas Prasetyo', 'phone' => '081234567009', 'position' => 'Lead', 'image' => 'https://via.placeholder.com/150'],
            ['name' => 'Lina Permata', 'phone' => '081234567010', 'position' => 'Manager', 'image' => 'https://via.placeholder.com/150'],
        ];

        foreach ($employees as $index => $data) {
            Employee::create(array_merge($data, [
                'division_id' => $divisions[$index % $divisions->count()]->id,
            ]));
        }
    }
}
