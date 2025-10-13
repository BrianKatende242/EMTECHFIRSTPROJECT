<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\School;
use App\Models\HealthFacility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedPatientManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_find_or_create_patient_for_school()
    {
        $school = School::create([
            'name' => 'Test School',
            'email' => 'test@school.com',
            'contact' => '+256700000000',
        ]);

        // Create a patient for the school
        $patientData = [
            'name' => 'John Doe',
            'birth_date' => '2010-01-01',
            'gender' => 'male',
            'parent_contact' => '+256700000000',
        ];

        $patient = Patient::findOrCreate($patientData, [
            'school_id' => $school->id,
            'grade' => 'Grade 5',
        ]);

        $this->assertEquals('John Doe', $patient->name);
        $this->assertEquals($school->id, $patient->school_id);
        $this->assertEquals('Grade 5', $patient->grade);
        $this->assertNotNull($patient->patient_id);

        // Try to find or create the same patient again
        $samePatient = Patient::findOrCreate($patientData, [
            'school_id' => $school->id,
            'grade' => 'Grade 6', // Different grade
        ]);

        // Should return the same patient
        $this->assertEquals($patient->id, $samePatient->id);
        $this->assertEquals('Grade 5', $samePatient->grade); // Should keep original grade
    }

    /** @test */
    public function it_can_find_or_create_patient_for_health_facility()
    {
        $healthFacility = HealthFacility::create([
            'name' => 'Test Health Facility',
            'email' => 'test@facility.com',
            'contact_number' => '+256711111111',
            'contact' => '+256711111111',
            'location' => 'Test Location',
            'type' => 'hospital',
        ]);

        // Create a patient for the health facility
        $patientData = [
            'name' => 'Jane Smith',
            'birth_date' => '1985-05-15',
            'gender' => 'female',
            'contact_number' => '+256711111111',
        ];

        $patient = Patient::findOrCreate($patientData, [
            'health_facility_id' => $healthFacility->id,
            'medical_history' => 'No known allergies',
        ]);

        $this->assertEquals('Jane Smith', $patient->name);
        $this->assertEquals($healthFacility->id, $patient->health_facility_id);
        $this->assertEquals('No known allergies', $patient->medical_history);
        $this->assertNotNull($patient->patient_id);

        // Try to find or create the same patient again
        $samePatient = Patient::findOrCreate($patientData, [
            'health_facility_id' => $healthFacility->id,
            'medical_history' => 'Updated history',
        ]);

        // Should return the same patient
        $this->assertEquals($patient->id, $samePatient->id);
        $this->assertEquals('No known allergies', $samePatient->medical_history); // Should keep original
    }

    /** @test */
    public function it_generates_unique_patient_ids()
    {
        $school = School::create([
            'name' => 'Test School',
            'email' => 'test@school.com',
            'contact' => '+256700000000',
        ]);

        $patient1 = Patient::findOrCreate([
            'name' => 'Patient One',
            'birth_date' => '2000-01-01',
            'gender' => 'male',
        ], ['school_id' => $school->id]);

        $patient2 = Patient::findOrCreate([
            'name' => 'Patient Two',
            'birth_date' => '2000-01-01',
            'gender' => 'female',
        ], ['school_id' => $school->id]);

        $this->assertNotEquals($patient1->patient_id, $patient2->patient_id);
        $this->assertMatchesRegularExpression('/^P\d{6}$/', $patient1->patient_id);
        $this->assertMatchesRegularExpression('/^P\d{6}$/', $patient2->patient_id);
    }
}