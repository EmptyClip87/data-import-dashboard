<?php

use App\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@test.org',
            'password' => bcrypt('zivkovic'),
        ]);
        $user->assignRole('admin');
    }
}
