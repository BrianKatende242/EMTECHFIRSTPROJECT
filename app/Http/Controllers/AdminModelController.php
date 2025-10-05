<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminModelController extends Controller
{
    protected $map = [
        'doctors' => \App\Models\Doctor::class,
        'appointments' => \App\Models\Appointment::class,
        'students' => \App\Models\Student::class,
        'schools' => \App\Models\School::class,
        'health-facilities' => \App\Models\HealthFacility::class,
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

    public function index($modelKey)
    {
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

        } else {
            $items = $modelClass::latest()->paginate(20);
        }

        $generatedSlug = null;
        if ($modelKey === 'doctors') {
            $generatedSlug = $this->generateUniqueMeetingSlug();
        }

        return view('admin.model.index', compact('modelKey', 'items', 'generatedSlug'));
    }

    public function create($modelKey)
    {
        $modelClass = $this->modelFor($modelKey);
        abort_unless($modelClass, 404);

        $generatedSlug = null;
        if ($modelKey === 'doctors') {
            $generatedSlug = $this->generateUniqueMeetingSlug();
        }

        return view('admin.model.form', ['modelKey' => $modelKey, 'item' => null, 'generatedSlug' => $generatedSlug]);
    }

    public function store(Request $request, $modelKey)
    {
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

        } else {
            $data = $request->except(['_token']);
            $item = $modelClass::create($data);
        }

        return redirect()->route('admin.model.index', $modelKey)->with('success', 'Created successfully');
    }

    public function edit($modelKey, $id)
    {
        $modelClass = $this->modelFor($modelKey);
        abort_unless($modelClass, 404);

        $item = $modelClass::findOrFail($id);
        return view('admin.model.form', compact('modelKey', 'item'));
    }

    public function update(Request $request, $modelKey, $id)
    {
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
        } else {
            $data = $request->except(['_token', '_method']);
            $item->update($data);
        }

        // If the form included a redirect_to, send the user there (useful for modal submits on profile)
        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', 'Updated successfully');
        }

        return redirect()->route('admin.model.index', $modelKey)->with('success', 'Updated successfully');
    }

    public function destroy($modelKey, $id)
    {
        $modelClass = $this->modelFor($modelKey);
        abort_unless($modelClass, 404);

        $item = $modelClass::findOrFail($id);
        $item->delete();

        return redirect()->route('admin.model.index', $modelKey)->with('success', 'Deleted successfully');
    }

    public function showDoctor($id)
    {
        $doctor = \App\Models\Doctor::with(['appointments' => function($q){ $q->latest(); }])->findOrFail($id);

        $totalAppointments = $doctor->appointments()->count();
        $completed = $doctor->appointments()->where('status', 'completed')->count();
        $cancelled = $doctor->appointments()->where('status', 'cancelled')->count();
        $upcoming = $doctor->appointments()->whereIn('status', ['pending','scheduled'])->count();

        return view('admin.doctors.show', compact('doctor','totalAppointments','completed','cancelled','upcoming'));
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

    // Export appointments as CSV based on current filters
    public function exportAppointmentsCsv(Request $request)
    {
        $q = \App\Models\Appointment::with(['doctor','student','school','healthFacility','patient']);
        if ($request->filled('status')) $q->where('status', $request->status);
        if ($request->filled('doctor_id')) $q->where('doctor_id', $request->doctor_id);
        if ($request->filled('date_from')) $q->whereDate('appointment_time', '>=', $request->date_from);
        if ($request->filled('date_to')) $q->whereDate('appointment_time', '<=', $request->date_to);

        $items = $q->latest()->get();

        $filename = 'appointments-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($items) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id','appointment_time','doctor','institution','patient','status','reason']);
            foreach ($items as $i) {
                $institution = $i->school->name ?? $i->healthFacility->name ?? '';
                $patient = $i->student->name ?? $i->patient->name ?? '';
                fputcsv($out, [$i->id, optional($i->appointment_time)->toDateTimeString(), $i->doctor->name ?? '', $institution, $patient, $i->status, $i->reason]);
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
}
