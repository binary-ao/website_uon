<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Support\Curriculum;
use App\Support\Settings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

/** Plano curricular em PDF, com cabeçalho institucional e dados gerais do curso. */
class CurriculumPdfController extends Controller
{
    public function __invoke(string $slug)
    {
        $course = Course::published()->where('slug', $slug)->with(['degree', 'unit.parent', 'subjects'])->firstOrFail();
        $plan = Curriculum::build($course);
        abort_if(empty($plan), 404);

        $pdf = Pdf::loadView('pdf.curriculum', [
            'course' => $course,
            'plan' => $plan,
            'withCredits' => Curriculum::hasCredits($plan),
            'withCodes' => Curriculum::hasCodes($course),
            'logo' => $this->logoDataUri(),
            'siteName' => setting('site_name'),
            'generatedAt' => now(),
        ])->setPaper('a4')->setOption('isFontSubsettingEnabled', true)->setOption('isRemoteEnabled', false);

        return $pdf->download('plano-curricular-'.Str::slug($course->name).'.pdf');
    }

    /** Logótipo embebido (data URI): o dompdf não precisa de aceder a URLs remotos. */
    private function logoDataUri(): ?string
    {
        $media = Settings::media('logo_media_id');
        $path = $media ? public_path('uploads/'.$media->path) : public_path('assets/images/brand/logo-uon.png');
        if (! is_file($path)) {
            return null;
        }
        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }
}
