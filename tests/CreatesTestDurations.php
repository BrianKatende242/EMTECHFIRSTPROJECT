<?php

namespace Tests;

use App\Models\Duration;
use App\Models\Appointment;

trait CreatesTestDurations
{
    protected function createTestDurations()
    {
        // Use firstOrCreate to ensure durations exist
        Duration::firstOrCreate(
            ['type' => 'general'],
            [
                'minutes' => 15,
                'general_price' => 50000,
                'specialist_price' => 75000,
                'is_active' => true,
            ]
        );

        Duration::firstOrCreate(
            ['type' => 'specialist'],
            [
                'minutes' => 30,
                'general_price' => 75000,
                'specialist_price' => 100000,
                'is_active' => true,
            ]
        );
    }

    protected function getGeneralDurationId()
    {
        $this->createTestDurations(); // Ensure durations exist
        return Duration::where('type', 'general')->first()->id;
    }

    protected function getSpecialistDurationId()
    {
        $this->createTestDurations(); // Ensure durations exist
        return Duration::where('type', 'specialist')->first()->id;
    }

    protected function createAppointment(array $data)
    {
        $this->createTestDurations(); // Ensure durations exist
        return Appointment::create($data);
    }
}