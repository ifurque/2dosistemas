@extends('layouts.app')
@section('content')
<section class="dashboard wrap">
  <div class="dashboard-top">
    <div>
      <p class="eyebrow">Panel de {{ $business->name }}</p>
      <h1>Horarios de atención</h1>
      <p class="muted">Configura cuándo estás disponible para atender turnos.</p>
    </div>
    <a class="button" href="{{ route('dashboard', $business) }}">← Volver al panel</a>
  </div>

  <div class="panel" style="max-width: 800px;">
    <form method="POST" action="{{ route('availability.update', $business) }}" class="form">
      @csrf
      
      <div class="form-section">
        <label class="check">
          <input type="hidden" name="appointments_enabled" value="0">
          <input type="checkbox" name="appointments_enabled" value="1" @if($business->appointments_enabled) checked @endif>
          <span>Habilitar turnos y citas para este emprendimiento</span>
        </label>
        <p class="muted" style="font-size: 0.85rem; margin-top: 8px;">Los clientes podrán solicitar turnos cuando esta opción esté habilitada.</p>
      </div>

      <hr>

      <h3 style="margin: 20px 0 16px;">Días y horarios de atención</h3>

      @foreach($days as $dayKey => $dayLabel)
        <div class="hours-section">
          <div class="hours-header">
            <label class="day-label">
              <strong>{{ $dayLabel }}</strong>
            </label>
            <label class="check closed-check">
              <input type="checkbox" name="hours[{{ $loop->index }}][is_closed]" value="1" @if($availability[$dayKey]->is_closed) checked @endif>
              <span>Cerrado</span>
            </label>
          </div>

          <input type="hidden" name="hours[{{ $loop->index }}][day_of_week]" value="{{ $dayKey }}">

          <div class="hours-inputs">
            <div class="time-input">
              <label>Desde</label>
              <input 
                type="time" 
                name="hours[{{ $loop->index }}][opening_time]" 
                value="{{ $availability[$dayKey]->opening_time ?? '09:00' }}"
                @if($availability[$dayKey]->is_closed) disabled @endif
              >
            </div>
            <div class="time-input">
              <label>Hasta</label>
              <input 
                type="time" 
                name="hours[{{ $loop->index }}][closing_time]" 
                value="{{ $availability[$dayKey]->closing_time ?? '18:00' }}"
                @if($availability[$dayKey]->is_closed) disabled @endif
              >
            </div>
          </div>
        </div>
      @endforeach

      <hr>

      <button class="button full" style="margin-top: 24px;">Guardar cambios</button>
    </form>
  </div>
</section>

<style>
  .form-section { margin-bottom: 20px; }
  .check { display: flex !important; align-items: center; font-size: 0.9rem !important; font-weight: 400 !important; gap: 10px; }
  .check input { width: auto; }
  .hours-section { margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--line); }
  .hours-header { display: grid; grid-template-columns: 1fr auto; align-items: center; gap: 15px; margin-bottom: 12px; }
  .day-label { font-weight: 600; margin: 0; }
  .closed-check { margin: 0; }
  .hours-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  .time-input { display: grid; gap: 8px; }
  .time-input label { font-size: 0.82rem; font-weight: 600; }
  .time-input input { border: 1px solid var(--line); padding: 10px; border-radius: 4px; }
  .time-input input:disabled { background: #f0f0f0; color: #999; }
  h3 { margin: 0 0 16px; font-size: 1.1rem; }
</style>

<script>
  document.querySelectorAll('.closed-check input').forEach((checkbox, index) => {
    const section = checkbox.closest('.hours-section');
    const timeInputs = section.querySelectorAll('.time-input input');
    
    checkbox.addEventListener('change', () => {
      timeInputs.forEach(input => {
        input.disabled = checkbox.checked;
      });
    });
  });
</script>
@endsection
