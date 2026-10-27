@extends('layouts.admin')
@section('title', 'Graus académicos')

@section('content')
<x-admin.title title="Graus académicos" subtitle="Graus usados para classificar e filtrar os cursos.">
  <a href="{{ route('admin.degrees.create') }}" class="btn btn-uon"><i class="bi bi-plus-lg"></i> Novo grau</a>
</x-admin.title>
<div class="d-box">
  <div class="table-responsive"><table class="table">
    <thead><tr><th>Grau</th><th>Plural</th><th>Endereço</th><th>Cursos</th><th class="text-end">Acções</th></tr></thead>
    <tbody>
      @forelse ($items as $d)
        <tr>
          <td><a class="row-title" href="{{ route('admin.degrees.edit', $d) }}">{{ $d->name }}</a></td>
          <td><small>{{ $d->plural_name }}</small></td>
          <td><small>{{ $d->slug }}</small></td>
          <td><small>{{ $d->courses_count }}</small></td>
          <td class="text-end text-nowrap"><a class="u-iconbtn" href="{{ route('admin.degrees.edit', $d) }}" aria-label="Editar"><i class="bi bi-pencil"></i></a>
            <x-admin.delete-form :action="route('admin.degrees.destroy', $d)" message="Eliminar o grau &quot;{{ $d->name }}&quot;?" :icon-only="true" /></td>
        </tr>
      @empty
        <tr><td colspan="5"><div class="r-empty"><i class="bi bi-award"></i>Sem graus.</div></td></tr>
      @endforelse
    </tbody>
  </table></div>
</div>
@endsection
