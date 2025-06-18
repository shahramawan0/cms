<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    public function run()
    {
        // Make sure Student role exists
        Role::firstOrCreate(['name' => 'Student']);
        
        $faker = Faker::create();
        
        // Get the institute ID
        $instituteId = auth()->user()->institute_id ?? 1; // Default to 1 if no user is authenticated
        
        // Create 70 students
        for ($i = 0; $i < 30; $i++) {
            $gender = $faker->randomElement(['Male', 'Female', 'Other']);
            $firstName = $gender == 'Male' ? $faker->firstNameMale : $faker->firstNameFemale;
            
            $user = User::create([
                'name' => $firstName . ' ' . $faker->lastName,
                'father_name' => $faker->name('male'),
                'cnic' => $this->generateFakeCnic(),
                'roll_number' => $faker->unique()->numberBetween(100, 9999), // 3-4 digit roll number
                'gender' => $gender,
                'dob' => $faker->dateTimeBetween('-20 years', '-15 years')->format('Y-m-d'),
                'admission_date' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                'email' => $faker->unique()->safeEmail,
                'phone' => '03' . $faker->numberBetween(10000000, 99999999),
                'address' => $faker->address,
                'password' => Hash::make('password'),
                'institute_id' => $instituteId,
                // Removed the is_active field
            ]);
            
            $user->assignRole('Student');
        }
    }
    
    protected function generateFakeCnic()
    {
        return mt_rand(10000, 99999) . '-' . 
               mt_rand(1000000, 9999999) . '-' . 
               mt_rand(0, 9);
    }
}