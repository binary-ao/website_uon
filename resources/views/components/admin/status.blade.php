@props(['status', 'date' => null])
@php $s = $status instanceof \App\Enums\PublicationStatus ? $status : \App\Enums\PublicationStatus::tryFrom((string) $status); @endphp
@if ($s)
  <span class="s-badge {{ $s->badge() }}" @if ($s === \App\Enums\PublicationStatus::Scheduled && $date) title="Publicação em {{ $date->format('d/m/Y H:i') }}" @endif>{{ $s->label() }}</span>
@endif
