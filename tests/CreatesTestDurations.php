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
            ['minutes' => 15, 'duration_type' => 'general'],
            [
                'price' => 50000,
                'is_active' => true,
            ]
        );

        Duration::firstOrCreate(
            ['minutes' => 30, 'duration_type' => 'specialist'],
            [
                'price' => 100000,
                'is_active' => true,
            ]
        );
    }

    protected function getGeneralDurationId()
    {
        $this->createTestDurations(); // Ensure durations exist
        return Duration::where('duration_type', 'general')->first()->id;
    }

    protected function getSpecialistDurationId()
    {
        $this->createTestDurations(); // Ensure durations exist
        return Duration::where('duration_type', 'specialist')->first()->id;
    }

    protected function createAppointment(array $data)
    {
        $this->createTestDurations(); // Ensure durations exist
        return Appointment::create($data);
    }
}