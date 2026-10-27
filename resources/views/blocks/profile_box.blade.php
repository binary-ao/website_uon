{{-- .profile-box (sobre.html / curso-detalhe.html) --}}
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="profile-box">
      @if (! empty($block['data']['title']))<h3 class="h5 mb-2">{{ $block['data']['title'] }}</h3>@endif
      <p class="mb-0 text-muted-brand">{!! nl2br(e($block['data']['text'] ?? '')) !!}</p>
    </div>
  </div>
</div>
