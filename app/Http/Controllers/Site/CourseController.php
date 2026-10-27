<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Course;

class CourseController extends Controller
{
    public function show(string $slug)
    {
        $course = Course::published()->where('slug', $slug)->with(['degree', 'unit', 'subjects', 'featuredMedia'])->firstOrFail();

        return $this->display($course);
    }

    public function display(Course $course, bool $isPreview = false)
    {
        return view('public.course-show', [
            'course' => $course,
            'years' => $course->subjects->groupBy('year'),
            'isPreview' => $isPreview,
            'previewBackUrl' => $isPreview ? route('admin.courses.edit', $course) : null,
        ]);
    }
}
