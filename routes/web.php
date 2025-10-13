<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PatientController; 
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\FinanceDashboardController;
use App\Http\Controllers\HealthFacilityController;
use App\Http\Controllers\ApiDashboardController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\OtpController;
use Illuminate\Http\Request; 
use App\Models\Doctor;
use App\Http\Controllers\PaymentController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider within a group
| which contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('https://ketiai.com');
});

// Home Route (Fixed Controller Reference)
Route::get('/home', [HomeController::class, 'index'])->name('home');

// API Dashboard Route
Route::get('/api-dashboard', [ApiDashboardController::class, 'index'])->name('api-dashboard');

// Finance Dashboard Route
Route::get('/finance-dashboard', [FinanceDashboardController::class, 'index'])->name('finance-dashboard');


// If you need a web view for admin purposes
//Route::get('/admin/contact-submissions', function () {
  //  return view('contact-submissions');
//});


Route::get('/admin/contact-submissions', [ContactController::class, 'index'])
     ->name('admin.contact-submissions');


// Newsletter verification (web - HTML)
Route::get('/verify-newsletter/{token}', [NewsletterController::class, 'verify'])->name('newsletter.verify.web');

Route::post('/send-otp', [App\Http\Controllers\OtpController::class, 'sendOtp']);

// routes/web.php
// Route::get('/school-dashboard/{school}', function (App\Models\School $school) {
//     return view('school-dashboard', [
//         'school' => $school,
//         'students' => $school->students()->with(['appointments', 'labTests'])->latest()->get(),
//         'appointments' => $school->appointments()->with(['student', 'doctor'])->latest()->get(),
//         'labTests' => $school->labTests()->with('student')->latest()->get(),
//         'doctors' => $school->doctors()->latest()->get()
//     ]);
// })->name('school.dashboard');




Route::get('/school-dashboard/{school}', function (App\Models\School $school) {
    return view('school-dashboard', [
        'school' => $school,
        'studentsCount' => $school->students()->count(),
        'appointmentsCount' => $school->appointments()->count(),
        'labTestsCount' => $school->labTests()->count(),
        'doctorsCount' => $school->doctors()->count(),
        'students' => $school->students()->latest()->get(),
        'appointments' => $school->appointments()->with(['patient', 'doctor', 'duration'])->latest()->get(),
        'labTests' => $school->labTests()->with('patient')->latest()->get(),
        'doctors' => Doctor::latest()->get()
    ]);
})->name('school.dashboard');


Route::get('/students/{school}', function (App\Models\School $school) {
    return view('students', [
        'school' => $school,
        'students' => $school->students()->latest()->get()
    ]);
})->name('students');


Route::delete('/students/{student}/delete', function ($studentId) {
    $student = App\Models\Patient::findOrFail($studentId);
    $schoolId = $student->school_id;
    if ($student->appointments()->count() > 0) {
        return redirect()->route('students', ['school' => $schoolId])
            ->with('error', 'Cannot delete student with existing appointments.');
    }
    $student->delete();
    return redirect()->route('students', ['school' => $schoolId])->with('success', 'Student deleted successfully.');
})->name('students.delete');

Route::post('/students/create', function (Request $request) {
    try {
        $validated = $request->validate([
            'patient_type' => 'required|in:new,existing',
            'patient_type' => 'required|in:new,existing',
            'school_id' => 'required',
        ]);

        if ($validated['patient_type'] === 'existing') {
            // Handle existing patient
            $existingValidation = $request->validate([
                'patient_id' => 'required|string|exists:patients,patient_id',
            ]);

            $patient = App\Models\Patient::where('patient_id', $existingValidation['patient_id'])->first();

            // Check if patient is already associated with this school
            if ($patient->school_id == $validated['school_id']) {
                return redirect()->route('students', ['school' => $validated['school_id']])
                    ->with('error', 'Patient is already associated with this school.');
            }

            // Update patient with school association
            $patient->update([
                'school_id' => $validated['school_id'],
                'grade' => $request->input('grade'), // Optional grade for existing patients
            ]);

            return redirect()->route('students', ['school' => $validated['school_id']])
                ->with('success', 'Existing patient associated with school successfully.');
        } else {
            // Handle new patient
            $newValidation = $request->validate([
                'name' => 'required',
                'gender' => 'required|in:male,female,other',
                'grade' => 'required',
                'parent_contact' => 'required',
                'birth_date' => 'required|date',
            ]);

            // Use findOrCreate to check if student exists or create new one
            $patient = App\Models\Patient::findOrCreate([
                'name' => $newValidation['name'],
                'birth_date' => $newValidation['birth_date'],
                'gender' => $newValidation['gender'],
                'parent_contact' => $newValidation['parent_contact'],
            ], [
                'school_id' => $validated['school_id'],
                'grade' => $newValidation['grade'],
            ]);

            return redirect()->route('students', ['school' => $patient->school_id])
                ->with('success', 'Student created successfully.');
        }
    } catch (\Illuminate\Validation\ValidationException $e) {
        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput();
    }
})->name('students.create');


