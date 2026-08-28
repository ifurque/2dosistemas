@if($business->posts->count())
    <h2>Publicaciones</h2>
    <div class="public-posts">
        @foreach($business->posts as $post)
            <article>
                @if($post->photo)
                    <img class="post-public-photo" src="{{ asset('storage/'.$post->photo) }}" alt="Foto de {{ $post->title }}">
                @endif
                <span class="tag">{{ $post->type === 'offer' ? 'Oferta' : ($post->type === 'product' ? 'Producto' : 'Novedad') }}</span>
                <h3>{{ $post->title }}</h3>
                <p>{{ $post->body }}</p>
                @if($post->price)
                    <strong>$ {{ number_format($post->discount_price ?: $post->price, 0, ',', '.') }}</strong>
                    @if($post->discount_price)
                        <del>$ {{ number_format($post->price, 0, ',', '.') }}</del>
                        <span class="discount-badge">{{ $post->discountPercentage() }}% OFF</span>
                    @endif
                @endif
            </article>
        @endforeach
    </div>
@endif