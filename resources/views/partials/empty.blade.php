<div class="empty-state{{ ! empty($boxed) ? ' empty-state-boxed' : '' }}">
  <i class="bi {{ $icon ?? 'bi-inbox' }}" aria-hidden="true"></i>
  <h3 class="h5">{{ $title ?? 'Sem conteúdos' }}</h3>
  @if (! empty($text))<p>{{ $text }}</p>@endif
</div>
