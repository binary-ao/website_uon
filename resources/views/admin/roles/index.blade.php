@extends('layouts.admin')
@section('title', 'Funções e permissões')

@php use App\Support\Permissions; use App\Models\User; @endphp

@section('content')
<x-admin.title title="Funções e permissões" subtitle="As permissões são verificadas no servidor em todas as rotas e acções. O superadministrador tem acesso global." />

<div class="row g-4">
  @foreach ($roles as $role)
    @continue($role->name === User::ROLE_SUPERADMIN)
    @php $has = $role->permissions->pluck('name')->all(); @endphp
    <div class="col-xxl-4 col-lg-6">
      <form method="post" action="{{ route('admin.roles.update', $role) }}" class="u-card h-100">
        @csrf @method('PUT')
        <div class="u-card-title"><i class="bi bi-shield"></i> {{ User::ROLE_LABELS[$role->name] ?? $role->name }}</div>
        <div class="table-responsive">
          <table class="table table-sm perm-table small">
            <thead><tr><th>Módulo</th>@foreach (Permissions::CONTENT_ACTIONS as $a => $l)<th title="{{ $l }}">{{ ['create' => 'Criar', 'edit' => 'Editar', 'publish' => 'Publicar', 'delete' => 'Eliminar'][$a] }}</th>@endforeach</tr></thead>
            <tbody>
              @foreach (Permissions::CONTENT_MODULES as $m => $label)
                <tr><td>{{ $label }}</td>
                  @foreach (array_keys(Permissions::CONTENT_ACTIONS) as $a)
                    <td><input class="form-check-input" type="checkbox" name="permissions[]" value="{{ "$m.$a" }}" @checked(in_array("$m.$a", $has)) aria-label="{{ $label }} — {{ Permissions::CONTENT_ACTIONS[$a] }}"></td>
                  @endforeach
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @foreach (Permissions::GLOBAL as $perm => $label)
          @continue(in_array($perm, Permissions::SUPERADMIN_ONLY, true))
          <div class="form-check small">
            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm }}" id="{{ $role->name }}_{{ $perm }}" @checked(in_array($perm, $has))>
            <label class="form-check-label" for="{{ $role->name }}_{{ $perm }}">{{ $label }}</label>
          </div>
        @endforeach
        <button class="btn btn-uon btn-sm mt-3" type="submit">Guardar permissões</button>
      </form>
    </div>
  @endforeach
</div>
<p class="small text-muted mt-3"><i class="bi bi-info-circle"></i> A gestão de utilizadores e de permissões está reservada ao superadministrador e não pode ser atribuída a outras funções.</p>
@endsection
