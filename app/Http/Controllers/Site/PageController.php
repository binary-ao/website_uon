<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\PageModules;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(Request $request, string $path, PageModules $modules)
    {
        $path = trim($path, '/');
        $page = Page::published()->where('path', $path)
            ->with(['parent', 'featuredMedia', 'documents' => fn ($d) => $d->published()->with('currentVersion')])
            ->first();
        abort_unless($page, 404);

        return $this->render($page, $request, $modules);
    }

    public function render(Page $page, Request $request, PageModules $modules, bool $isPreview = false)
    {
        $data = $page->isModule() ? $modules->data($page, $request) : [];

        return view('public.page', array_merge($data, [
            'page' => $page,
            'isPreview' => $isPreview,
            'previewBackUrl' => $isPreview ? route('admin.pages.edit', $page) : null,
        ]));
    }
}
