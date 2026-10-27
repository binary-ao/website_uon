@props(['name', 'label' => 'Imagem', 'media' => null, 'kind' => 'image', 'help' => null, 'required' => false])
@php
    $id = 'mf_'.str_replace(['[', ']'], '_', $name);
    $current = old($name, $media?->id);
    $m = $current && (! $media || (int) $current !== $media->id) ? \App\Models\Media::find($current) : $media;
@endphp
<div class="mb-3" data-media-field data-kind="{{ $kind }}">
  <span class="form-label d-block{{ $required ? ' req' : '' }}" id="{{ $id }}_label">{{ $label }}</span>
  <div class="media-field">
    <div class="mf-preview" data-mf-preview>
      @if ($m && $m->isImage())
        <img src="{{ $m->url('md') }}" alt="">
      @elseif ($m)
        <i class="bi {{ $m->icon() }}"></i>
      @else
        <i class="bi bi-image"></i>
      @endif
    </div>
    <div class="flex-grow-1">
      <input type="hidden" name="{{ $name }}" value="{{ $m?->id }}" data-mf-input>
      <div class="mf-name mb-2" data-mf-name>{{ $m ? ($m->title ?: $m->original_name) : 'Nenhum ficheiro seleccionado' }}</div>
      <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-sm btn-u-light" data-mf-choose aria-describedby="{{ $id }}_label"><i class="bi bi-folder2-open"></i> Escolher</button>
        <button type="button" class="btn btn-sm btn-u-light{{ $m ? '' : ' d-none' }}" data-mf-clear><i class="bi bi-x-lg"></i> Remover</button>
      </div>
    </div>
  </div>
  @if ($help)<div class="form-text">{{ $help }}</div>@endif
  @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
