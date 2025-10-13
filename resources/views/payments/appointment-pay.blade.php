@extends('layouts.base')

@section('content')
<style>
  .btn-brand{background-color:#FF00F8;border-color:#FF00F8;color:#fff}
  .btn-brand:hover{background-color:#d600d3;border-color:#d600d3;color:#fff}
  .amount-chip{display:inline-block;padding:6px 12px;border-radius:999px;background:linear-gradient(90deg,#FF00F8,rgb(128,0,128));color:#fff;font-weight:700;box-shadow:0 2px 8px rgba(0,0,0,.08)}
  .provider-panel{background:#f8f9fa;border-radius:8px;padding:8px}
  @media (prefers-color-scheme: dark){.provider-panel{background:#1f1f1f}}
</style>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header text-white" style="background-color: black;">
          <strong class="mb-3" style="font-size: 1.2rem;">Pay for Appointment #{{ $appointment->id }}</strong>
        </div>
        <div class="card-body">
          @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
          @endif
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          <div class="row mb-4 text-dark">
            <div class="col-12 col-md-7">
              <dl class="row small mb-0">
                <dt class="col-4">Patient</dt><dd class="col-8">{{ optional($appointment->patient)->name ?? '—' }}</dd>
                <dt class="col-4">Doctor</dt><dd class="col-8">{{ optional($appointment->doctor)->name ? 'Dr. ' . $appointment->doctor->name : '—' }}</dd>
                <dt class="col-4">Time</dt><dd class="col-8">{{ optional($appointment->appointment_time)->format('D, M j, Y g:i A') }}</dd>
                <dt class="col-4">Amount</dt><dd class="col-8">{{ $appointment->amount ? number_format($appointment->amount, 0) . ' UGX' : '—' }}</dd>
                <dt class="col-4">Status</dt><dd class="col-8"><span class="badge bg-warning text-dark">{{ $appointment->status }}</span></dd>
              </dl>
              @if($appointment->amount)
              <div class="mt-2">
                <span class="amount-chip">UGX {{ number_format($appointment->amount, 0) }}</span>
              </div>
              @endif
            </div>
            <div class="col-12 col-md-5 d-flex align-items-start justify-content-md-end mt-3 mt-md-0">
              <div class="provider-panel w-100 d-flex justify-content-md-end justify-content-center" style="background-color: #fcca0a;">
                <img src="{{ asset('images/momo.png') }}" alt="MTN MoMo" class="img-fluid" style="max-width: 200px; height: auto;" loading="lazy">
              </div>
            </div>
          </div>

          <form class="text-dark" action="{{ route('payment.appointment.checkout') }}" method="POST">
            @csrf
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">

            <div class="mb-3">
              <label class="form-label">Payer Phone Number</label>
              <input id="phone_number" type="text" name="phone_number" class="form-control" placeholder="2567XXXXXXXX"
                     inputmode="numeric" maxlength="12" pattern="^2567\d{8}$" aria-describedby="msisdnHelp"
                     value="{{ old('phone_number') }}" required>
              <div id="msisdnHelp" class="form-text">Enter MSISDN in international format without + (e.g. 2567XXXXXXXX)</div>
              <div class="invalid-feedback">Enter a valid number like 2567XXXXXXXX</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Amount (UGX)</label>
              <input type="number" name="amount" min="1" step="1" class="form-control"
                     value="{{ old('amount', $appointment->amount ?? '') }}">
            </div>

            
      <div class="d-flex justify-content-between mb-1">
                <a href="{{ url()->previous() }}" class="btn btn-light">Back</a>
        <button type="submit" class="btn btn-brand">Request Payment</button>
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
      // basic validity check against pattern
      try{
        const re = new RegExp('^2567\\\d{8}$');
        input.classList.toggle('is-invalid', !re.test(input.value));
      }catch(e){}
    });
    input.addEventListener('input', function(){
      // do not aggressively mutate while typing; only strip leading +
      if(input.value.startsWith('+256')){
        input.value = input.value.replace(/^\+/, '');
      }
      // clear invalid as user types
      if(input.classList.contains('is-invalid')){
        input.classList.remove('is-invalid');
      }
    });
  })();
</script>
@endpush

@push('scripts')
<script>
  (function(){
    const form = document.querySelector('form[action*="payment.appointment.checkout"]');
    if(!form) return;
    const btn = form.querySelector('button[type="submit"]');
    form.addEventListener('submit', function(e){
      if(!btn) return;
      btn.disabled = true;
      btn.dataset.originalText = btn.innerHTML;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting…';
    });
  })();
</script>
@endpush