Route::get('/lab-tests/{school}', function (App\Models\School $school) {
    return view('lab-tests', [
        'school' => $school,
        'labTests' => $school->labTests()->with('patient')->latest()->get(),
        'students' => $school->students()->latest()->get()
    ]);
})->name('lab-tests');


Route::get('/book-doctor/{school}/', function (App\Models\School $school) {
    return view('book-doctor', [
        'school' => $school,
        'appointments' => $school->appointments()->with(['student', 'doctor', 'duration'])->latest()->get(),
        'patients' => $school->students()->latest()->get(),
        'doctors' => Doctor::latest()->get()
    ]);
})->name('book-doctor');


Route::get('/doctor/{doctorId}/appointments', [DoctorController::class, 'getDoctorAppointments'])->name('doctor.appointments');
// Appointment actions
Route::patch('/appointments/{appointment}/cancel', [\App\Http\Controllers\AppointmentController::class, 'cancel'])->name('appointments.cancel');
Route::patch('/appointments/{appointment}/complete', [\App\Http\Controllers\AppointmentController::class, 'complete'])->name('appointments.complete');

Route::get('doctor/{doctorId}/meeting-link/', function ($doctorId) {
    $doctor = Doctor::findOrFail($doctorId);
    return view('meeting-link', [
        'appointments' => $doctor->appointments()->with(['student', 'school', 'healthFacility', 'duration'])->latest()->get(),
        'doctor' => $doctor
    ]);
})->name('doctor.meeting-link');

// Web route to update a doctor's meeting link from the web form (keeps session & CSRF)
Route::post('/doctor/{id}/update-meeting-link', [DoctorController::class, 'updateMeetingLink'])
    ->name('doctor.update-meeting-link');

// Web route to send meeting link to an email (school/health facility)
Route::post('/doctor/{doctor}/send-link', [\App\Http\Controllers\DoctorController::class, 'sendLink'])
    ->name('doctor.send-link');

// Web route to update doctor availability (form submissions)
Route::post('/doctor/{doctor}/availability', [\App\Http\Controllers\DoctorAvailabilityController::class, 'update'])
    ->name('doctor.update-availability');

Route::get('/doctor-dashboard/{doctorId}/availability', [DoctorController::class, 'availability'])->name('doctor.availability');


// Doctor Dashboard Route
Route::get('/doctors-dashboard', [DoctorController::class, 'dashboard']);


Route::middleware(['auth:doctor'])->group(function () {
    Route::get('/doctor/dashboard', [DoctorController::class, 'authDashboard'])->name('doctor.dashboard');
    // Add routes for other methods if not already defined
});


Route::get('/doctor-dashboard', function () {
    return view('doctor-dashboard'); // points to resources/views/doctor-dashboard.blade.php
});


Route::get('/doctor-dashboard/{doctorId}', [DoctorController::class, 'showDoctorDashboard'])->name('doctor.dashboard');


// In your web.php routes file, add this route

Route::get('/health-facilities-dashboard', function () {
    try {
        // Fetch data from the API endpoint
        $response = Http::get('https://laravelbackendchil.onrender.com/api/health-facilities');
        
        // Check if the request was successful
        if ($response->successful()) {
            // Get the data from the response
            $data = $response->json();
            
            // Check if we have health facilities data
            $healthFacilities = $data['data'] ?? [];
            
            // If data is not in expected format, check if it's an array at root level
            if (empty($healthFacilities) && is_array($data)) {
                $healthFacilities = $data;
            }
        } else {
            // Request failed, set empty array
            $healthFacilities = [];
            $error = 'Failed to fetch data from API: ' . ($response->json()['message'] ?? 'Unknown error');
        }
    } catch (\Exception $e) {
        // Handle exceptions (network errors, etc.)
        $healthFacilities = [];
        $error = 'Exception occurred: ' . $e->getMessage();
    }
    
    // Pass the data to the view
    return view('health_facilities', [
        'healthFacilities' => $healthFacilities ?? [],
        'error' => $error ?? null
    ]);
});


