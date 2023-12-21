<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class UserProfilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all('id');
        $localFaker = fake('id');
        $faker = fake();
        $female = Gender::FEMALE;
        $male = Gender::MALE;

        $users->each(function (User $user, int $iteration) use ($localFaker, $faker, $male, $female) {
            $data = ['gender' => (string) rand($female->value, $male->value)];

            if ($iteration < 25) {
                $data['first_name'] = $localFaker->firstName(Gender::from($data['gender']));
                $data['last_name'] = $localFaker->lastName();
                $data['dob'] = sprintf('%s-%s-%s', rand(1990, 2000), now()->format('m'), now()->format('d'));
                $data['timezone'] = $localFaker->timezone('ID');
            } else {
                $data['first_name'] = $faker->firstName;
                $data['last_name'] = $faker->lastName;
                $data['dob'] = $faker->date('Y-m-d');
                $data['timezone'] = $faker->timezone;
            }

            UserProfile::factory()->belongsToUser($user)->create($data);
        });
    }
}
