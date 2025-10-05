<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PatientController; 
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\FinanceDashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminModelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthFacilityController;
use App\Http\Controllers\ApiDashboardController;
use App\Models\NewsletterSubscriber;
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


// ✅ Email Verification Route for Newsletter
Route::get('/verify-newsletter/{token}', function ($token) {
    $subscriber = NewsletterSubscriber::where('verification_token', $token)->first();

    if (!$subscriber) {
        return response()->json(['message' => 'Invalid or expired verification link.'], 404);
    }

    // Mark as verified
    $subscriber->update(['is_verified' => true]);

    return response()->json(['message' => 'Email confirmed!']);
})->name('verify-newsletter');

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




use App\Http\Controllers\SchoolController;

Route::get('/school-dashboard/{school}', [SchoolController::class, 'showDashboard'])->name('school.dashboard');


Route::get('/students/{school}', function (App\Models\School $school) {
    return view('students', [
        'school' => $school,
        'students' => $school->students()->latest()->get()
    ]);
})->name('students');


Route::delete('/students/{student}/delete', function ($studentId) {
    $student = App\Models\Student::findOrFail($studentId);
    $schoolId = $student->school_id;

    // Deleting the student will cascade and remove related appointments (handled in Student model)
    $student->delete();

    return redirect()->route('students', ['school' => $schoolId])->with('success', 'Student deleted successfully.');
})->name('students.delete');

Route::post('/students/create', function (Request $request) {
    try {
        $validated = $request->validate([
            'name' => 'required',
            'grade' => 'required',
            'age' => 'required',
            'parent_contact' => 'required',
            'birth_date' => 'required',
            'school_id' => 'required',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'errors' => $e->errors(),
        ], 422);
    }

    $student = App\Models\Student::create($validated);

    return redirect()->route('students', ['school' => $student->school_id]);
})->name('students.create');


Route::get('/lab-tests/{school}', function (App\Models\School $school) {
    // Paginate lab tests for the school (15 per page)
    $labTests = $school->labTests()->with('student')->latest()->paginate(15);

    return view('lab-tests', [
        'school' => $school,
        'labTests' => $labTests,
        'students' => $school->students()->latest()->get()
    ]);
})->name('lab-tests');

// Handle lab test form submissions from web forms (redirect back to lab-tests page)
Route::post('/lab-tests', function (Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'school_id' => 'required|exists:schools,id',
        'student_id' => 'required|exists:students,id',
        'test_type' => 'required|string',
        'notes' => 'nullable|string'
    ]);

    $labTest = App\Models\LabTest::create(array_merge($validated, ['status' => 'pending']));

    return redirect()->route('lab-tests', ['school' => $validated['school_id']])->with('success', 'Lab test requested successfully.');
})->name('lab-tests.store');

// Delete a lab test (web)
Route::delete('/lab-tests/{labTest}', function (App\Models\LabTest $labTest) {
    $schoolId = $labTest->school_id;
    $labTest->delete();
    return redirect()->route('lab-tests', ['school' => $schoolId])->with('success', 'Lab test deleted');
})->name('lab-tests.destroy');

// Mark a lab test as completed (web)
Route::post('/lab-tests/{labTest}/complete', function (App\Models\LabTest $labTest) {
    $labTest->update(['status' => 'completed']);
    return redirect()->route('lab-tests', ['school' => $labTest->school_id])->with('success', 'Lab test marked completed');
})->name('lab-tests.complete');


Route::get('/book-doctor/{school}/', function (App\Models\School $school) {
    return view('book-doctor', [
        'school' => $school,
        'appointments' => $school->appointments()->with(['student', 'doctor'])->latest()->get(),
        'patients' => $school->students()->latest()->get(),
        'doctors' => Doctor::latest()->get()
    ]);
})->name('book-doctor');


Route::get('/doctor/{doctorId}/appointments', [DoctorController::class, 'getDoctorAppointments'])->name('doctor.appointments');
// Appointment actions
Route::patch('/appointments/{appointment}/cancel', [\App\Http\Controllers\AppointmentController::class, 'cancel'])->name('appointments.cancel');
Route::patch('/appointments/{appointment}/complete', [\App\Http\Controllers\AppointmentController::class, 'complete'])->name('appointments.complete');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Simple admin area (protected)
Route::prefix('admin')->middleware(['auth', 'can:admin'])->group(function(){
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/{modelKey}', [AdminModelController::class, 'index'])->name('admin.model.index');
    Route::get('/{modelKey}/create', [AdminModelController::class, 'create'])->name('admin.model.create');
    Route::post('/{modelKey}', [AdminModelController::class, 'store'])->name('admin.model.store');
    // Appointments extras
    Route::post('/appointments/bulk', [AdminModelController::class, 'bulkUpdateAppointments'])->name('admin.appointments.bulk');
    Route::get('/appointments/export', [AdminModelController::class, 'exportAppointmentsCsv'])->name('admin.appointments.export');
    Route::get('/{modelKey}/{id}/edit', [AdminModelController::class, 'edit'])->name('admin.model.edit');
    Route::get('/doctors/{id}', [AdminModelController::class, 'showDoctor'])->name('admin.doctors.show');
    Route::post('/doctors/{id}/send-login-link', [AdminModelController::class, 'sendLoginLinkToDoctor'])->name('admin.doctors.send-login');
    Route::put('/{modelKey}/{id}', [AdminModelController::class, 'update'])->name('admin.model.update');
    Route::delete('/{modelKey}/{id}', [AdminModelController::class, 'destroy'])->name('admin.model.destroy');
});
Route::get('doctor/{doctorId}/meeting-link/', function ($doctorId) {
    $doctor = Doctor::findOrFail($doctorId);
    return view('meeting-link', [
        'appointments' => $doctor->appointments()->with(['student', 'school', 'healthFacility'])->latest()->get(),
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

// One-time login link consume route (public)
Route::get('/one-time-login/{token}', [\App\Http\Controllers\OneTimeLoginController::class, 'consume'])->name('one-time-login.consume');


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

Route::put('/health-facilities/{id}', [HealthFacilityController::class, 'updateHealthFacility'])->name('health-facilities.update');
Route::post('/health-facilities/{id}/change-password', [HealthFacilityController::class, 'changePassword'])->name('health-facilities.change-password');
Route::post('/health-facilities/{id}/upload-logo', [HealthFacilityController::class, 'uploadLogo'])->name('health-facilities.upload-logo');
Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');


Route::get('/patients/{patient}/maternal', [PatientController::class, 'maternalDocuments'])
    ->name('patient.maternal');
    
Route::post('/patients/create', function (Request $request) {
    try {
        $validated = $request->validate([
            'health_facility_id' => 'required|exists:health_facilities,id',
            'name' => 'required|string|max:255',
            'gender' => 'required|string|in:male,female,other',
            'birth_date' => 'required|date',
            'contact_number' => 'nullable|string',
            'medical_history' => 'nullable|string'
        ]);

        $patient = App\Models\Patient::create($validated);

        return redirect()->route('health-facility.dashboard', ['id' => $validated['health_facility_id']]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
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
Route::post('/appointment/checkout', [PaymentController::class, 'createAppointmentCheckout'])->name('payment.appointment.checkout');
Route::get('/appointment/success/{appointment}', [PaymentController::class, 'appointmentSuccess'])->name('payment.appointment.success');
Route::get('/appointment/cancel/{appointment}', [PaymentController::class, 'appointmentCancel'])->name('payment.appointment.cancel');