Route::get('/health-facility/dashboard/{id}', [HealthFacilityController::class, 'showDashboard'])->name('health-facility.dashboard');

// Health Facility section routes
Route::get('/health-facility/{id}/patients', [HealthFacilityController::class, 'patients'])->name('health-facility.patients');
Route::get('/health-facility/{id}/patients/create', [HealthFacilityController::class, 'createPatient'])->name('health-facility.patients.create');
Route::get('/health-facility/{id}/book-doctor', [HealthFacilityController::class, 'bookDoctor'])->name('health-facility.book-doctor');
Route::get('/health-facility/{id}/lab-tests', [HealthFacilityController::class, 'labTests'])->name('health-facility.lab-tests');

Route::put('/health-facilities/{id}', [HealthFacilityController::class, 'updateHealthFacility'])->name('health-facilities.update');
Route::post('/health-facilities/{id}/change-password', [HealthFacilityController::class, 'changePassword'])->name('health-facilities.change-password');
Route::post('/health-facilities/{id}/upload-logo', [HealthFacilityController::class, 'uploadLogo'])->name('health-facilities.upload-logo');
Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
Route::delete('/patients/{patient}/delete', [PatientController::class, 'destroy'])->name('patients.delete');

Route::get('/patients/create', [PatientController::class, 'createGeneral'])->name('patients.general.create');
Route::post('/patients/general', [PatientController::class, 'storeGeneral'])->name('patients.general.store');


Route::get('/patients/{patient}/maternal', [PatientController::class, 'maternalDocuments'])
    ->name('patient.maternal');

Route::get('/patients/{patient}/profile', [PatientController::class, 'show'])->name('patients.profile');
    
Route::post('/patients/create', function (Request $request) {
    try {
        $validated = $request->validate([
            'patient_type' => 'required|in:new,existing',
            'health_facility_id' => 'required|exists:health_facilities,id',
        ]);

        if ($validated['patient_type'] === 'existing') {
            // Handle existing patient
            $existingValidation = $request->validate([
                'patient_id' => 'required|string|exists:patients,patient_id',
            ]);

            $patient = App\Models\Patient::where('patient_id', $existingValidation['patient_id'])->first();

            // Check if patient is already associated with this health facility
            if ($patient->health_facility_id == $validated['health_facility_id']) {
                return redirect()->route('health-facility.patients', ['id' => $validated['health_facility_id']])
                    ->with('error', 'Patient is already associated with this health facility.');
            }

            // Update patient with health facility association
            $patient->update([
                'health_facility_id' => $validated['health_facility_id'],
            ]);

            return redirect()->route('health-facility.patients', ['id' => $validated['health_facility_id']])
                ->with('success', 'Existing patient associated with health facility successfully.');
        } else {
            // Handle new patient
            $newValidation = $request->validate([
                'name' => 'required|string|max:255',
                'gender' => 'required|string|in:male,female,other',
                'birth_date' => 'required|date',
                'contact_number' => 'nullable|string'
            ]);

            $patient = App\Models\Patient::create(array_merge($newValidation, [
                'health_facility_id' => $validated['health_facility_id']
            ]));

            return redirect()->route('health-facility.patients', ['id' => $validated['health_facility_id']])
                ->with('success', 'Patient created successfully.');
        }
    } catch (\Illuminate\Validation\ValidationException $e) {
        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput();
    }
})->name('patients.create');

   
Route::get('/payment', [PaymentController::class, 'index'])->name('payment.form');
Route::post('/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');
Route::get('/success', function () {
        return "Payment Successful!";
    })->name('payment.success');
    Route::get('/cancel', function () {
        return "Payment Canceled!";
    })->name('payment.cancel');


// ...existing code...

// New appointment payment routes
Route::get('/appointment/pay/{appointment}', [PaymentController::class, 'showAppointmentPayForm'])->name('payment.appointment.pay');
Route::post('/appointment/checkout', [PaymentController::class, 'createAppointmentCheckout'])->name('payment.appointment.checkout');
Route::get('/appointment/success/{appointment}', [PaymentController::class, 'appointmentSuccess'])->name('payment.appointment.success');
Route::get('/appointment/cancel/{appointment}', [PaymentController::class, 'appointmentCancel'])->name('payment.appointment.cancel');