@extends('layouts.app')

@section('content')
<section class="dashboard wrap">
  <a class="back" href="{{ route('dashboard', $business) }}">← Volver al panel</a>

  <div class="dashboard-top">
    <div>
      <p class="eyebrow">Administración</p>
      <h1>Todo tu negocio, aquí.</h1>
      <p class="muted">Gestiona propuestas, stock, turnos, consultas, equipo y presencia digital.</p>
    </div>
  </div>

  <div class="dashboard-grid">
    <div class="panel">
      <p class="eyebrow">Catálogo</p>
      <h2>Agregar producto o servicio</h2>

      <form method="POST" enctype="multipart/form-data" action="{{ route('products.store', $business) }}" class="form">
        @csrf
        <label>Nombre<input name="name" required placeholder="Ej. Corte de cabello"></label>
        <label>Tipo
          <select name="type">
            <option value="service">Servicio</option>
            <option value="product">Producto</option>
          </select>
        </label>
        <label>Precio<input type="number" name="price" min="0" step="0.01" placeholder="Opcional"></label>
        <label>Stock inicial <span class="label-hint">Solo se descuenta en productos vendidos</span><input type="number" name="stock" min="0" step="1" value="0"></label>
        <label>Foto del producto <span class="label-hint">JPG, PNG o WEBP · máx. 5 MB</span><input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
        <label>Descripción<textarea name="description" rows="3"></textarea></label>
        <button class="button">Agregar al perfil <span>→</span></button>
      </form>

      <div class="product-list management-list">
        @forelse($business->products as $product)
          <div class="product-admin-row">
            <div class="product-admin-main">
              @if($product->photo)
                <img class="product-thumb" src="{{ asset('storage/'.$product->photo) }}" alt="Foto de {{ $product->name }}">
              @endif
              <div>
                <strong>{{ $product->name }}</strong>
                <small>{{ $product->type === 'product' ? 'Producto' : 'Servicio' }}</small>
                <small>Último precio: $ {{ number_format($product->price ?? 0, 0, ',', '.') }}</small>
                <small>Stock actual: {{ $product->stock ?? 0 }}</small>
              </div>
            </div>

            <form method="POST" action="{{ route('products.stock', [$business, $product]) }}" class="stock-form">
              @csrf
              <label>Precio<input type="number" name="price" min="0" step="0.01" value="{{ $product->price ?? 0 }}"></label>
              <label>Stock<input type="number" name="stock" min="0" step="1" value="{{ $product->stock ?? 0 }}"></label>
              <button class="plain-button" type="submit">Guardar cambios</button>
            </form>

            <form method="POST" action="{{ route('products.destroy', [$business, $product->id]) }}">
              @csrf
              @method('DELETE')
              <button class="plain-button">Eliminar</button>
            </form>
          </div>
        @empty
          <p class="muted">Todavía no hay productos ni servicios cargados.</p>
        @endforelse
      </div>
    </div>

    <div class="panel">
      <p class="eyebrow">Equipo</p>
      <h2>Sumar un miembro</h2>

      <form method="POST" action="{{ route('members.store', $business) }}" class="form">
        @csrf
        <label>Correo de usuario<input name="email" type="email" required placeholder="persona@correo.com"></label>
        <label>Rol
          <select name="role">
            <option value="administrator">Administrador</option>
            <option value="employee">Empleado</option>
          </select>
        </label>
        <button class="button">Asociar miembro <span>→</span></button>
      </form>

      <div class="member-list">
        @foreach($business->members as $member)
          <div>
            <strong>{{ $member->name }}</strong>
            <small>{{ $member->email }} · {{ ucfirst($member->pivot->role) }}</small>
          </div>
        @endforeach
      </div>

      <hr>

      <p class="eyebrow">Redes sociales</p>
      <h2>Tu presencia online</h2>
      <form method="POST" action="{{ route('social.store', $business) }}" class="form">
        @csrf
        <label>Red
          <select name="platform">
            <option value="instagram">Instagram</option>
            <option value="facebook">Facebook</option>
            <option value="whatsapp">WhatsApp</option>
            <option value="tiktok">TikTok</option>
          </select>
        </label>
        <label>Enlace<input name="url" type="url" required placeholder="https://..."></label>
        <button class="button secondary">Guardar red</button>
      </form>
    </div>
  </div>

  <div class="panel recent">
    <div class="panel-head">
      <div>
        <p class="eyebrow">Atención</p>
        <h2>Turnos recientes</h2>
      </div>
    </div>
    <div class="expense-list">
      @forelse($appointments as $appointment)
        <div>
          <span>
            <strong>{{ $appointment->client->name }} @if($appointment->product) · {{ $appointment->product->name }} @endif</strong>
            <small>{{ $appointment->appointment_date->format('d/m/Y') }} a las {{ $appointment->start_time }} · {{ ucfirst($appointment->status) }}</small>
          </span>
          <form method="POST" action="{{ route('appointments.status', [$business, $appointment->id]) }}">
            @csrf
            <select name="status" onchange="this.form.submit()">
              <option value="pending" @selected($appointment->status === 'pending')>Pendiente</option>
              <option value="confirmed" @selected($appointment->status === 'confirmed')>Confirmado</option>
              <option value="completed" @selected($appointment->status === 'completed')>Completado</option>
              <option value="cancelled" @selected($appointment->status === 'cancelled')>Cancelado</option>
            </select>
          </form>
        </div>
      @empty
        <p class="muted">Todavía no hay turnos solicitados.</p>
      @endforelse
    </div>
  </div>

  <div class="panel recent">
    <div class="panel-head">
      <div>
        <p class="eyebrow">Bandeja</p>
        <h2>Consultas recibidas</h2>
      </div>
    </div>
    <div class="expense-list">
      @forelse($inquiries as $inquiry)
        <div>
          <span>
            <strong>{{ $inquiry->subject }}</strong>
            <small>{{ $inquiry->client->name }} · {{ $inquiry->message }}</small>
          </span>
          <form method="POST" action="{{ route('inquiries.status', [$business, $inquiry->id]) }}">
            @csrf
            <select name="status" onchange="this.form.submit()">
              <option value="pending" @selected($inquiry->status === 'pending')>Pendiente</option>
              <option value="answered" @selected($inquiry->status === 'answered')>Respondida</option>
              <option value="closed" @selected($inquiry->status === 'closed')>Cerrada</option>
            </select>
          </form>
        </div>
      @empty
        <p class="muted">No hay consultas pendientes.</p>
      @endforelse
    </div>
  </div>

  <div class="panel recent">
    <div class="panel-head">
      <div>
        <p class="eyebrow">Stock</p>
        <h2>Historial de movimientos</h2>
      </div>
    </div>
    <div class="expense-list">
      @forelse($stockMovements as $movement)
        <div>
          <span class="expense-icon">{{ $movement->quantity_change > 0 ? '+' : '−' }}</span>
          <span>
            <strong>{{ $movement->product->name }}</strong>
            <small>{{ ucfirst(str_replace('_', ' ', $movement->type)) }} · {{ $movement->created_at->format('d/m/Y H:i') }} · {{ $movement->creator->name }}</small>
            <small>Antes: {{ $movement->stock_before }} · Después: {{ $movement->stock_after }} · Cambio: {{ $movement->quantity_change }}</small>
            @if($movement->note)
              <small>{{ $movement->note }}</small>
            @endif
          </span>
        </div>
      @empty
        <p class="muted">Todavía no hay movimientos de stock registrados.</p>
      @endforelse
    </div>
  </div>
</section>
@endsection
