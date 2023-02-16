<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::firstOrCreate(['name' => 'Student', 'registrable' => true]);
        Role::firstOrCreate(['name' => 'Teacher', 'registrable' => true]);
        Role::firstOrCreate(['name' => 'Administrator', 'registrable' => false]);
    }
}
