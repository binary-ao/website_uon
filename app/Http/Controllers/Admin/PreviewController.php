<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site;
use App\Models\Course;
use App\Models\Event;
use App\Models\News;
use App\Models\Page;
use App\Services\PageModules;
use Illuminate\Http\Request;

/**
 * Pré-visualização privada: renderiza o conteúdo com o layout público,
 * independentemente do estado, apenas para utilizadores autorizados.
 * A resposta inclui "noindex" e não é guardada em cache.
 */
class PreviewController extends Controller
{
    public function __invoke(Request $request, string $type, int $id)
    {
        $model = match ($type) {
            'pages' => Page::with(['parent', 'featuredMedia', 'documents.currentVersion'])->findOrFail($id),
            'news' => News::with(['category', 'tags', 'featuredMedia', 'documents.currentVersion'])->findOrFail($id),
            'events' => Event::with(['category', 'featuredMedia'])->findOrFail($id),
            'courses' => Course::with(['degree', 'unit', 'subjects', 'featuredMedia'])->findOrFail($id),
            'units' => \App\Models\OrganicUnit::findOrFail($id),
        };
        $this->authorize('view', $model);

        return match ($type) {
            'pages' => app(Site\PageController::class)->render($model, $request, app(PageModules::class), true),
            'news' => app(Site\NewsController::class)->display($model, true),
            'events' => app(Site\EventController::class)->display($model, true),
            'courses' => app(Site\CourseController::class)->display($model, true),
            'units' => app(Site\UnitController::class)->display($model, true),
        };
    }
}
