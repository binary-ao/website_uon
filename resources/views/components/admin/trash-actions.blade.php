@props(['model', 'prefix'])
@can('restore', $model)
  <form method="post" action="{{ route($prefix.'.restore', $model->id) }}" class="d-inline">@csrf
    <button type="submit" class="u-iconbtn" title="Restaurar" aria-label="Restaurar"><i class="bi bi-arrow-counterclockwise"></i></button>
  </form>
@endcan
@can('forceDelete', $model)
  <x-admin.delete-form :action="route($prefix.'.force-delete', $model->id)" message="Eliminar definitivamente? Esta acção não pode ser anulada." label="Eliminar definitivamente" icon="bi-x-octagon" :icon-only="true" />
@endcan
