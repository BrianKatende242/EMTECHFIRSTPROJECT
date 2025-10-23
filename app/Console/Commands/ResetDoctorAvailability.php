<?php

namespace App\Console\Commands;

use App\Models\Doctor;
use App\Models\DoctorAvailability;
use Illuminate\Console\Command;

class ResetDoctorAvailability extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'doctors:reset-availability {--week=next : Reset availability for "current" or "next" week}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset doctor availability for the upcoming week';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $week = $this->option('week');
        $this->info("Resetting doctor availability for the {$week} week...");

        // Clear existing availability records
        DoctorAvailability::truncate();

        // Get all doctors
        $doctors = Doctor::all();

        if ($doctors->isEmpty()) {
            $this->warn('No doctors found in the system.');
            return;
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $created = 0;

        foreach ($doctors as $doctor) {
            foreach ($days as $day) {
                DoctorAvailability::create([
                    'doctor_id' => $doctor->id,
                    'day' => $day,
                    'available' => true,
                    'max_appointments' => 10, // Default 10 appointments per day
                ]);
                $created++;
            }
        }

        $this->info("Successfully reset availability for {$doctors->count()} doctors.");
        $this->info("Created {$created} availability records for Monday-Friday.");
        $this->info('Doctors are now available for booking.');
    }
}
