@php $partners = \App\Models\Partner::active()->with('logo')->get(); @endphp
@if ($partners->isNotEmpty())
  @if (! empty($block['data']['title']))<h2 class="section-title text-center mb-4">{{ $block['data']['title'] }}</h2>@endif
  @include('modules.partials.partner-grid', ['items' => $partners])
@endif
