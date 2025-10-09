<?php

namespace Database\Seeds;

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
            ['minutes' => 15, 'amount' => 5000.00],
            ['minutes' => 20, 'amount' => 7000.00],
            ['minutes' => 30, 'amount' => 10000.00],
            ['minutes' => 45, 'amount' => 15000.00],
            ['minutes' => 60, 'amount' => 20000.00],
        ];

        foreach ($durations as $duration) {
            \App\Models\Duration::create($duration);
        }
    }
}
