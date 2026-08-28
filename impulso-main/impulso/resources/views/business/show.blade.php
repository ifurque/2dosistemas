@extends('layouts.app')
@section('content')
<section class="profile wrap">
  <a class="back" href="{{ route('discover') }}">← Volver a explorar</a>
  <div class="profile-hero"{{ $business->cover_photo ? ' style="background-image: linear-gradient(90deg, rgba(24,37,34,.82), rgba(24,37,34,.2)), url(\'' . asset('storage/'.$business->cover_photo) . '\'); background-size: cover; background-position: center; color: white;"' : '' }}>
    <div class="profile-logo">
      @if($business->profile_photo)
        <img src="{{ asset('storage/'.$business->profile_photo) }}" alt="Logo de {{ $business->name }}">
      @else
        {{ Str::substr($business->name, 0, 1) }}
      @endif
    </div>
    <div>
      <p class="eyebrow">{{ $business->category }}</p>
      <h1>{{ $business->name }}</h1>
      <p class="muted">⌖ {{ $business->location }} @if($business->phone) · {{ $business->phone }} @endif</p>
    </div>
    <div class="profile-rating">
      <strong>★ 4.9</strong>
      <span>Comunidad</span>
    </div>
  </div>
  <div class="profile-grid">
    <div>
      <h2>Sobre el emprendimiento</h2>
      <p class="large-text">{{ $business->description }}</p>
      @include('business._posts')
      
      <h2>Productos y servicios</h2>
      <div class="product-list">
        @forelse($business->products as $product)
          <div>
            @if($product->photo)
              <img class="product-photo" src="{{ asset('storage/'.$product->photo) }}" alt="Foto de {{ $product->name }}">
            @endif
            <span>{{ $product->type === 'product' ? 'Producto' : 'Servicio' }}</span>
            <strong>{{ $product->name }}</strong>
            <small>
              {{ $product->description }}
              @if($product->price)
                · $ {{ number_format($product->price, 0, ',', '.') }}
              @endif
            </small>
          </div>
        @empty
          <p class="muted">Próximamente encontrarás aquí sus propuestas.</p>
        @endforelse
      </div>

      <h2>Redes y contacto</h2>
      <div class="actions">
        @foreach($business->socialLinks as $link)
          <a class="text-link" target="_blank" href="{{ $link->url }}">{{ ucfirst($link->platform) }} ↗</a>
        @endforeach
      </div>

      <h2>Reseñas <span class="rating">★ {{ number_format($business->reviews->avg('rating') ?: 0, 1) }}</span></h2>
      <div class="review-list">
        @forelse($business->reviews as $review)
          <article>
            <div class="rating">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
            <strong>{{ $review->reviewer_name }}</strong>
            <p>{{ $review->body }}</p>
          </article>
        @empty
          <p class="muted">Sé la primera persona en dejar una reseña.</p>
        @endforelse
      </div>
    </div>

    <aside class="contact-box">
      <h3>Solicitar turno</h3>
      <form method="POST" action="{{ route('appointments.store', $business) }}" class="form">
        @csrf
        @guest
          <label>Nombre<input name="guest_name" required maxlength="100"></label>
          <label>Email para confirmar<input name="guest_email" type="email" required maxlength="180"></label>
        @endguest
        <label>
          Servicio
          <select name="product_id">
            <option value="">Selecciona una propuesta</option>
            @foreach($business->products as $product)
              <option value="{{ $product->id }}">{{ $product->name }}</option>
            @endforeach
          </select>
        </label>
        <label>Fecha<input name="appointment_date" type="date" min="{{ now()->format('Y-m-d') }}" required></label>
        <label>Hora<input name="start_time" type="time" required></label>
        <label>Notas<textarea name="notes" rows="2"></textarea></label>
        <button class="button full">Solicitar turno <span>→</span></button>
        <small class="muted">Te enviaremos un enlace para confirmar el turno por email.</small>
      </form>

      <hr>

      <h3>Dejar una reseña</h3>
      <form method="POST" action="{{ route('reviews.store', $business) }}" class="form">
        @csrf
        <label>Tu nombre<input name="reviewer_name" maxlength="100" required></label>
        <label>
          Calificación
          <select name="rating" required>
            <option value="5">★★★★★ Excelente</option>
            <option value="4">★★★★ Muy buena</option>
            <option value="3">★★★ Buena</option>
            <option value="2">★★ Regular</option>
            <option value="1">★ Necesita mejorar</option>
          </select>
        </label>
        <label>Tu reseña<textarea name="body" rows="4" maxlength="1000" required placeholder="Comparte tu experiencia"></textarea></label>
        <button class="button secondary full">Publicar reseña</button>
      </form>

      <hr>

      <h3>Hacer una consulta</h3>
      @auth
        <form method="POST" action="{{ route('inquiries.store', $business) }}" class="form">
          @csrf
          <label>Asunto<input name="subject" required></label>
          <label>Mensaje<textarea name="message" rows="3" required></textarea></label>
          <button class="button secondary full">Enviar consulta</button>
        </form>
      @else
        <p class="muted">Ingresa para enviar una consulta al emprendimiento.</p>
        <a class="button full" href="{{ route('login') }}">Ingresar</a>
      @endauth
    </aside>
  </div>
</section>
@endsection