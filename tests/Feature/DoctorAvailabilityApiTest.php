<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\DoctorAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorAvailabilityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_available_doctors_for_specific_day()
    {
        // Create doctors
        $doctor1 = Doctor::create([
            'name' => 'Dr. Available',
            'email' => 'available@example.com',
            'specialization' => 'General Practitioner',
            'contact' => '123456789'
        ]);

        $doctor2 = Doctor::create([
            'name' => 'Dr. Unavailable',
            'email' => 'unavailable@example.com',
            'specialization' => 'Cardiologist',
            'contact' => '987654321'
        ]);

        // Set availability
        DoctorAvailability::create([
            'doctor_id' => $doctor1->id,
            'day' => 'monday',
            'available' => true,
            'max_appointments' => 10
        ]);

        DoctorAvailability::create([
            'doctor_id' => $doctor2->id,
            'day' => 'monday',
            'available' => false,
            'max_appointments' => 5
        ]);

        // Test API endpoint
        $response = $this->getJson('/api/doctors/available?day=monday');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'doctors' => [
                    [
                        'id' => $doctor1->id,
                        'name' => 'Dr. Available',
                        'email' => 'available@example.com'
                    ]
                ]
            ]);

        // Ensure only available doctor is returned
        $this->assertCount(1, $response->json('doctors'));
        $this->assertEquals($doctor1->id, $response->json('doctors.0.id'));
    }

    public function test_get_available_doctors_returns_empty_for_unavailable_day()
    {
        // Create doctor but no availability for requested day
        Doctor::create([
            'name' => 'Dr. Test',
            'email' => 'test@example.com',
            'specialization' => 'General Practitioner',
            'contact' => '123456789'
        ]);

        $response = $this->getJson('/api/doctors/available?day=saturday');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'doctors' => []
            ]);
    }

    public function test_get_available_doctors_requires_day_parameter()
    {
        $response = $this->getJson('/api/doctors/available');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Day parameter is required'
            ]);
    }

    public function test_get_available_doctors_handles_case_insensitive_day_names()
    {
        $doctor = Doctor::create([
            'name' => 'Dr. Test',
            'email' => 'test@example.com',
            'specialization' => 'General Practitioner',
            'contact' => '123456789'
        ]);

        DoctorAvailability::create([
            'doctor_id' => $doctor->id,
            'day' => 'monday',
            'available' => true,
            'max_appointments' => 10
        ]);

        // Test with different cases
        $response1 = $this->getJson('/api/doctors/available?day=MONDAY');
        $response2 = $this->getJson('/api/doctors/available?day=Monday');
        $response3 = $this->getJson('/api/doctors/available?day=monday');

        foreach ([$response1, $response2, $response3] as $response) {
            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'doctors' => [
                        ['id' => $doctor->id]
                    ]
                ]);
        }
    }

    public function test_get_available_doctors_returns_multiple_doctors()
    {
        // Create multiple available doctors
        $doctor1 = Doctor::create([
            'name' => 'Dr. One',
            'email' => 'one@example.com',
            'specialization' => 'General Practitioner',
            'contact' => '111111111'
        ]);

        $doctor2 = Doctor::create([
            'name' => 'Dr. Two',
            'email' => 'two@example.com',
            'specialization' => 'Cardiologist',
            'contact' => '222222222'
        ]);

        // Both available on Tuesday
        DoctorAvailability::create(['doctor_id' => $doctor1->id, 'day' => 'tuesday', 'available' => true, 'max_appointments' => 10]);
        DoctorAvailability::create(['doctor_id' => $doctor2->id, 'day' => 'tuesday', 'available' => true, 'max_appointments' => 10]);

        $response = $this->getJson('/api/doctors/available?day=tuesday');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertCount(2, $response->json('doctors'));
        $doctorIds = collect($response->json('doctors'))->pluck('id')->sort()->values();
        $this->assertEquals([$doctor1->id, $doctor2->id], $doctorIds->toArray());
    }
}
