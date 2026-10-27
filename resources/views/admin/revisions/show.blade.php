@extends('layouts.admin')
@section('title', 'Revisão')

@php
    $labels = ['title' => 'Título', 'name' => 'Nome', 'slug' => 'Endereço', 'excerpt' => 'Resumo', 'summary' => 'Resumo', 'body' => 'Conteúdo', 'blocks' => 'Blocos', 'status' => 'Estado', 'published_at' => 'Data de publicação', 'meta_title' => 'Título SEO', 'meta_description' => 'Meta description', 'layout' => 'Layout', 'eyebrow' => 'Sobretítulo', 'presentation' => 'Apresentação', 'objectives' => 'Objectivos'];
    $fmt = fn ($v) => is_array($v) ? json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string) $v;
@endphp

@section('content')
<x-admin.title title="Revisão: {{ $revision->actionLabel() }}" subtitle="{{ $revision->created_at?->format('d/m/Y H:i') }} · {{ $revision->user?->name ?? 'Sistema' }}">
  <a class="btn btn-u-light" href="{{ $backUrl }}"><i class="bi bi-arrow-left"></i> Voltar ao conteúdo</a>
  @can('update', $model)
    <form method="post" action="{{ route('admin.revisions.restore', $revision) }}" data-confirm="Repor os campos editoriais desta revisão? O estado de publicação não é alterado.">@csrf
      <button class="btn btn-uon"><i class="bi bi-arrow-counterclockwise"></i> Repor esta revisão</button>
    </form>
  @endcan
</x-admin.title>

<div class="d-box">
  <div class="table-responsive"><table class="table">
    <thead><tr><th style="width:16%">Campo</th><th style="width:42%">Nesta revisão</th><th style="width:42%">Versão actual</th></tr></thead>
    <tbody>
      @foreach ($revision->data as $field => $value)
        @php $changed = $fmt($value) !== $fmt($current[$field] ?? null); @endphp
        <tr>
          <td><small><strong>{{ $labels[$field] ?? $field }}</strong>@if ($changed)<br><span class="u-badge red">Diferente</span>@endif</small></td>
          <td class="{{ $changed ? 'diff-old' : '' }}"><div class="diff-cell">{{ \Illuminate\Support\Str::limit($fmt($value), 3000) }}</div></td>
          <td class="{{ $changed ? 'diff-new' : '' }}"><div class="diff-cell">{{ \Illuminate\Support\Str::limit($fmt($current[$field] ?? null), 3000) }}</div></td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
</div>
@endsection
