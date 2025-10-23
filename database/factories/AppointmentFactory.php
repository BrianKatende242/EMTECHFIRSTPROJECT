<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Appointment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'appointment_time' => $this->faker->dateTimeBetween('now', '+1 month'),
            'reason' => $this->faker->sentence,
            'status' => $this->faker->randomElement(['scheduled', 'confirmed', 'completed', 'cancelled', 'awaiting_payment']),
            'payment_reference' => $this->faker->optional()->uuid,
            'school_id' => \App\Models\School::factory(),
            'patient_id' => \App\Models\Patient::factory(),
            'doctor_id' => 1, // Use a dummy ID for tests
            'duration_id' => 1, // Use a dummy ID for tests
        ];
    }
}