@props(['action', 'message' => 'Tem a certeza?', 'label' => 'Eliminar', 'icon' => 'bi-trash', 'method' => 'DELETE', 'btn' => 'btn btn-sm btn-outline-danger', 'iconOnly' => false])
<form method="post" action="{{ $action }}" class="d-inline" data-confirm="{{ $message }}">
  @csrf
  @if ($method !== 'POST')@method($method)@endif
  <button type="submit" class="{{ $iconOnly ? 'u-iconbtn danger' : $btn }}" @if ($iconOnly) title="{{ $label }}" aria-label="{{ $label }}" @endif><i class="bi {{ $icon }}"></i>@unless ($iconOnly) {{ $label }}@endunless</button>
</form>
