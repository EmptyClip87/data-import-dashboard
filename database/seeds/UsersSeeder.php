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
        $admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@test.org',
            'password' => bcrypt('zivkovic'),
        ]);
        $admin->assignRole('admin');

        $member = User::create([
            'name'     => 'Member 1',
            'email'    => 'member1@test.org',
            'password' => bcrypt('11111111'),
        ]);
        $member->assignRole('member');
    }
}
