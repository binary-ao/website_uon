@php
    $siteName = setting('site_name');
    $sep = setting('seo_title_separator', '—');
    $pageTitle = trim($__env->yieldContent('title'));
    $fullTitle = $pageTitle !== '' ? $pageTitle.' '.$sep.' '.$siteName : (setting('seo_home_title') ?: $siteName);
    $description = trim($__env->yieldContent('meta_description')) ?: (setting('site_description') ?: $siteName);
    $ogImage = trim($__env->yieldContent('og_image')) ?: (\App\Support\Settings::media('seo_og_image_media_id')?->url() ?? \App\Support\Settings::logoUrl());
    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
    $noindex = ! setting('seo_allow_indexing') || ! empty($isPreview) || trim($__env->yieldContent('noindex')) !== '';
@endphp
<!doctype html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ \Illuminate\Support\Str::limit($description, 300) }}">
@if ($noindex)
<meta name="robots" content="noindex, nofollow">
@else
<link rel="canonical" href="{{ $canonical }}">
@endif
<meta property="og:type" content="@yield('og_type', 'website')">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $pageTitle !== '' ? $pageTitle : $siteName }}">
<meta property="og:description" content="{{ \Illuminate\Support\Str::limit($description, 300) }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:locale" content="pt_AO">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#d10000">
<link rel="icon" href="{{ \App\Support\Settings::faviconUrl() }}" type="image/png">

<link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/variables.css') }}" rel="stylesheet">
<link href="{{ asset('css/components.css') }}" rel="stylesheet">
<link href="{{ asset('css/pages.css') }}" rel="stylesheet">
<link href="{{ asset('css/responsive.css') }}" rel="stylesheet">
<link href="{{ asset('css/uon.css') }}?v=5" rel="stylesheet">
@stack('head')
</head>
<body class="site-body" data-section="@yield('section')">
<a class="visually-hidden-focusable" href="#main-content">Saltar para o conteúdo</a>
@if (! empty($isPreview))
  <div class="preview-banner" role="status"><i class="bi bi-eye"></i> Pré-visualização privada — este conteúdo pode não estar publicado. <a href="{{ $previewBackUrl ?? url('/admin') }}" class="text-reset text-decoration-underline ms-2">Voltar ao CMS</a></div>
@endif
<div class="site-shell">
@include('partials.header')

@yield('page_header')

<main id="main-content">
@yield('content')
</main>

@include('partials.footer')
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/navigation.js') }}"></script>
<script src="{{ asset('js/uon.js') }}?v=2"></script>
@stack('scripts')
</body>
</html>
