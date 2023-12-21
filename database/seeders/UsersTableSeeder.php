<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = fake('id');

        User::factory()
            ->count(100)
            ->sequence(fn (Sequence $sequence) => [
                'name'  => $faker->userName,
                'email' => $faker->email,
            ])
            ->create([
                'password' => bcrypt('trx100%'),
            ]);
    }
}
