@extends('layouts.base')

@section('content')
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
          <strong>Pay for Appointment #{{ $appointment->id }}</strong>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-center align-items-center mb-3">
            <img src="{{ asset('images/momo.png') }}" alt="MTN MoMo" style="height: 40px; width: auto;" loading="lazy">
          </div>
          @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
          @endif
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          <dl class="row small mb-4 text-dark">
            <dt class="col-4">Patient</dt><dd class="col-8">{{ optional($appointment->patient)->name ?? optional($appointment->student)->name ?? '—' }}</dd>
            <dt class="col-4">Doctor</dt><dd class="col-8">{{ optional($appointment->doctor)->name ? 'Dr. ' . $appointment->doctor->name : '—' }}</dd>
            <dt class="col-4">Time</dt><dd class="col-8">{{ optional($appointment->appointment_time)->format('D, M j, Y g:i A') }}</dd>
            <dt class="col-4">Amount</dt><dd class="col-8">{{ $appointment->amount ? number_format($appointment->amount, 0) . ' UGX' : '—' }}</dd>
            <dt class="col-4">Status</dt><dd class="col-8"><span class="badge bg-warning text-dark">{{ $appointment->status }}</span></dd>
          </dl>

          <form class="text-dark" action="{{ route('payment.appointment.checkout') }}" method="POST">
            @csrf
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">

            <div class="mb-3">
              <label class="form-label">Payer Phone Number</label>
              <input id="phone_number" type="text" name="phone_number" class="form-control" placeholder="2567XXXXXXXX"
                     value="{{ old('phone_number') }}" required>
              <div class="form-text">Enter MSISDN in international format without + (e.g. 2567XXXXXXXX)</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Amount (UGX)</label>
              <input type="number" name="amount" min="1" step="1" class="form-control"
                     value="{{ old('amount', $appointment->amount ?? '') }}">
            </div>

            <div class="d-flex justify-content-between">
              <a href="{{ url()->previous() }}" class="btn btn-light">Back</a>
              <button type="submit" class="btn btn-primary">Request Payment</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function(){
    const input = document.getElementById('phone_number');
    if(!input) return;
    function normalize(val){
      // remove non-digits
      let digits = (val || '').replace(/\D+/g, '');
      if(digits.startsWith('07')){
        // 07XXXXXXXX -> 2567XXXXXXXX
        digits = '256' + digits.substring(1);
      } else if(digits.startsWith('2560')){
        // 2560XXXXXXXX -> 2567XXXXXXXX
        digits = '256' + digits.substring(3);
      } else if(digits.startsWith('0') && digits.length >= 9){
        // 0XXXXXXXXX -> 256XXXXXXXXX
        digits = '256' + digits.substring(1);
      } else if(digits.startsWith('256+')){
        digits = '256' + digits.substring(4);
      }
      return digits;
    }
    input.addEventListener('blur', function(){
      input.value = normalize(input.value);
    });
    input.addEventListener('input', function(){
      // do not aggressively mutate while typing; only strip leading +
      if(input.value.startsWith('+256')){
        input.value = input.value.replace(/^\+/, '');
      }
    });
  })();
</script>
@endpush
