<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $durations = [
            [
                'minutes' => 15,
                'price' => 30000.00,
                'duration_type' => 'general',
            ],
            [
                'minutes' => 15,
                'price' => 100000.00,
                'duration_type' => 'specialist',
            ],
            [
                'minutes' => 20,
                'price' => 45000.00,
                'duration_type' => 'general',
            ],
            [
                'minutes' => 20,
                'price' => 150000.00,
                'duration_type' => 'specialist',
            ],
            [
                'minutes' => 30,
                'price' => 75000.00,
                'duration_type' => 'general',
            ],
            [
                'minutes' => 30,
                'price' => 250000.00,
                'duration_type' => 'specialist',
            ],
            [
                'minutes' => 45,
                'price' => 100000.00,
                'duration_type' => 'general',
            ],
            [
                'minutes' => 45,
                'price' => 350000.00,
                'duration_type' => 'specialist',
            ],
            [
                'minutes' => 60,
                'price' => 125000.00,
                'duration_type' => 'general',
            ],
            [
                'minutes' => 60,
                'price' => 450000.00,
                'duration_type' => 'specialist',
            ],
        ];

        foreach ($durations as $duration) {
            \App\Models\Duration::updateOrCreate(
                [
                    'minutes' => $duration['minutes'],
                    'duration_type' => $duration['duration_type']
                ],
                $duration
            );
        }
    }
}
