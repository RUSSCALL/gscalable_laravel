<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // firstOrCreate so this can run on every deploy without duplicating rows.
        foreach (['SuperAdmin', 'Admin', 'job_applicant'] as $role) {
            Role::firstOrCreate(['role_name' => $role]);
        }
    }
}
