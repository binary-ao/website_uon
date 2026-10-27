@extends('layouts.admin')
@section('title', 'Configurações — '.$groups[$group]['label'])

@section('content')
<x-admin.title title="Header, Footer e configurações" subtitle="Conteúdos dos componentes originais do template. A estrutura visual do Header e do Footer não é alterável no CMS; as cores institucionais estão centralizadas em css/variables.css." />

<div class="adm-tabs">
  @foreach ($groups as $key => $def)
    <a href="{{ route('admin.settings.edit', $key) }}" class="{{ $key === $group ? 'active' : '' }}"><i class="bi {{ $def['icon'] }}"></i> {{ $def['label'] }}</a>
  @endforeach
</div>

<form method="post" action="{{ route('admin.settings.update', $group) }}" class="u-form" data-dirty-check>
  @csrf @method('PUT')
  <div class="u-card">
    <div class="u-card-title"><i class="bi {{ $groups[$group]['icon'] }}"></i> {{ $groups[$group]['label'] }}</div>
    @if (in_array($group, ['contacts', 'social', 'external'], true))
      <div class="alert alert-warning small"><i class="bi bi-exclamation-triangle"></i> Introduza apenas informação oficial confirmada pela UON. Os campos vazios não são apresentados no website.</div>
    @endif
    <div class="row">
      @foreach ($groups[$group]['fields'] as $key => $f)
        @php $val = $values[$key] ?? ''; @endphp
        @if ($f['type'] === 'media')
          <div class="col-md-6">
            <x-admin.media-field :name="$key" :label="$f['label']" :media="$media[(int) $val] ?? null" :help="$f['help'] ?? null" />
          </div>
        @elseif ($f['type'] === 'bool')
          <x-admin.field col="col-12 mb-3" :name="$key" :label="$f['label']" type="checkbox" :value="$val === '1' || $val === 1 || $val === true" :help="$f['help'] ?? null" />
        @elseif (in_array($f['type'], ['textarea', 'lines'], true))
          <x-admin.field col="col-12 mb-3" :name="$key" :label="$f['label']" type="textarea" :rows="$f['type'] === 'lines' ? 5 : 3" :value="$val" :help="$f['help'] ?? null" :required="! empty($f['required'])" />
        @else
          <x-admin.field col="col-md-6 mb-3" :name="$key" :label="$f['label']" :type="$f['type'] === 'email' ? 'email' : 'text'" :value="$val" :help="$f['help'] ?? null" :required="! empty($f['required'])" />
        @endif
      @endforeach
    </div>
  </div>
  <div class="cf-actions">
    <button type="submit" class="btn btn-uon"><i class="bi bi-check2"></i> Guardar configurações</button>
    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-u-light"><i class="bi bi-box-arrow-up-right"></i> Ver website</a>
  </div>
</form>
@endsection
