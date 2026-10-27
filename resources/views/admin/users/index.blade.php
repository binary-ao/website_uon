@extends('layouts.admin')
@section('title', 'Utilizadores')

@section('content')
<x-admin.title title="Utilizadores" subtitle="Contas com acesso ao CMS.">
  <a href="{{ route('admin.users.create') }}" class="btn btn-uon"><i class="bi bi-person-plus"></i> Novo utilizador</a>
</x-admin.title>
<form method="get" class="adm-filters d-flex flex-wrap gap-2" role="search">
  <input type="search" class="form-control w-auto" name="q" value="{{ request('q') }}" placeholder="Nome ou email" aria-label="Pesquisar">
  <select name="role" class="form-select w-auto" aria-label="Função"><option value="">Todas as funções</option>@foreach ($roles as $k => $l)<option value="{{ $k }}" @selected(request('role') === $k)>{{ $l }}</option>@endforeach</select>
  <select name="state" class="form-select w-auto" aria-label="Estado"><option value="">Todos</option><option value="active" @selected(request('state') === 'active')>Activos</option><option value="inactive" @selected(request('state') === 'inactive')>Inactivos</option></select>
  <button class="btn btn-u-dark"><i class="bi bi-funnel"></i> Filtrar</button>
</form>
<div class="d-box">
  @if ($items->isEmpty())
    <div class="r-empty"><i class="bi bi-people"></i>Sem utilizadores.</div>
  @else
  <div class="table-responsive"><table class="table">
    <thead><tr><th>Nome</th><th>Função</th><th>Estado</th><th>Último acesso</th><th class="text-end">Acções</th></tr></thead>
    <tbody>
      @foreach ($items as $usr)
        <tr>
          <td><span class="d-flex align-items-center gap-2"><span class="u-avatar" aria-hidden="true">{{ $usr->initials() }}</span><span><a class="row-title" href="{{ route('admin.users.edit', $usr) }}">{{ $usr->name }}</a><small>{{ $usr->email }}</small></span></span></td>
          <td><small>{{ $usr->roleLabel() }}</small></td>
          <td>@if ($usr->is_active)<span class="s-badge ok">Activo</span>@else<span class="s-badge off">Inactivo</span>@endif</td>
          <td><small>{{ $usr->last_login_at?->format('d/m/Y H:i') ?? 'Nunca' }}</small></td>
          <td class="text-end text-nowrap">
            @if (! $usr->isSuperAdmin() || auth()->user()->isSuperAdmin())
              <a class="u-iconbtn" href="{{ route('admin.users.edit', $usr) }}" aria-label="Editar"><i class="bi bi-pencil"></i></a>
              @unless ($usr->is(auth()->user()))
                <x-admin.delete-form :action="route('admin.users.destroy', $usr)" message="Eliminar a conta de {{ $usr->name }}? Os conteúdos criados são preservados." :icon-only="true" />
              @endunless
            @endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  @endif
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
