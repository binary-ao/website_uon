<?php

namespace App\Support;

use App\Models\Course;

/**
 * Estrutura o plano curricular de um curso em anos → semestres, com totais
 * de carga horária e créditos. Usado na página do curso e no PDF.
 */
class Curriculum
{
    /**
     * @return array<int, array{year:int,label:string,hours:int,credits:float,semesters:array}>
     */
    public static function build(Course $course): array
    {
        $subjects = $course->relationLoaded('subjects') ? $course->subjects : $course->subjects()->get();
        $years = [];
        foreach ($subjects->groupBy('year')->sortKeys() as $year => $items) {
            $semesters = [];
            foreach ($items->groupBy(fn ($s) => $s->semester ?? 0)->sortKeys() as $sem => $list) {
                $semesters[] = [
                    'label' => match ((int) $sem) {
                        1, 2 => ((($year - 1) * 2) + $sem).'.º Semestre',
                        3 => 'Unidades curriculares anuais',
                        default => 'Unidades curriculares',
                    },
                    'subjects' => $list->values(),
                    'hours' => (int) $list->sum('hours'),
                    'credits' => (float) $list->sum('credits'),
                ];
            }
            $years[] = [
                'year' => (int) $year,
                'label' => $year.'.º Ano',
                'hours' => (int) $items->sum('hours'),
                'credits' => (float) $items->sum('credits'),
                'semesters' => $semesters,
            ];
        }

        return $years;
    }

    public static function number(?float $value): string
    {
        if ($value === null) {
            return '—';
        }

        return rtrim(rtrim(number_format($value, 1, ',', ''), '0'), ',');
    }

    public static function hasCredits(array $years): bool
    {
        return collect($years)->sum('credits') > 0;
    }

    public static function hasCodes(Course $course): bool
    {
        return $course->subjects->whereNotNull('code')->isNotEmpty();
    }
}
