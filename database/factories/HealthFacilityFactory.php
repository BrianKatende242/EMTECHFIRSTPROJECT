<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\HealthFacility;
use Faker\Generator as Faker;

$factory->define(HealthFacility::class, function (Faker $faker) {
    return [
        'name' => $faker->company(),
        'email' => $faker->unique()->safeEmail,
        'contact_number' => $faker->phoneNumber(),
        'location' => $faker->address(),
        'type' => $faker->randomElement(['hospital', 'clinic', 'health_center']),
    ];
});