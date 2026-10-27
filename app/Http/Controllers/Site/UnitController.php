<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\OrganicUnit;

/** Página pública de uma unidade orgânica (conteúdo gerido em CMS → Unidades orgânicas). */
class UnitController extends Controller
{
    public function show(string $slug)
    {
        $unit = OrganicUnit::published()->where('slug', $slug)->firstOrFail();

        return $this->display($unit);
    }

    public function display(OrganicUnit $unit, bool $isPreview = false)
    {
        $unit->load([
            'parent',
            'featuredMedia',
            'children' => fn ($q) => $q->published(),
            'courses' => fn ($q) => $q->published()->with(['degree', 'unit', 'featuredMedia'])->orderBy('sort_order')->orderBy('name'),
        ]);
        // Cursos dos departamentos desta unidade também pertencem à unidade.
        $childCourses = \App\Models\Course::published()->whereIn('organic_unit_id', $unit->children->pluck('id'))
            ->with(['degree', 'unit', 'featuredMedia'])->orderBy('name')->get();

        return view('public.unit-show', [
            'unit' => $unit,
            'courses' => $unit->courses->concat($childCourses)->unique('id')->values(),
            'isPreview' => $isPreview,
            'previewBackUrl' => $isPreview ? route('admin.units.edit', $unit) : null,
        ]);
    }
}
