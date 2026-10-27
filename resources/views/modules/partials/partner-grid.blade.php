{{-- .partner-logo do website_template --}}
<div class="row g-3 justify-content-center">
  @foreach ($items as $partner)
    <div class="col-6 col-md-3 col-lg-2">
      @php $href = $partner->href(); @endphp
      <{{ $href ? 'a' : 'div' }} class="partner-logo"@if ($href) href="{{ $href }}" target="_blank" rel="noopener"@endif title="{{ $partner->name }}">
        @if ($partner->logo)
          <img src="{{ $partner->logo->url('md') }}" alt="{{ $partner->name }}" loading="lazy">
        @else
          {{ $partner->name }}
        @endif
      </{{ $href ? 'a' : 'div' }}>
    </div>
  @endforeach
</div>
