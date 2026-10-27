@props(['model'])
@php $revisions = $model->exists ? $model->revisions()->with('user:id,name')->limit(15)->get() : collect(); @endphp
@if ($revisions->isNotEmpty())
<div class="u-card">
  <div class="u-card-title"><i class="bi bi-clock-history"></i> Histórico de revisões</div>
  <ul class="d-tl">
    @foreach ($revisions as $rev)
      <li>
        <a href="{{ route('admin.revisions.show', $rev) }}">{{ $rev->actionLabel() }}</a>
        <small>{{ $rev->created_at?->format('d/m/Y H:i') }} · {{ $rev->user?->name ?? 'Sistema' }}</small>
      </li>
    @endforeach
  </ul>
</div>
@endif
