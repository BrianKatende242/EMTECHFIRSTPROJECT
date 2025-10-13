<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\HealthFacility;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Get all patients for a specific health facility.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getPatientsByHealthFacility($id)
    {
        // Fetch patients based on health facility ID
        $patients = Patient::where('health_facility_id', $id)->get();

        // Return the data to the view or as JSON
        return view('Health-Facility-Instance', compact('patients'));
    }

    /**
     * Show the form for creating a new patient.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // You can pass health facilities to the create view for selection
        $healthFacilities = HealthFacility::all();
        return view('patients.create', compact('healthFacilities'));
    }


    public function maternalDocuments(Patient $patient)
    {
        $documents = $patient->maternalDocuments()->orderBy('created_at', 'desc')->get();
        $groupedDocuments = $documents->groupBy('document_type');
        
        return view('maternal-documents', [
            'patient' => $patient,
            'groupedDocuments' => $groupedDocuments,
            'healthFacility' => $patient->healthFacility // Get facility from patient instead
        ]);
    }

    /**
     * Store a newly created patient in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'health_facility_id' => 'required|exists:health_facilities,id',
            'name' => 'required|string|max:255',
            'gender' => 'required|string|in:male,female,other',
            'birth_date' => 'required|date',
            'contact_number' => 'nullable|string',
            'medical_history' => 'nullable|string'
        ]);

        // Use findOrCreate to check if patient exists or create new one
        $patient = Patient::findOrCreate([
            'name' => $validated['name'],
            'birth_date' => $validated['birth_date'],
            'gender' => $validated['gender'],
            'contact_number' => $validated['contact_number'],
        ], [
            'health_facility_id' => $validated['health_facility_id'],
            'medical_history' => $validated['medical_history'],
        ]);

        // Redirect back to the patients list for this health facility
        return redirect()->route('health-facility.patients', ['id' => $validated['health_facility_id']])
                         ->with('success', 'Patient added successfully!');
    }

    /**
     * Delete a patient, preventing deletion if there are existing appointments.
     */
    public function destroy(Patient $patient)
    {
        $facilityId = $patient->health_facility_id;

        // Cascade appointments: delete child appointments before patient
        $patient->appointments()->delete();

        $patient->delete();

        return redirect()
            ->route('health-facility.patients', ['id' => $facilityId])
            ->with('success', 'Patient and their appointments deleted successfully.');
    }
}
