@extends('layouts.base')

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card availability-card">
            <div class="card-header d-flex justify-content-between align-items-center availability-header">
                <div>
                    <h4 class="mb-0">Manage Availability</h4>
                    <small class="text-muted">Set which days you're available and the maximum appointments allowed.</small>
                </div>
                <div>
                    <a href="{{ route('doctor.dashboard', ['doctorId' => $doctor->id]) }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
                </div>
            </div>
            <div class="card-body">

                <div id="alert-placeholder"></div>

                <form id="availability-form" method="POST" action="{{ route('doctor.update-availability', ['doctor' => $doctor->id]) }}">
                    @csrf
                    @php
                        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                        $avMap = [];
                        foreach($doctor->availabilities ?? [] as $av) {
                            $avMap[strtolower($av->day)] = $av;
                        }
                    @endphp

                    <div class="row availability-grid">
                        @foreach($days as $day)
                            @php $key = strtolower($day); $av = $avMap[$key] ?? null; @endphp
                            <div class="col-md-6">
                                <div class="card day-card h-100 {{ ($av && $av->available) ? 'is-available' : 'is-off' }}" data-day-card="{{ $day }}">
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="day-icon" aria-hidden="true">{{ substr($day,0,2) }}</div>
                                                    <div class="day-sep" aria-hidden="true"></div>
                                                    <div>
                                                        <div class="day-title">{{ $day }}</div>
                                                        <div class="small text-muted">Configure availability and capacity</div>
                                                    </div>
                                                </div>
                                                @php $isOn = ($av && $av->available); @endphp
                                                <span class="status-pill {{ $isOn ? 'pill-on' : 'pill-off' }}" data-status-pill="{{ $day }}">{{ $isOn ? 'Available' : 'Off' }}</span>
                                            </div>
                                            <div class="mt-2" style="max-width: 200px;">
                                                <label class="small mb-1" for="max_{{ $key }}">Max appointments</label>
                                                <input id="max_{{ $key }}" type="number" min="0" class="form-control form-control-sm day-max" data-day="{{ $day }}" name="days[{{ $day }}][max_appointments]" value="{{ $av->max_appointments ?? 0 }}">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input day-available" type="checkbox" role="switch" id="avail_{{ $key }}" data-day="{{ $day }}" name="days[{{ $day }}][available]" {{ ($av && $av->available) ? 'checked' : '' }}>
                                                <label class="form-check-label small ms-2" for="avail_{{ $key }}">Available</label>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-outline-secondary save-day" type="button" data-day="{{ $day }}">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 text-end">
                        <button class="btn btn-primary" id="save-all" type="submit">Save All</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function(){
        const form = document.getElementById('availability-form');
        const alertPlaceholder = document.getElementById('alert-placeholder');
        const csrfToken = form.querySelector('input[name="_token"]').value;

        function showAlert(message, type = 'success'){
            alertPlaceholder.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
        }

        function clearAlert(){ alertPlaceholder.innerHTML = '' }

        async function postDays(payload){
            try{
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ days: payload })
                });

                if (!res.ok) {
                    const data = await res.json().catch(()=>null);
                    throw new Error((data && data.message) ? data.message : 'Save failed');
                }

                // success
                showAlert('Availability saved', 'success');
                setTimeout(clearAlert, 3000);
                return true;
            } catch(err){
                showAlert('Error: ' + err.message, 'danger');
                return false;
            }
        }

        // Save whole form via AJAX
        form.addEventListener('submit', async function(e){
            e.preventDefault();
            const days = {};
            // build payload from inputs
            form.querySelectorAll('[data-day]').forEach(function(el){
                const day = el.getAttribute('data-day');
                if (!days[day]) days[day] = {};
                if (el.classList.contains('day-max')){
                    days[day]['max_appointments'] = parseInt(el.value) || 0;
                }
                if (el.classList.contains('day-available')){
                    days[day]['available'] = el.checked ? 1 : 0;
                }
            });

            // Ensure all days present (defaults)
            @php
                echo "const defaultDays = " . json_encode($days) . ";";
            @endphp

            // Merge with defaultDays to ensure order
            const payload = {};
            Object.keys(defaultDays).forEach(d => payload[d] = days[d] || { available: 0, max_appointments: 0 });

            await postDays(payload);
        });

        // Per-day save (Save button)
        document.querySelectorAll('.save-day').forEach(function(btn){
            btn.addEventListener('click', async function(){
                const day = btn.getAttribute('data-day');
                const availableEl = document.querySelector('.day-available[data-day="'+day+'"]');
                const maxEl = document.querySelector('.day-max[data-day="'+day+'"]');
                const payload = {};
                payload[day] = {
                    available: availableEl.checked ? 1 : 0,
                    max_appointments: parseInt(maxEl.value) || 0
                };
                await postDays(payload);
            });
        });

        // Reflect UI changes when toggling availability
        document.querySelectorAll('.day-available').forEach(function(sw){
            sw.addEventListener('change', function(){
                const day = sw.getAttribute('data-day');
                const card = document.querySelector('[data-day-card="'+day+'"]');
                const pill = document.querySelector('[data-status-pill="'+day+'"]');
                if (!card || !pill) return;
                if (sw.checked) {
                    card.classList.remove('is-off');
                    card.classList.add('is-available');
                    pill.classList.remove('pill-off');
                    pill.classList.add('pill-on');
                    pill.textContent = 'Available';
                } else {
                    card.classList.remove('is-available');
                    card.classList.add('is-off');
                    pill.classList.remove('pill-on');
                    pill.classList.add('pill-off');
                    pill.textContent = 'Off';
                }
            });
        });

        // Autofill defaultDays const in JS
        // Build a JS-friendly days structure from server for ordering
        // We used PHP variable $days earlier; recreate here for defaults
        const serverDays = [
            @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d)
                '{{ $d }}',
            @endforeach
        ];
        // create defaultDays map used in submit
        window.defaultDays = {};
        serverDays.forEach(d => window.defaultDays[d] = { available: 0, max_appointments: 0 });

    })();
</script>
@endpush

@endsection
