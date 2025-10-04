@extends('layouts.base')

@section('content')
<div class="container py-4 card shadow-sm mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Appointments</h2>
        <div>
            <input id="search" class="form-control form-control-sm" placeholder="Search by patient or school" style="width:280px; display:inline-block;">
        </div>
    </div>

    <div class="row mb-3 g-2">
        <div class="col-md-3">
            <select id="filter-status" class="form-select form-select-sm">
                <option value="">All statuses</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="col-md-3">
            <input id="filter-from" type="date" class="form-control form-control-sm" placeholder="From">
        </div>
        <div class="col-md-3">
            <input id="filter-to" type="date" class="form-control form-control-sm" placeholder="To">
        </div>
        <div class="col-md-3 text-end">
            <button id="clear-filters" class="btn btn-sm btn-outline-secondary">Clear</button>
        </div>
    </div>

    <div id="appointments-container">
    @if($appointments->count())
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle text-dark" id="appointments-table">
                <thead class="table-primary">
                    <tr>
                        <th>Date & Time</th>
                        <th>Patient</th>
                        <th>School</th>
                        <th>Health Facility</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($appointments as $appointment)
                    <tr data-id="{{ $appointment->id }}">
                        <td class="appt-time">{{ $appointment->appointment_time->format('M d, Y h:i A') }}</td>
                        <td class="appt-patient">{{ $appointment->student->name ?? $appointment->patient->name ?? '-' }}</td>
                        <td class="appt-school">{{ $appointment->school->name ?? '-' }}</td>
                        <td class="appt-facility">{{ $appointment->healthFacility->name ?? 'N/A' }}</td>
                        <td>{{ $appointment->duration }} mins</td>
                        <td class="appt-status">
                            <span class="badge bg-{{ $appointment->status == 'confirmed' ? 'success' : ($appointment->status == 'cancelled' ? 'danger' : ($appointment->status=='completed' ? 'secondary' : 'warning')) }}">{{ ucfirst($appointment->status) }}</span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="" class="btn btn-sm btn-info" title="View">
                                    <i class="fa fa-video-camera"></i>
                                </a>
                                @if($appointment->status !== 'cancelled')
                                    <button class="btn btn-sm btn-danger btn-cancel" data-id="{{ $appointment->id }}" title="Cancel">
                                        <i class="fa fa-times"></i>
                                    </button>
                                @endif
                                @if($appointment->status === 'pending')
                                    <button class="btn btn-sm btn-success btn-complete" data-id="{{ $appointment->id }}" title="Mark Complete">
                                        <i class="fa fa-check"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-info mt-4">No appointments found.</div>
    @endif
    </div>
</div>

@push('scripts')
<script>
    (function(){
        const table = document.getElementById('appointments-table');
        const search = document.getElementById('search');
        const filterStatus = document.getElementById('filter-status');
        const filterFrom = document.getElementById('filter-from');
        const filterTo = document.getElementById('filter-to');
        const clearBtn = document.getElementById('clear-filters');

        function matchesFilters(row){
            const status = row.querySelector('.appt-status').innerText.trim().toLowerCase();
            const patient = row.querySelector('.appt-patient').innerText.toLowerCase();
            const school = row.querySelector('.appt-school').innerText.toLowerCase();
            const timeText = row.querySelector('.appt-time').innerText;
            const time = new Date(timeText);

            if (filterStatus.value && status !== filterStatus.value) return false;
            if (search.value){
                const q = search.value.toLowerCase();
                if (!patient.includes(q) && !school.includes(q)) return false;
            }
            if (filterFrom.value){
                const from = new Date(filterFrom.value);
                if (time < from) return false;
            }
            if (filterTo.value){
                const to = new Date(filterTo.value);
                to.setHours(23,59,59,999);
                if (time > to) return false;
            }
            return true;
        }

        function applyFilters(){
            const rows = table ? table.querySelectorAll('tbody tr') : [];
            rows.forEach(r => {
                if (matchesFilters(r)) r.style.display = '';
                else r.style.display = 'none';
            });
        }

        [search, filterStatus, filterFrom, filterTo].forEach(el => el && el.addEventListener('input', applyFilters));
        if (clearBtn) clearBtn.addEventListener('click', function(){ search.value=''; filterStatus.value=''; filterFrom.value=''; filterTo.value=''; applyFilters(); });

        // AJAX appointment actions
        async function sendAction(url, method='PATCH'){
            try{
                const res = await fetch(url, { method, headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept':'application/json' } });
                if (!res.ok) throw new Error('Request failed');
                return await res.json().catch(()=>({ success:true }));
            } catch(e){
                alert('Action failed: '+e.message);
                return null;
            }
        }

        document.querySelectorAll('.btn-cancel').forEach(btn => {
            btn.addEventListener('click', async function(){
                if (!confirm('Cancel this appointment?')) return;
                const id = btn.getAttribute('data-id');
                const url = `/appointments/${id}/cancel`;
                const data = await sendAction(url,'PATCH');
                if (data && data.success){
                    const row = document.querySelector(`tr[data-id='${id}']`);
                    if (row){
                        row.querySelector('.appt-status').innerHTML = '<span class="badge bg-danger">Cancelled</span>';
                        btn.remove();
                    }
                }
            });
        });

        document.querySelectorAll('.btn-complete').forEach(btn => {
            btn.addEventListener('click', async function(){
                if (!confirm('Mark as completed?')) return;
                const id = btn.getAttribute('data-id');
                const url = `/appointments/${id}/complete`;
                const data = await sendAction(url,'PATCH');
                if (data && data.success){
                    const row = document.querySelector(`tr[data-id='${id}']`);
                    if (row){
                        row.querySelector('.appt-status').innerHTML = '<span class="badge bg-secondary">Completed</span>';
                        btn.remove();
                    }
                }
            });
        });

    })();
</script>
@endpush

@endsection
