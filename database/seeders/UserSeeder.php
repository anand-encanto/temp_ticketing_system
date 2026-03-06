<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Standard User',
            'username' => 'temp_user',
            'email' => 'temp_user@example.com',
            
            'password' => Hash::make('12345678'), // bcrypt password
            'role' => 'department_head', // if you have a role field

            'department_id' => 1, // optional
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
