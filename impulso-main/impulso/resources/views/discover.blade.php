@extends('layouts.app')
@section('content')
<section class="discover wrap">
  <div class="discover-head">
    <div>
      <p class="eyebrow">La comunidad</p>
      <h1>Ideas que ya están en marcha.</h1>
      <p class="muted">Encuentra personas y servicios cerca de ti.</p>
    </div>
    <div class="search-controls">
      <input 
        type="text" 
        id="search-input" 
        class="search-input" 
        value="{{ request('search') }}" 
        placeholder="Buscar por nombre o descripción"
        autocomplete="off"
      >
      <select id="category-filter" class="category-filter">
        <option value="">Todos los rubros</option>
        @foreach($categories as $category)
          <option value="{{ $category }}" @if(request('category') === $category) selected @endif>
            {{ $category }}
          </option>
        @endforeach
      </select>
    </div>
  </div>

  <div id="businesses-container">
    @if($businesses->count())
      <div class="business-grid" id="business-grid">
        @foreach($businesses as $business)
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
        @endforeach
      </div>
      <div class="pagination" id="pagination">
        {{ $businesses->links() }}
      </div>
    @else
      <div class="empty">
        <span>✦</span>
        <h2>La comunidad está tomando forma.</h2>
        <p>Sé de los primeros en compartir tu emprendimiento.</p>
        @auth
          <a class="button" href="{{ route('business.create') }}">Crear emprendimiento</a>
        @else
          <a class="button" href="{{ route('register') }}">Crear cuenta</a>
        @endauth
      </div>
    @endif
  </div>
</section>

<script>
  const searchInput = document.getElementById('search-input');
  const categoryFilter = document.getElementById('category-filter');
  const businessesContainer = document.getElementById('businesses-container');
  let searchTimeout;

  function performSearch() {
    const search = searchInput.value;
    const category = categoryFilter.value;
    const params = new URLSearchParams();
    
    if (search) params.append('search', search);
    if (category) params.append('category', category);

    fetch(`/explorar?${params}&ajax=1`)
      .then(response => response.json())
      .then(data => {
        businessesContainer.innerHTML = `<div class="business-grid" id="business-grid">${data.html}</div><div class="pagination" id="pagination">${data.pagination}</div>`;
      })
      .catch(error => console.error('Error:', error));
  }

  searchInput.addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(performSearch, 300);
  });

  categoryFilter.addEventListener('change', performSearch);
</script>
@endsection