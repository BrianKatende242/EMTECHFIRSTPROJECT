<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminModelController extends Controller
{
    protected $map = [
        'doctors' => \App\Models\Doctor::class,
        'appointments' => \App\Models\Appointment::class,
        'payments' => \App\Models\Payment::class,
        'patients' => \App\Models\Patient::class,
        'schools' => \App\Models\School::class,
        'health-facilities' => \App\Models\HealthFacility::class,
        'doctor-availabilities' => \App\Models\Doctor::class,
        'users' => \App\User::class,
    ];

    protected function modelFor($key)
    {
        return $this->map[$key] ?? null;
    }

    /**
     * Generate a unique 6-letter alphabetic meeting slug for doctors.
     */
    protected function generateUniqueMeetingSlug()
    {
        // Format: keti-<6 digits>
        do {
            $digits = '';
            for ($i = 0; $i < 6; $i++) {
                $digits .= (string) random_int(0, 9);
            }
            $slug = 'keti-' . $digits;
        } while (\App\Models\Doctor::where('meeting_slug', $slug)->exists());

        return $slug;
    }

    public function index($modelKey = null)
    {
        // If no modelKey provided, determine it from the route name
        if (!$modelKey) {
            $routeName = request()->route()->getName();
            $modelKey = str_replace(['admin.', '.index'], '', $routeName);
        }

        $modelClass = $this->modelFor($modelKey);
        abort_unless($modelClass, 404);

        // Special handling for appointments: allow filters and eager loads
        if ($modelKey === 'appointments') {
            $q = $modelClass::with(['doctor', 'student', 'school', 'healthFacility', 'patient']);

            if (request()->filled('status')) {
                $q->where('status', request('status'));
            }

            if (request()->filled('doctor_id')) {
                $q->where('doctor_id', request('doctor_id'));
            }

            if (request()->filled('school_id')) {
                $q->where('school_id', request('school_id'));
            }

            if (request()->filled('q')) {
                $term = '%' . request('q') . '%';
                $q->where(function($r) use ($term) {
                    $r->where('reason', 'like', $term)
                      ->orWhere('id', 'like', $term);
                });
            }

            // Date range filter
            if (request()->filled('date_from')) {
                $q->whereDate('appointment_time', '>=', request('date_from'));
            }
            if (request()->filled('date_to')) {
                $q->whereDate('appointment_time', '<=', request('date_to'));
            }

            // Sorting
            $sort = request('sort', 'appointment_time_desc');
            if ($sort === 'appointment_time_asc') $q->orderBy('appointment_time', 'asc');
            elseif ($sort === 'appointment_time_desc') $q->orderBy('appointment_time', 'desc');
            elseif ($sort === 'created_at_desc') $q->orderBy('created_at', 'desc');
            elseif ($sort === 'created_at_asc') $q->orderBy('created_at', 'asc');

            $items = $q->latest()->paginate(30)->appends(request()->query());

        } elseif ($modelKey === 'payments') {
            $q = $modelClass::with(['appointment.patient', 'appointment.doctor']);

            if (request()->filled('status')) {
                $q->where('status', request('status'));
            }

            if (request()->filled('appointment_id')) {
                $q->where('appointment_id', request('appointment_id'));
            }

            if (request()->filled('q')) {
                $term = '%' . request('q') . '%';
                $q->where(function($r) use ($term) {
                    $r->where('reference_id', 'like', $term)
                      ->orWhere('phone_number', 'like', $term)
                      ->orWhere('amount', 'like', $term);
                });
            }

            // Date range filter
            if (request()->filled('date_from')) {
                $q->whereDate('created_at', '>=', request('date_from'));
            }
            if (request()->filled('date_to')) {
                $q->whereDate('created_at', '<=', request('date_to'));
            }

            // Sorting
            $sort = request('sort', 'created_at_desc');
            if ($sort === 'amount_asc') $q->orderBy('amount', 'asc');
            elseif ($sort === 'amount_desc') $q->orderBy('amount', 'desc');
            elseif ($sort === 'created_at_desc') $q->orderBy('created_at', 'desc');
            elseif ($sort === 'created_at_asc') $q->orderBy('created_at', 'asc');

            $items = $q->paginate(20)->appends(request()->query());

        } elseif ($modelKey === 'patients') {
            $q = $modelClass::with(['school', 'healthFacility', 'appointments']);

            if (request()->filled('gender')) {
                $q->where('gender', request('gender'));
            }

            if (request()->filled('school_id')) {
                $q->where('school_id', request('school_id'));
            }

            if (request()->filled('health_facility_id')) {
                $q->where('health_facility_id', request('health_facility_id'));
            }

            if (request()->filled('q')) {
                $term = '%' . request('q') . '%';
                $q->where(function($r) use ($term) {
                    $r->where('name', 'like', $term)
                      ->orWhere('patient_id', 'like', $term)
                      ->orWhere('contact_number', 'like', $term)
                      ->orWhere('parent_contact', 'like', $term);
                });
            }

            // Age range filter
            if (request()->filled('min_age')) {
                $q->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= ?', [request('min_age')]);
            }
            if (request()->filled('max_age')) {
                $q->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) <= ?', [request('max_age')]);
            }

            // Sorting
            $sort = request('sort', 'created_at_desc');
            if ($sort === 'name_asc') $q->orderBy('name', 'asc');
            elseif ($sort === 'name_desc') $q->orderBy('name', 'desc');
            elseif ($sort === 'age_asc') $q->orderByRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) ASC');
            elseif ($sort === 'age_desc') $q->orderByRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) DESC');
            elseif ($sort === 'created_at_desc') $q->orderBy('created_at', 'desc');
            elseif ($sort === 'created_at_asc') $q->orderBy('created_at', 'asc');

            $items = $q->paginate(20)->appends(request()->query());

        } elseif ($modelKey === 'schools') {
            $q = $modelClass::with(['students', 'doctors']);

            if (request()->filled('q')) {
                $term = '%' . request('q') . '%';
                $q->where(function($r) use ($term) {
                    $r->where('name', 'like', $term)
                      ->orWhere('email', 'like', $term)
                      ->orWhere('contact', 'like', $term);
                });
            }

            // Sorting
            $sort = request('sort', 'created_at_desc');
            if ($sort === 'name_asc') $q->orderBy('name', 'asc');
            elseif ($sort === 'name_desc') $q->orderBy('name', 'desc');
            elseif ($sort === 'student_count_asc') $q->withCount('students')->orderBy('students_count', 'asc');
            elseif ($sort === 'student_count_desc') $q->withCount('students')->orderBy('students_count', 'desc');
            elseif ($sort === 'created_at_desc') $q->orderBy('created_at', 'desc');
            elseif ($sort === 'created_at_asc') $q->orderBy('created_at', 'asc');

            $items = $q->paginate(20)->appends(request()->query());

        } elseif ($modelKey === 'health-facilities') {
            $q = $modelClass::with(['patients', 'doctors']);

            if (request()->filled('q')) {
                $term = '%' . request('q') . '%';
                $q->where(function($r) use ($term) {
                    $r->where('name', 'like', $term)
                      ->orWhere('email', 'like', $term)
                      ->orWhere('contact', 'like', $term);
                });
            }

            // Sorting
            $sort = request('sort', 'created_at_desc');
            if ($sort === 'name_asc') $q->orderBy('name', 'asc');
            elseif ($sort === 'name_desc') $q->orderBy('name', 'desc');
            elseif ($sort === 'created_at_desc') $q->orderBy('created_at', 'desc');
            elseif ($sort === 'created_at_asc') $q->orderBy('created_at', 'asc');

            $items = $q->paginate(20)->appends(request()->query());

        } elseif ($modelKey === 'doctors') {
            $q = $modelClass::with(['school', 'healthFacility']);

            if (request()->filled('specialization')) {
                $q->where('specialization', request('specialization'));
            }

            if (request()->filled('school_id')) {
                $q->where('school_id', request('school_id'));
            }

            if (request()->filled('health_facility_id')) {
                $q->where('health_facility_id', request('health_facility_id'));
            }

            if (request()->filled('q')) {
                $term = '%' . request('q') . '%';
                $q->where(function($r) use ($term) {
                    $r->where('name', 'like', $term)
                      ->orWhere('email', 'like', $term)
                      ->orWhere('specialization', 'like', $term);
                });
            }

            // Sorting
            $sort = request('sort', 'created_at_desc');
            if ($sort === 'name_asc') $q->orderBy('name', 'asc');
            elseif ($sort === 'name_desc') $q->orderBy('name', 'desc');
            elseif ($sort === 'created_at_desc') $q->orderBy('created_at', 'desc');
            elseif ($sort === 'created_at_asc') $q->orderBy('created_at', 'asc');

                        $items = $q->paginate(20)->appends(request()->query());

        } elseif ($modelKey === 'doctors') {
            $q = $modelClass::with(['school', 'healthFacility']);

            if (request()->filled('specialization')) {
                $q->where('specialization', request('specialization'));
            }

            if (request()->filled('school_id')) {
                $q->where('school_id', request('school_id'));
            }

            if (request()->filled('health_facility_id')) {
                $q->where('health_facility_id', request('health_facility_id'));
            }

            if (request()->filled('q')) {
                $term = '%' . request('q') . '%';
                $q->where(function($r) use ($term) {
                    $r->where('name', 'like', $term)
                      ->orWhere('email', 'like', $term)
                      ->orWhere('specialization', 'like', $term);
                });
            }

            // Sorting
            $sort = request('sort', 'created_at_desc');
            if ($sort === 'name_asc') $q->orderBy('name', 'asc');
            elseif ($sort === 'name_desc') $q->orderBy('name', 'desc');
            elseif ($sort === 'created_at_desc') $q->orderBy('created_at', 'desc');
            elseif ($sort === 'created_at_asc') $q->orderBy('created_at', 'asc');

            $items = $q->paginate(20)->appends(request()->query());

            // Export functionality
            if (request('export') === 'csv') {
                return $this->exportDoctorsCsv($items);
            }

        } elseif ($modelKey === 'users') {
            $q = $modelClass::where('is_admin', true);

            if (request()->filled('q')) {
                $term = '%' . request('q') . '%';
                $q->where(function($r) use ($term) {
                    $r->where('name', 'like', $term)
                      ->orWhere('email', 'like', $term);
                });
            }

            // Sorting
            $sort = request('sort', 'created_at_desc');
            if ($sort === 'name_asc') $q->orderBy('name', 'asc');
            elseif ($sort === 'name_desc') $q->orderBy('name', 'desc');
            elseif ($sort === 'created_at_desc') $q->orderBy('created_at', 'desc');
            elseif ($sort === 'created_at_asc') $q->orderBy('created_at', 'asc');

            $items = $q->paginate(20)->appends(request()->query());

        } else {
            $items = $modelClass::latest()->paginate(20);
        }

        $generatedSlug = null;
        if ($modelKey === 'doctors') {
            $generatedSlug = $this->generateUniqueMeetingSlug();
        }

        // Use dedicated view for doctors
        if ($modelKey === 'doctors') {
            $specializations = \App\Models\Doctor::select('specialization')->distinct()->pluck('specialization')->filter()->values();
            $schools = \App\Models\School::pluck('name','id');
            $hfs = \App\Models\HealthFacility::pluck('name','id');
            return view('admin.doctors.index', compact('modelKey', 'items', 'generatedSlug', 'specializations', 'schools', 'hfs'));
        }

        // Use dedicated view for users
        if ($modelKey === 'users') {
            return view('admin.users.index', compact('modelKey', 'items'));
        }

        // Use dedicated view for appointments
        if ($modelKey === 'appointments') {
            $statuses = ['pending', 'scheduled', 'completed', 'cancelled'];
            $doctors = \App\Models\Doctor::pluck('name','id');
            $schools = \App\Models\School::pluck('name','id');
            $hfs = \App\Models\HealthFacility::pluck('name','id');
            return view('admin.appointments.index', compact('modelKey', 'items', 'statuses', 'doctors', 'schools', 'hfs'));
        }

        // Use dedicated view for payments
        if ($modelKey === 'payments') {
            $statuses = ['pending', 'completed', 'failed', 'cancelled'];
            $appointments = \App\Models\Appointment::with('patient')->get()->pluck('patient.name', 'id');
            return view('admin.payments.index', compact('modelKey', 'items', 'statuses', 'appointments'));
        }

        // Use dedicated view for patients
        if ($modelKey === 'patients') {
            $genders = ['male', 'female', 'other'];
            $schools = \App\Models\School::pluck('name','id');
            $hfs = \App\Models\HealthFacility::pluck('name','id');
            return view('admin.patients.index', compact('modelKey', 'items', 'genders', 'schools', 'hfs'));
        }

        // Use dedicated view for schools
        if ($modelKey === 'schools') {
            return view('admin.schools.index', compact('modelKey', 'items'));
        }

        // Use dedicated view for health-facilities
        if ($modelKey === 'health-facilities') {
            return view('admin.health-facilities.index', compact('modelKey', 'items', 'generatedSlug'));
        }

        // Use dedicated view for doctor-availabilities
        if ($modelKey === 'doctor-availabilities') {
            $doctors = $modelClass::with(['availabilities', 'school', 'healthFacility'])->latest()->paginate(20)->appends(request()->query());
            return view('admin.doctor-availabilities.index', compact('doctors'));
        }

        return view('admin.model.index', compact('modelKey', 'items', 'generatedSlug'));
    }

    public function create($modelKey = null)
    {
        // If no modelKey provided, determine it from the route name
        if (!$modelKey) {
            $routeName = request()->route()->getName();
            $modelKey = str_replace(['admin.', '.create'], '', $routeName);
        }

        $modelClass = $this->modelFor($modelKey);
        abort_unless($modelClass, 404);

        $generatedSlug = null;
        if ($modelKey === 'doctors') {
            $generatedSlug = $this->generateUniqueMeetingSlug();
        }

        return view('admin.model.form', ['modelKey' => $modelKey, 'item' => null, 'generatedSlug' => $generatedSlug]);
    }

    public function store(Request $request, $modelKey = null)
    {
        // If no modelKey provided, determine it from the route name
        if (!$modelKey) {
            $routeName = request()->route()->getName();
            $modelKey = str_replace(['admin.', '.store'], '', $routeName);
        }

        $modelClass = $this->modelFor($modelKey);
        abort_unless($modelClass, 404);


        if ($modelKey === 'doctors') {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'specialization' => 'nullable|string|max:255',
                'contact' => 'nullable|string|max:255',
                'school_id' => 'nullable|exists:schools,id',
                'health_facility_id' => 'nullable|exists:health_facilities,id',
                'file_url' => 'nullable|file|image|max:2048',
            ]);

            // handle file upload
            if ($request->hasFile('file_url')) {
                $path = $request->file('file_url')->store('doctors', 'public');
                $validated['file_url'] = '/storage/' . $path;
            }

            // Always generate a unique 6-letter alphabetic meeting_slug server-side
            $validated['meeting_slug'] = $this->generateUniqueMeetingSlug();

            $item = $modelClass::create($validated);

        } elseif ($modelKey === 'users') {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:8',
                'is_admin' => 'boolean',
            ]);

            $validated['password'] = bcrypt($validated['password']);

            $item = $modelClass::create($validated);

        } else {
            $data = $request->except(['_token']);
            $item = $modelClass::create($data);
        }

        $redirectRoute = 'admin.' . $modelKey . '.index';
        return redirect()->route($redirectRoute)->with('success', 'Created successfully');
    }

    public function edit($modelKey = null, $id = null)
    {
        // If no modelKey provided, determine it from the route name
        if (!$modelKey) {
            $routeName = request()->route()->getName();
            $modelKey = str_replace(['admin.', '.edit'], '', $routeName);
        }

        $modelClass = $this->modelFor($modelKey);
        abort_unless($modelClass, 404);

        $item = $modelClass::findOrFail($id);
        return view('admin.model.form', compact('modelKey', 'item'));
    }

    public function update(Request $request, $modelKey = null, $id = null)
    {
        // If no modelKey provided, determine it from the route name
        if (!$modelKey) {
            $routeName = request()->route()->getName();
            $modelKey = str_replace(['admin.', '.update'], '', $routeName);
        }

        $modelClass = $this->modelFor($modelKey);
        abort_unless($modelClass, 404);

        $item = $modelClass::findOrFail($id);

        if ($modelKey === 'doctors') {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'specialization' => 'nullable|string|max:255',
                'contact' => 'nullable|string|max:255',
                'school_id' => 'nullable|exists:schools,id',
                'health_facility_id' => 'nullable|exists:health_facilities,id',
                'file_url' => 'nullable|file|image|max:2048',
            ]);

            if ($request->hasFile('file_url')) {
                $path = $request->file('file_url')->store('doctors', 'public');
                $validated['file_url'] = '/storage/' . $path;
            }

            // Do NOT allow manual meeting_slug updates; keep existing slug
            $item->update($validated);
        } elseif ($modelKey === 'users') {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $id,
                'password' => 'nullable|string|min:8',
                'is_admin' => 'boolean',
            ]);

            if (!empty($validated['password'])) {
                $validated['password'] = bcrypt($validated['password']);
            } else {
                unset($validated['password']);
            }

            $item->update($validated);
        } else {
            $data = $request->except(['_token', '_method']);
            $item->update($data);
        }

        // If the form included a redirect_to, send the user there (useful for modal submits on profile)
        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', 'Updated successfully');
        }

        $redirectRoute = 'admin.' . $modelKey . '.index';
        return redirect()->route($redirectRoute)->with('success', 'Updated successfully');
    }

    public function destroy($modelKey = null, $id = null)
    {
        // If no modelKey provided, determine it from the route name
        if (!$modelKey) {
            $routeName = request()->route()->getName();
            $modelKey = str_replace(['admin.', '.destroy'], '', $routeName);
        }

        $modelClass = $this->modelFor($modelKey);
        abort_unless($modelClass, 404);

        $item = $modelClass::findOrFail($id);
        $item->delete();

        $redirectRoute = 'admin.' . $modelKey . '.index';
        return redirect()->route($redirectRoute)->with('success', 'Deleted successfully');
    }

    public function showDoctor($id)
    {
        $doctor = \App\Models\Doctor::with(['appointments' => function($q){ $q->latest(); }])->findOrFail($id);

        $totalAppointments = $doctor->appointments()->count();
        $completed = $doctor->appointments()->where('status', 'completed')->count();
        $cancelled = $doctor->appointments()->where('status', 'cancelled')->count();
        $upcoming = $doctor->appointments()->whereIn('status', ['pending','scheduled'])->count();

        // Monthly appointment data for the current year
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $count = $doctor->appointments()
                ->whereYear('appointment_time', date('Y'))
                ->whereMonth('appointment_time', $month)
                ->count();
            $monthlyData[] = $count;
        }

        return view('admin.doctors.show', compact('doctor','totalAppointments','completed','cancelled','upcoming','monthlyData'));
    }

    public function sendLoginLinkToDoctor(Request $request, $id)
    {
        $doctor = \App\Models\Doctor::findOrFail($id);

        if (empty($doctor->email)) {
            return redirect()->back()->with('error', 'Doctor has no email to send login link to.');
        }

        // create a one-time token and send the login link
        $token = bin2hex(random_bytes(32));

        \Illuminate\Support\Facades\DB::table('one_time_logins')->insert([
            'doctor_id' => $doctor->id,
            'token' => $token,
            'expires_at' => now()->addMinutes(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $loginUrl = url('/one-time-login/' . $token);

        try {
            \Illuminate\Support\Facades\Mail::to($doctor->email)->send(new \App\Mail\LoginLinkMail($doctor, $loginUrl));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send login link: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'One-time login link sent to doctor email.');
    }

    public function sendAdminInvite(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:admin_invites,email',
        ]);

        $token = \App\Models\AdminInvite::generateToken();

        $invite = \App\Models\AdminInvite::create([
            'email' => $validated['email'],
            'token' => $token,
            'expires_at' => now()->addDays(7), // expires in 7 days
        ]);

        $inviteUrl = url('/admin/register/' . $token);

        try {
            \Illuminate\Support\Facades\Mail::to($validated['email'])->send(new \App\Mail\AdminInviteMail($inviteUrl));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send invite: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Admin invitation sent successfully.');
    }

    // Export doctors as CSV
    public function exportDoctorsCsv($doctors)
    {
        $filename = 'doctors-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($doctors) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id','name','email','specialization','contact','school','health_facility','meeting_slug','created_at']);
            foreach ($doctors as $doctor) {
                fputcsv($out, [
                    $doctor->id,
                    $doctor->name,
                    $doctor->email,
                    $doctor->specialization,
                    $doctor->contact,
                    $doctor->school->name ?? '',
                    $doctor->healthFacility->name ?? '',
                    $doctor->meeting_slug,
                    $doctor->created_at
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Bulk update appointments (complete or cancel)
    public function bulkUpdateAppointments(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);
        if (!in_array($action, ['complete','cancel'])) {
            return redirect()->back()->with('error', 'Invalid action');
        }

        $status = $action === 'complete' ? 'completed' : 'cancelled';
        \App\Models\Appointment::whereIn('id', $ids)->update(['status' => $status, 'updated_at' => now()]);

        return redirect()->back()->with('success', 'Bulk update applied');
    }

    // Bulk update payments (complete or fail)
    public function bulkUpdatePayments(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);
        if (!in_array($action, ['complete','fail'])) {
            return redirect()->back()->with('error', 'Invalid action');
        }

        $status = $action === 'complete' ? 'completed' : 'failed';
        \App\Models\Payment::whereIn('id', $ids)->update(['status' => $status, 'updated_at' => now()]);

        return redirect()->back()->with('success', 'Bulk update applied');
    }
}
