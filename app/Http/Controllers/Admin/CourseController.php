<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Admin\Concerns\ManagesContent;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Degree;
use App\Models\OrganicUnit;
use App\Support\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    use ManagesContent;

    protected function modelClass(): string
    {
        return Course::class;
    }

    protected function routePrefix(): string
    {
        return 'admin.courses';
    }

    protected function label(): string
    {
        return 'curso';
    }

    protected function permissionModule(): ?string
    {
        return 'courses';
    }

    protected function sortable(): array
    {
        return ['updated_at', 'name', 'sort_order', 'published_at'];
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Course::class);
        $query = Course::query()->with(['degree:id,name', 'unit:id,name'])->withCount('subjects');
        $this->applyListFilters($query, $request, ['name', 'summary']);
        if ($request->filled('degree')) {
            $query->where('degree_id', (int) $request->query('degree'));
        }
        if ($request->filled('unit')) {
            $query->where('organic_unit_id', (int) $request->query('unit'));
        }

        return view('admin.courses.index', [
            'items' => $query->paginate(config('uon.pagination.admin'))->withQueryString(),
            'degrees' => Degree::orderBy('sort_order')->pluck('name', 'id'),
            'units' => OrganicUnit::orderBy('name')->pluck('name', 'id'),
            'authors' => $this->authorOptions(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Course::class);

        return view('admin.courses.form', $this->formData(new Course(['status' => PublicationStatus::Draft])));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Course::class);
        $course = new Course();
        $this->save($request, $course);

        return redirect()->route('admin.courses.edit', $course)->with('success', 'Curso criado.');
    }

    public function edit(Course $course)
    {
        $this->authorize('view', $course);
        $course->load('subjects');

        return view('admin.courses.form', $this->formData($course));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);
        $this->save($request, $course);

        return redirect()->route('admin.courses.edit', $course)->with('success', 'Curso guardado.');
    }

    private function formData(Course $course): array
    {
        return [
            'course' => $course,
            'degrees' => Degree::orderBy('sort_order')->pluck('name', 'id'),
            'units' => OrganicUnit::orderBy('name')->get(['id', 'name', 'type'])->mapWithKeys(fn ($u) => [$u->id => $u->name.' ('.$u->typeLabel().')']),
        ];
    }

    private function save(Request $request, Course $course): void
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('courses', 'slug')->ignore($course->id)],
            'degree_id' => ['required', 'integer', Rule::exists('degrees', 'id')],
            'organic_unit_id' => ['nullable', 'integer', Rule::exists('organic_units', 'id')],
            'icon' => ['nullable', 'regex:/^bi-[a-z0-9-]{1,40}$/'],
            'summary' => ['nullable', 'string', 'max:600'],
            'duration' => ['nullable', 'string', 'max:60'],
            'modality' => ['nullable', 'string', 'max:120'],
            'regime' => ['nullable', 'string', 'max:120'],
            'presentation' => ['nullable', 'string', 'max:200000'],
            'objectives' => ['nullable', 'string', 'max:200000'],
            'entry_profile' => ['nullable', 'string', 'max:5000'],
            'exit_profile' => ['nullable', 'string', 'max:5000'],
            'careers' => ['nullable', 'string', 'max:5000'],
            'curriculum_note' => ['nullable', 'string', 'max:2000'],
            'coordinator_name' => ['nullable', 'string', 'max:150'],
            'contact_email' => ['nullable', 'email', 'max:190'],
            'featured_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'status' => ['required', Rule::in(array_keys(PublicationStatus::options()))],
            'published_at' => ['nullable', 'date'],
            'subjects' => ['nullable', 'array', 'max:200'],
            'subjects.*.name' => ['nullable', 'string', 'max:200'],
            'subjects.*.year' => ['nullable', 'integer', 'min:1', 'max:10'],
            'subjects.*.semester' => ['nullable', 'integer', 'min:1', 'max:3'],
            'subjects.*.hours' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'subjects.*.credits' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'subjects.*.code' => ['nullable', 'string', 'max:30'],
            'subjects.*.is_optional' => ['nullable', 'boolean'],
        ], [
            'icon.regex' => 'Indique um ícone Bootstrap Icons válido (ex.: bi-mortarboard).',
        ], [
            'subjects.*.name' => 'unidade curricular', 'subjects.*.year' => 'ano', 'subjects.*.hours' => 'horas',
        ]);

        // "Saídas profissionais": uma por linha, "Título | descrição".
        $careers = collect(preg_split('/\r?\n/', (string) ($data['careers'] ?? '')))
            ->map(fn ($l) => trim(strip_tags($l)))->filter()
            ->map(function ($line) {
                [$title, $text] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');

                return ['title' => Str::limit($title, 120, ''), 'text' => Str::limit($text, 300, '')];
            })->take(30)->values()->all();

        DB::transaction(function () use ($request, $course, $data, $careers) {
            $created = ! $course->exists;
            $course->fill([
                'name' => $data['name'],
                'slug' => ($data['slug'] ?? null) ?: ($created ? Str::slug($data['name']) : $course->slug),
                'degree_id' => $data['degree_id'],
                'organic_unit_id' => $data['organic_unit_id'] ?? null,
                'icon' => $data['icon'] ?? null,
                'summary' => $data['summary'] ?? null,
                'duration' => $data['duration'] ?? null,
                'modality' => $data['modality'] ?? null,
                'regime' => $data['regime'] ?? null,
                'presentation' => HtmlSanitizer::clean($data['presentation'] ?? null),
                'objectives' => HtmlSanitizer::clean($data['objectives'] ?? null),
                'entry_profile' => isset($data['entry_profile']) ? strip_tags($data['entry_profile']) : null,
                'exit_profile' => isset($data['exit_profile']) ? strip_tags($data['exit_profile']) : null,
                'careers' => $careers ?: null,
                'curriculum_note' => isset($data['curriculum_note']) ? strip_tags($data['curriculum_note']) : null,
                'coordinator_name' => $data['coordinator_name'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'featured_media_id' => $data['featured_media_id'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
            ]);
            $course->fill($this->publicationFromRequest($request, $created ? null : $course));
            if ($created) {
                $course->author_id = $request->user()->id;
            }
            $course->updated_by = $request->user()->id;
            $course->save();

            // Plano curricular: substituição integral numa única transacção.
            $course->subjects()->delete();
            $order = 0;
            foreach ($data['subjects'] ?? [] as $row) {
                if (blank($row['name'] ?? null) || blank($row['year'] ?? null)) {
                    continue;
                }
                $course->subjects()->create([
                    'year' => (int) $row['year'],
                    'semester' => $row['semester'] ?? null,
                    'name' => strip_tags($row['name']),
                    'hours' => $row['hours'] ?? null,
                    'credits' => $row['credits'] ?? null,
                    'code' => isset($row['code']) ? strip_tags($row['code']) : null,
                    'is_optional' => ! empty($row['is_optional']),
                    'sort_order' => $order++,
                ]);
            }
            $this->afterSave($course, $created);
        });
    }

    protected function titleOf(\Illuminate\Database\Eloquent\Model $model): string
    {
        return (string) $model->name;
    }
}
