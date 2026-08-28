@forelse($businesses as $business)
  <article class="business-card">
    <div class="business-cover" @if($business->cover_photo) style="background-image: url('{{ asset('storage/'.$business->cover_photo) }}'); background-size: cover; background-position: center;" @endif>
      <span class="logo-placeholder">
        @if($business->profile_photo)
          <img src="{{ asset('storage/'.$business->profile_photo) }}" alt="Logo de {{ $business->name }}">
        @else
          {{ Str::substr($business->name, 0, 1) }}
        @endif
      </span>
      <span class="tag">{{ $business->category }}</span>
    </div>
    <div class="business-body">
      <div class="rating">★ 4.9</div>
      <h2>{{ $business->name }}</h2>
      <p>{{ Str::limit($business->description, 110) }}</p>
      <small>⌖ {{ $business->location }} · {{ $business->products_count }} propuestas</small>
      <a class="text-link" href="{{ route('business.show', $business) }}">Ver emprendimiento <span>→</span></a>
    </div>
  </article>
@empty
  <div class="empty" style="grid-column: 1/-1;">
    <span>✦</span>
    <h2>No encontramos coincidencias.</h2>
    <p>Intenta con otros términos de búsqueda o categorías.</p>
  </div>
@endforelse
