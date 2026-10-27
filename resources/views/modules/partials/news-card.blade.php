{{-- .card-news do website_template (normal / featured / compacto) --}}
<article class="card-flat card-news{{ ! empty($featured) ? ' featured' : '' }} h-100">
  @include('partials.media', ['media' => $item->featuredMedia, 'ratio' => '4/3', 'icon' => 'bi-newspaper'])
  <div class="card-body">
    <div>
      @if ($item->category)<span class="card-category">{{ $item->category->name }}</span>@endif
      <span class="card-date{{ $item->category ? ' ms-2' : '' }}"><time datetime="{{ $item->published_at->toDateString() }}">{{ $item->published_at->translatedFormat('d M Y') }}</time></span>
      @if ($item->is_demo && setting('show_demo_badges')) <span class="demo-badge ms-1">Exemplo</span>@endif
    </div>
    <h3><a href="{{ $item->url() }}" class="stretched-link text-decoration-none text-reset">{{ $item->title }}</a></h3>
    @if (empty($compact) && $item->excerpt)
      <p>{{ \Illuminate\Support\Str::limit($item->excerpt, 180) }}</p>
    @endif
  </div>
</article>
