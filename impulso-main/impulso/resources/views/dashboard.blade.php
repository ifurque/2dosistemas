@extends('layouts.app')
@section('content')
<section class="dashboard wrap"><div class="dashboard-top"><div><p class="eyebrow">Panel de {{ $business->name }}</p><h1>Hola, {{ Str::before(auth()->user()->name, ' ') }}.</h1><p class="muted">Aquí tienes una mirada rápida de lo que está pasando.</p></div><a class="button" href="{{ route('business.show', $business) }}">Ver perfil público <span>↗</span></a></div>
<div class="metric-grid"><div class="metric-card accent"><span>Pagos entrantes · 30 días</span><strong>$ {{ number_format($totalIncomes, 0, ',', '.') }}</strong><small>Todo lo que ingresó a tu negocio.</small></div><div class="metric-card"><span>Gastos · 30 días</span><strong>$ {{ number_format($totalExpenses, 0, ',', '.') }}</strong><small>Movimientos registrados</small></div><div class="metric-card"><span>Turnos próximos</span><strong>{{ $appointments }}</strong><small class="positive">● En seguimiento</small></div></div>
<div class="dashboard-grid">
  <div class="dashboard-main">
    <div class="panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Egresos</p>
          <h2>Gastos por categoría</h2>
        </div>
        <a class="text-link" href="{{ route('movements.index', $business) }}">Ver movimientos →</a>
      </div>
      @if($categoryTotals->count())
        <div class="bars">
          @foreach($categoryTotals as $category => $amount)
            <div class="bar-row">
              <div>
                <span>{{ $category }}</span>
                <strong>$ {{ number_format($amount, 0, ',', '.') }}</strong>
              </div>
              <div class="bar-track">
                <i style="width: {{ max(8, ($amount / max(1, $categoryTotals->first())) * 100) }}%"></i>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <p class="muted">Todavía no hay gastos registrados.</p>
      @endif
    </div>

    <div class="panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Ingresos</p>
          <h2>Pagos recibidos</h2>
        </div>
        <a class="text-link" href="{{ route('movements.index', $business) }}">Ver movimientos →</a>
      </div>
      @if($incomeTotals->count())
        <div class="bars">
          @foreach($incomeTotals as $source => $amount)
            <div class="bar-row">
              <div>
                <span>{{ $source === 'service' ? 'Servicios' : ($source === 'sale' ? 'Ventas' : 'Otros') }}</span>
                <strong>$ {{ number_format($amount, 0, ',', '.') }}</strong>
              </div>
              <div class="bar-track income">
                <i style="width: {{ max(8, ($amount / max(1, $incomeTotals->first())) * 100) }}%"></i>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <p class="muted">Todavía no hay pagos entrantes.</p>
      @endif
    </div>
  </div>

  <div class="panel quick-panel">
    <p class="eyebrow">Acciones rápidas</p>
    <h2>Haz avanzar tu día.</h2>
    <div class="quick-links">
      <a href="{{ route('movements.index', $business) }}" class="quick-link">
        <span>↕</span>
        <div>
          <strong>Ver movimientos</strong>
          <small>Gastos e ingresos</small>
        </div>
      </a>
      <a href="{{ route('management.index', $business) }}" class="quick-link">
        <span>◷</span>
        <div>
          <strong>Ver turnos</strong>
          <small>Gestiona citas y consultas</small>
        </div>
      </a>
      <a href="{{ route('posts.index', $business) }}" class="quick-link">
        <span>✦</span>
        <div>
          <strong>Publicaciones</strong>
          <small>Administra contenido</small>
        </div>
      </a>
      <a href="{{ route('management.index', $business) }}" class="quick-link">
        <span>⌁</span>
        <div>
          <strong>Equipo y redes</strong>
          <small>Conexiones y miembros</small>
        </div>
      </a>
      <a href="{{ route('availability.index', $business) }}" class="quick-link">
        <span>🕐</span>
        <div>
          <strong>Horarios</strong>
          <small>Configure días y horarios</small>
        </div>
      </a>
    </div>
  </div>
</div>
</section>
@endsection