<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Revision;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevisionController extends Controller
{
    private const ROUTES = [
        \App\Models\Page::class => 'admin.pages.edit',
        \App\Models\News::class => 'admin.news.edit',
        \App\Models\Event::class => 'admin.events.edit',
        \App\Models\Course::class => 'admin.courses.edit',
    ];

    public function show(Revision $revision)
    {
        $model = $revision->revisionable()->withTrashed()->firstOrFail();
        $this->authorize('view', $model);

        $current = [];
        foreach ($model->revisionFields() as $f) {
            $v = $model->getAttribute($f);
            $current[$f] = $v instanceof \BackedEnum ? $v->value : ($v instanceof \DateTimeInterface ? $v->format('Y-m-d H:i:s') : $v);
        }

        return view('admin.revisions.show', [
            'revision' => $revision->load('user:id,name'),
            'model' => $model,
            'current' => $current,
            'backUrl' => route(self::ROUTES[get_class($model)], $model),
        ]);
    }

    /** Repõe os campos editoriais de uma revisão (o estado de publicação mantém-se). */
    public function restore(Request $request, Revision $revision)
    {
        $model = $revision->revisionable()->firstOrFail();
        $this->authorize('update', $model);

        DB::transaction(function () use ($model, $revision, $request) {
            $model->applyRevision($revision);
            $model->updated_by = $request->user()->id;
            $model->save();
            $model->recordRevision('restored');
        });
        ActivityLogger::log('revisions.restored', $model, 'Repôs a revisão #'.$revision->id.' de "'.($model->title ?? $model->name).'"');

        return redirect()->route(self::ROUTES[get_class($model)], $model)->with('success', 'Revisão reposta. O estado de publicação não foi alterado.');
    }
}
