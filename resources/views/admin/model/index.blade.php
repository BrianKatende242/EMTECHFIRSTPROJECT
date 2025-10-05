@extends('layouts.base')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">{{ ucfirst(str_replace('-', ' ', $modelKey)) }}</h2>
        @unless($modelKey === 'doctors')
            <div>
                @if($modelKey === 'appointments')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAppointmentModal">Create</button>
                @else
                    <a href="{{ route('admin.model.create', $modelKey) }}" class="btn btn-primary">Create</a>
                @endif
            </div>
        @endunless
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        @if($modelKey === 'doctors')
        <div class="mb-3 d-flex justify-content-between">
            <div>
                <input id="admin-search" class="form-control form-control-sm" placeholder="Search doctors by name or email" style="width:320px;">
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createDoctorModal">Create Doctor</button>
            </div>
        </div>

    <table class="table table-striped table-sm text-dark" id="admin-doctors-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Specialization</th>
                    <th>Contact</th>
                    <th>Meeting Slug</th>
                    <th>Created</th>
                    <th class="text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr data-name="{{ strtolower($item->name ?? '') }}" data-email="{{ strtolower($item->email ?? '') }}">
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->name ?? '-' }}</td>
                    <td>{{ $item->email ?? '-' }}</td>
                    <td>{{ $item->specialization ?? '-' }}</td>
                    <td>{{ $item->contact ?? '-' }}</td>
                    <td>{{ $item->meeting_slug ?? '-' }}</td>
                    <td>{{ optional($item->created_at)->format('Y-m-d') ?? '-' }}</td>
                    <td class="text-nowrap">
                        <div class="d-flex gap-2 flex-nowrap">
                            <a href="{{ route('admin.model.edit', [$modelKey, $item->id]) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <a href="{{ route('admin.doctors.show', $item->id) }}" class="btn btn-sm btn-info text-white">View Profile</a>
                            <form action="{{ route('admin.doctors.send-login', $item->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Send login OTP to this doctor?')">
                                @csrf
                                <button class="btn btn-sm btn-warning text-dark">Send Login Link</button>
                            </form>
                            <form action="{{ route('admin.model.destroy', [$modelKey, $item->id]) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Delete this doctor?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Create Doctor Modal -->
        <div class="modal fade" id="createDoctorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Doctor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.model.store', $modelKey) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Name</label>
                                    <input name="name" class="form-control" required value="{{ old('name') }}">
                                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Email</label>
                                    <input name="email" type="email" class="form-control" value="{{ old('email') }}">
                                    @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Specialization</label>
                                    <input name="specialization" class="form-control" value="{{ old('specialization') }}">
                                    @error('specialization')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Contact</label>
                                    <input name="contact" class="form-control" value="{{ old('contact') }}">
                                    @error('contact')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">School</label>
                                    @php $schools = \App\Models\School::pluck('name','id'); @endphp
                                    <select name="school_id" class="form-select form-control">
                                        <option value="">-- none --</option>
                                        @foreach($schools as $id => $label)
                                            <option value="{{ $id }}" @if(old('school_id') == $id) selected @endif>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Health Facility</label>
                                    @php $hfs = \App\Models\HealthFacility::pluck('name','id'); @endphp
                                    <select name="health_facility_id" class="form-select form-control">
                                        <option value="">-- none --</option>
                                        @foreach($hfs as $id => $label)
                                            <option value="{{ $id }}" @if(old('health_facility_id') == $id) selected @endif>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Profile Image</label>
                                    <input name="file_url" type="file" accept="image/*" class="form-control">
                                    @error('file_url')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Meeting Slug</label>
                                    <input name="meeting_slug" class="form-control" value="{{ old('meeting_slug', $generatedSlug ?? '') }}" readonly>

                                    <label class="form-label small mt-2">Meeting URL</label>
                                    <div class="input-group">
                                        <input id="modal-meeting-url" type="text" class="form-control" value="{{ 'https://meet.jit.si/' . (old('meeting_slug', $generatedSlug ?? '')) }}" readonly>
                                        <button type="button" id="copy-modal-meeting-url" class="btn btn-outline-secondary">Copy</button>
                                    </div>
                                    <div id="modal-copy-feedback" class="small text-success mt-1" style="display:none">Copied to clipboard</div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @else

        @if($modelKey === 'appointments')
            <div class="mb-3 d-flex gap-2 align-items-center">
                @php $statuses = \App\Models\Appointment::select('status')->distinct()->pluck('status')->filter()->values(); @endphp
                <form method="GET" class="d-flex gap-2 align-items-center">
                    <input name="q" value="{{ request('q') }}" placeholder="Search reason or id" class="form-control form-control-sm">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All statuses</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}" @if(request('status')== $st) selected @endif>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                    @php $doctors = \App\Models\Doctor::pluck('name','id'); @endphp
                    <select name="doctor_id" class="form-select form-select-sm">
                        <option value="">All doctors</option>
                        @foreach($doctors as $id => $label)
                            <option value="{{ $id }}" @if(request('doctor_id') == $id) selected @endif>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
                        <select name="sort" class="form-select form-select-sm">
                        <option value="appointment_time_desc" @if(request('sort')=='appointment_time_desc') selected @endif>Appointment (new → old)</option>
                        <option value="appointment_time_asc" @if(request('sort')=='appointment_time_asc') selected @endif>Appointment (old → new)</option>
                        <option value="created_at_desc" @if(request('sort')=='created_at_desc') selected @endif>Created (new → old)</option>
                        <option value="created_at_asc" @if(request('sort')=='created_at_asc') selected @endif>Created (old → new)</option>
                    </select>
                        <button class="btn btn-sm btn-primary">Filter</button>
                        </form>
                        <form method="GET" action="{{ route('admin.appointments.export') }}" class="d-flex align-items-center ms-2">
                            <input type="hidden" name="q" value="{{ request('q') }}">
                            <input type="hidden" name="status" value="{{ request('status') }}">
                            <input type="hidden" name="doctor_id" value="{{ request('doctor_id') }}">
                            <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                            <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                            <button class="btn btn-sm btn-outline-secondary">Export CSV</button>
                        </form>
                </form>
                    <div class="ms-2">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createAppointmentModal">Create Appointment</button>
                    </div>
            </div>

            <table class="table table-striped table-sm text-dark">
                <form id="bulk-form" method="POST" action="{{ route('admin.appointments.bulk') }}">
                    @csrf
                    <input type="hidden" name="action" id="bulk-action-input" value="">
                    <table class="table table-striped table-sm text-dark">
                <thead>
                    <tr>
                            <th><input type="checkbox" id="select-all"></th>
                        <th>ID</th>
                        <th>When</th>
                        <th>Doctor</th>
                        <th>Institution</th>
                        <th>Patient</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="row-checkbox"></td>
                                <td>{{ $item->id }}</td>
                            <td>{{ optional($item->appointment_time)->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>{{ $item->doctor->name ?? '-' }}</td>
                            <td>{{ $item->school->name ?? $item->healthFacility->name ?? '-' }}</td>
                            <td>{{ $item->student->name ?? $item->patient->name ?? '-' }}</td>
                            <td>{{ ucfirst($item->status ?? 'unknown') }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.model.edit', ['appointments', $item->id]) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                @if($item->status !== 'completed')
                                        <button type="button" class="btn btn-sm btn-success ajax-complete" data-id="{{ $item->id }}">Complete</button>
                                @endif
                                @if($item->status !== 'cancelled')
                                        <button type="button" class="btn btn-sm btn-warning text-dark ajax-cancel" data-id="{{ $item->id }}">Cancel</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-success" id="bulk-complete">Mark selected as complete</button>
                        <button type="button" class="btn btn-sm btn-warning" id="bulk-cancel">Cancel selected</button>
                    </div>
                </form>

        @else

            <table class="table table-striped text-dark">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Preview</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td style="max-width:420px;word-break:break-word;">{{ json_encode($item->toArray()) }}</td>
                        <td>{{ $item->created_at ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.model.edit', [$modelKey, $item->id]) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('admin.model.destroy', [$modelKey, $item->id]) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Delete this item?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        @endif

        @endif
    </div>

    <div class="mt-3">{{ $items->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const search = document.getElementById('admin-search');
    if (!search) return;
    const rows = Array.from(document.querySelectorAll('#admin-doctors-table tbody tr'));
    search.addEventListener('input', function(){
        const q = search.value.trim().toLowerCase();
        rows.forEach(r => {
            const name = r.getAttribute('data-name') || '';
            const email = r.getAttribute('data-email') || '';
            if (!q || name.includes(q) || email.includes(q)) r.style.display = '';
            else r.style.display = 'none';
        });
    });
});
</script>
<!-- Create Appointment Modal -->
<div class="modal fade" id="createAppointmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="create-appointment-errors" class="alert alert-danger d-none"></div>
                <form id="create-appointment-form">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-semibold">Doctor <sup class="text-danger">*</sup></label>
                            <select name="doctor_id" class="form-select form-select-sm text-dark" required aria-label="Select doctor">
                                <option value="" disabled selected>-- select doctor --</option>
                                @foreach(\App\Models\Doctor::pluck('name','id') as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-semibold">Duration (mins) <sup class="text-danger">*</sup></label>
                            <select name="duration" class="form-select form-select-sm text-dark" required aria-label="Duration in minutes">
                                <option value="" disabled selected>-- choose duration --</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="45">45</option>
                                <option value="60">60</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-semibold">Appointment Time <span class="text-danger">*</span></label>
                            <input name="appointment_time" type="datetime-local" class="form-control form-control-sm text-dark" required>
                            <div class="form-text">Times are in your local timezone.</div>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                            <input name="reason" class="form-control form-control-sm text-dark" placeholder="Brief reason for visit" required>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-semibold">School (optional)</label>
                            <select name="school_id" class="form-select form-select-sm text-dark">
                                <option value="" selected>-- none --</option>
                                @foreach(\App\Models\School::pluck('name','id') as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-semibold">Student (optional)</label>
                            <select name="student_id" class="form-select form-select-sm text-dark">
                                <option value="" selected>-- none --</option>
                                @foreach(\App\Models\Student::pluck('name','id') as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-semibold">Health Facility (optional)</label>
                            <select name="health_facility_id" class="form-select form-select-sm text-dark">
                                <option value="" selected>-- none --</option>
                                @foreach(\App\Models\HealthFacility::pluck('name','id') as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-semibold">Patient (optional)</label>
                            <select name="patient_id" class="form-select form-select-sm text-dark">
                                <option value="" selected>-- none --</option>
                                @foreach(\App\Models\Patient::pluck('name','id') as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="create-appointment-submit" class="btn btn-primary">Create</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const submit = document.getElementById('create-appointment-submit');
    submit?.addEventListener('click', function(){
        const form = document.getElementById('create-appointment-form');
        const data = new FormData(form);
        fetch('/appointments', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: data
        }).then(async res => {
            if (res.status === 422) {
                const json = await res.json();
                const errors = json.errors || {};
                const el = document.getElementById('create-appointment-errors');
                el.style.display = 'block';
                el.innerHTML = Object.values(errors).map(v => '<div>'+v[0]+'</div>').join('');
                return;
            }
            if (!res.ok) {
                const txt = await res.text();
                alert('Failed: ' + txt);
                return;
            }

            // success — close modal and reload
            var myModalEl = document.getElementById('createAppointmentModal');
            var modal = bootstrap.Modal.getInstance(myModalEl);
            modal.hide();
            location.reload();
        }).catch(err => { alert('Error: ' + err); });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    // If there were validation errors or old input for creating a doctor, open the modal
    @if($errors->any() && old())
        var myModal = new bootstrap.Modal(document.getElementById('createDoctorModal'));
        myModal.show();
    @endif
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const modalCopy = document.getElementById('copy-modal-meeting-url');
    if (modalCopy) {
        modalCopy.addEventListener('click', function(){
            const target = document.getElementById('modal-meeting-url');
            if (!target) return;
            target.select();
            target.setSelectionRange(0, 99999);
            try { document.execCommand('copy'); } catch(e) { navigator.clipboard && navigator.clipboard.writeText && navigator.clipboard.writeText(target.value); }
            const fb = document.getElementById('modal-copy-feedback');
            if (fb) { fb.style.display = 'block'; setTimeout(() => fb.style.display = 'none', 2000); }
        });
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    // select all checkbox
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function(){
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = selectAll.checked);
        });
    }

    // AJAX actions
    function ajaxPatch(url, cb) {
        fetch(url, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        }).then(r => r.json()).then(cb).catch(err => console.error(err));
    }

    document.querySelectorAll('.ajax-complete').forEach(btn => {
        btn.addEventListener('click', function(){
            if (!confirm('Mark appointment as completed?')) return;
            const id = this.dataset.id;
            ajaxPatch('/appointments/' + id + '/complete', function(res){ location.reload(); });
        });
    });

    document.querySelectorAll('.ajax-cancel').forEach(btn => {
        btn.addEventListener('click', function(){
            if (!confirm('Cancel this appointment?')) return;
            const id = this.dataset.id;
            ajaxPatch('/appointments/' + id + '/cancel', function(res){ location.reload(); });
        });
    });

    // bulk actions
    document.getElementById('bulk-complete')?.addEventListener('click', function(){
        if (!confirm('Mark selected appointments as completed?')) return;
        document.getElementById('bulk-action-input').value = 'complete';
        document.getElementById('bulk-form').submit();
    });
    document.getElementById('bulk-cancel')?.addEventListener('click', function(){
        if (!confirm('Cancel selected appointments?')) return;
        document.getElementById('bulk-action-input').value = 'cancel';
        document.getElementById('bulk-form').submit();
    });
});
</script>
@endpush
