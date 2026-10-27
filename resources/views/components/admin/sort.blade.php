@props(['field', 'label'])
@php
    $active = request('sort', 'updated_at') === $field;
    $dir = $active && request('dir') !== 'asc' ? 'asc' : 'desc';
@endphp
<a class="sort" href="{{ request()->fullUrlWithQuery(['sort' => $field, 'dir' => $dir, 'page' => null]) }}">{{ $label }}@if ($active) <i class="bi {{ request('dir') === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>@endif</a>
