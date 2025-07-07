<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert departments first
        $departments = [
            ['name' => 'Human Resources', 'description' => 'HR Department'],
            ['name' => 'Information Technology', 'description' => 'IT Department'],
            ['name' => 'Finance', 'description' => 'Finance Department'],
            ['name' => 'Marketing', 'description' => 'Marketing Department'],
            ['name' => 'Operations', 'description' => 'Operations Department'],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->insert($department);
        }

        // Insert employees
        $employees = [
            [
                'employee_id' => 'EMP001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@company.com',
                'phone' => '1234567890',
                'address' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'USA',
                'department_id' => 1,
                'position' => 'HR Manager',
                'base_salary' => 85000.00,
                'gender' => 'male',
                'status' => 'active',
                'hire_date' => '2022-01-15',
                'date_of_birth' => '1990-05-15',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'employee_id' => 'EMP002',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@company.com',
                'phone' => '2345678901',
                'address' => '456 Oak Ave',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'postal_code' => '90001',
                'country' => 'USA',
                'department_id' => 2,
                'position' => 'Senior Developer',
                'base_salary' => 95000.00,
                'gender' => 'female',
                'status' => 'active',
                'hire_date' => '2022-02-01',
                'date_of_birth' => '1992-08-20',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'employee_id' => 'EMP003',
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'email' => 'michael.j@company.com',
                'phone' => '3456789012',
                'address' => '789 Pine St',
                'city' => 'Chicago',
                'state' => 'IL',
                'postal_code' => '60601',
                'country' => 'USA',
                'department_id' => 3,
                'position' => 'Financial Analyst',
                'base_salary' => 75000.00,
                'gender' => 'male',
                'status' => 'active',
                'hire_date' => '2022-03-10',
                'date_of_birth' => '1988-11-30',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'employee_id' => 'EMP004',
                'first_name' => 'Sarah',
                'last_name' => 'Williams',
                'email' => 'sarah.w@company.com',
                'phone' => '4567890123',
                'address' => '321 Elm St',
                'city' => 'Houston',
                'state' => 'TX',
                'postal_code' => '77001',
                'country' => 'USA',
                'department_id' => 4,
                'position' => 'Marketing Specialist',
                'base_salary' => 65000.00,
                'gender' => 'female',
                'status' => 'active',
                'hire_date' => '2022-04-05',
                'date_of_birth' => '1993-02-25',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'employee_id' => 'EMP005',
                'first_name' => 'Robert',
                'last_name' => 'Brown',
                'email' => 'robert.b@company.com',
                'phone' => '5678901234',
                'address' => '654 Maple Ave',
                'city' => 'Phoenix',
                'state' => 'AZ',
                'postal_code' => '85001',
                'country' => 'USA',
                'department_id' => 5,
                'position' => 'Operations Manager',
                'base_salary' => 90000.00,
                'gender' => 'male',
                'status' => 'active',
                'hire_date' => '2022-05-20',
                'date_of_birth' => '1991-07-10',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($employees as $employee) {
            DB::table('employees')->insert($employee);
        }
    }
} 