<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $departments = [
            [
                'name' => 'Human Resources',
                'description' => 'Manages employee relations, recruitment, and HR policies',
                'budget' => 10000000.00,
                'status' => 'active'
            ],
            [
                'name' => 'Information Technology',
                'description' => 'Manages IT infrastructure and software development',
                'budget' => 15000000.00,
                'status' => 'active'
            ],
            [
                'name' => 'Finance',
                'description' => 'Manages company finances and accounting',
                'budget' => 12000000.00,
                'status' => 'active'
            ],
            [
                'name' => 'Marketing',
                'description' => 'Manages marketing campaigns and brand strategy',
                'budget' => 8000000.00,
                'status' => 'active'
            ],
            [
                'name' => 'Operations',
                'description' => 'Manages day-to-day business operations',
                'budget' => 20000000.00,
                'status' => 'active'
            ]
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
} 