@extends('layouts.admin')
@section('title', 'Banner da página inicial')

@section('content')
<x-admin.title title="Banner da página inicial" subtitle="Diapositivos do carrossel no topo da página inicial. A ordem define a sequência de apresentação.">
  <a href="{{ route('admin.hero-slides.create') }}" class="btn btn-uon"><i class="bi bi-plus-lg"></i> Novo diapositivo</a>
  <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-u-light"><i class="bi bi-box-arrow-up-right"></i> Ver página inicial</a>
</x-admin.title>

<div class="d-box">
  @if ($items->isEmpty())
    <div class="r-empty"><i class="bi bi-images"></i>Sem diapositivos. Enquanto não existirem diapositivos activos, a página inicial apresenta o banner definido em Configurações → Página inicial.</div>
  @else
  <div class="table-responsive"><table class="table">
    <thead><tr><th style="width:8rem">Imagem</th><th>Diapositivo</th><th>Botões</th><th>Período</th><th>Estado</th><th class="text-end">Acções</th></tr></thead>
    <tbody>
      @foreach ($items as $slide)
        <tr>
          <td>@if ($slide->media)<img src="{{ $slide->media->url('md') }}" alt="" style="width:7rem;height:3.6rem;object-fit:cover;border-radius:8px">@endif</td>
          <td>
            @if ($slide->eyebrow)<small class="text-uppercase">{{ $slide->eyebrow }}</small>@endif
            <a class="row-title" href="{{ route('admin.hero-slides.edit', $slide) }}">{{ $slide->title }} {{ $slide->title_highlight }}</a>
            @if ($slide->is_demo)<span class="u-badge demo ms-1">Exemplo</span>@endif
          </td>
          <td><small>{{ collect([$slide->primary_label, $slide->secondary_label])->filter()->implode(' · ') ?: '—' }}</small></td>
          <td><small>{{ $slide->starts_at?->format('d/m/Y') ?? 'Sempre' }} → {{ $slide->ends_at?->format('d/m/Y') ?? 'sem fim' }}</small></td>
          <td>
            @if ($slide->isCurrent())<span class="s-badge ok">Visível</span>
            @elseif ($slide->is_active)<span class="s-badge info">Fora do período</span>
            @else<span class="s-badge off">Inactivo</span>@endif
          </td>
          <td class="text-end text-nowrap">
            <form method="post" action="{{ route('admin.hero-slides.move', $slide) }}" class="d-inline">@csrf<input type="hidden" name="direction" value="up"><button class="u-iconbtn" title="Subir" aria-label="Subir {{ $slide->title }}" @disabled($loop->first)><i class="bi bi-arrow-up"></i></button></form>
            <form method="post" action="{{ route('admin.hero-slides.move', $slide) }}" class="d-inline">@csrf<input type="hidden" name="direction" value="down"><button class="u-iconbtn" title="Descer" aria-label="Descer {{ $slide->title }}" @disabled($loop->last)><i class="bi bi-arrow-down"></i></button></form>
            <a class="u-iconbtn" href="{{ route('admin.hero-slides.edit', $slide) }}" aria-label="Editar"><i class="bi bi-pencil"></i></a>
            <x-admin.delete-form :action="route('admin.hero-slides.destroy', $slide)" message="Eliminar este diapositivo?" :icon-only="true" />
          </td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  @endif
</div>
@endsection
