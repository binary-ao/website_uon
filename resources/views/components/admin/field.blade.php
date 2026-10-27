@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'help' => null,
    'required' => false,
    'options' => [],
    'placeholder' => null,
    'rows' => 3,
    'empty' => null,
    'col' => null,
    'id' => null,
])
@php
    $id = $id ?? 'f_'.str_replace(['[', ']', '.'], '_', $name);
    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $current = old($dotName, $value instanceof \BackedEnum ? $value->value : $value);
    if ($type === 'datetime-local' && $current instanceof \DateTimeInterface) { $current = $current->format('Y-m-d\TH:i'); }
    if ($type === 'date' && $current instanceof \DateTimeInterface) { $current = $current->format('Y-m-d'); }
    $invalid = $errors->has($dotName) ? ' is-invalid' : '';
    $describedBy = trim(($help ? $id.'_help ' : '').($invalid ? $id.'_err' : ''));
@endphp
<div {{ $attributes->merge(['class' => $col ?? 'mb-3']) }}>
  @if ($type === 'checkbox')
    <div class="form-check form-switch">
      <input type="hidden" name="{{ $name }}" value="0">
      <input class="form-check-input{{ $invalid }}" type="checkbox" role="switch" id="{{ $id }}" name="{{ $name }}" value="1" @checked((bool) $current)@if ($describedBy) aria-describedby="{{ $describedBy }}"@endif>
      <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
    </div>
  @else
    @if ($label)<label for="{{ $id }}" class="form-label{{ $required ? ' req' : '' }}">{{ $label }}</label>@endif
    @if ($type === 'textarea')
      <textarea class="form-control{{ $invalid }}" id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @if ($placeholder) placeholder="{{ $placeholder }}" @endif @required($required)@if ($describedBy) aria-describedby="{{ $describedBy }}"@endif>{{ $current }}</textarea>
    @elseif ($type === 'select')
      <select class="form-select{{ $invalid }}" id="{{ $id }}" name="{{ $name }}" @required($required)@if ($describedBy) aria-describedby="{{ $describedBy }}"@endif>
        @if ($empty !== null)<option value="">{{ $empty }}</option>@endif
        @foreach ($options as $optValue => $optLabel)
          <option value="{{ $optValue }}" @selected((string) $current === (string) $optValue)>{{ $optLabel }}</option>
        @endforeach
      </select>
    @else
      <input type="{{ $type }}" class="form-control{{ $invalid }}" id="{{ $id }}" name="{{ $name }}" value="{{ $current }}" @if ($placeholder) placeholder="{{ $placeholder }}" @endif @required($required)@if ($describedBy) aria-describedby="{{ $describedBy }}"@endif>
    @endif
  @endif
  @if ($help)<div class="form-text" id="{{ $id }}_help">{{ $help }}</div>@endif
  @error($dotName)<div class="invalid-feedback d-block" id="{{ $id }}_err">{{ $message }}</div>@enderror
</div>
