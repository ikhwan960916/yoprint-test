<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = User::find(1);
        if (!$user) {
            User::create([
                'id' => 1,
                'name' => 'User A',
                'email' => 'user-a@gmail.com',
                'password' => bcrypt('123'),
            ]);
        }
        
        $user = User::find(2);
        if (!$user) {
            User::create([
                'id' => 2,
                'name' => 'User B',
                'email' => 'user-b@gmail.com',
                'password' => bcrypt('123'),
            ]);
        }
       
    }
}
