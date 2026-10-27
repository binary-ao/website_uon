<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Admin\Concerns\ManagesContent;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Support\HtmlSanitizer;
use App\Support\SafeUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    use ManagesContent;

    protected function modelClass(): string
    {
        return Event::class;
    }

    protected function routePrefix(): string
    {
        return 'admin.events';
    }

    protected function label(): string
    {
        return 'evento';
    }

    protected function permissionModule(): ?string
    {
        return 'events';
    }

    protected function sortable(): array
    {
        return ['updated_at', 'starts_at', 'title', 'published_at'];
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Event::class);
        $query = Event::query()->with(['category:id,name', 'author:id,name']);
        $this->applyListFilters($query, $request, ['title', 'location']);
        if ($request->query('period') === 'upcoming') {
            $query->reorder()->upcoming();
        } elseif ($request->query('period') === 'past') {
            $query->reorder()->past();
        }
        if ($request->filled('category')) {
            $query->where('category_id', (int) $request->query('category'));
        }
        if (! $request->filled('sort') && ! $request->filled('period')) {
            $query->reorder()->orderByDesc('starts_at');
        }

        return view('admin.events.index', [
            'items' => $query->paginate(config('uon.pagination.admin'))->withQueryString(),
            'categories' => Category::ofType('event')->pluck('name', 'id'),
            'authors' => $this->authorOptions(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Event::class);

        return view('admin.events.form', [
            'event' => new Event(['status' => PublicationStatus::Draft]),
            'categories' => Category::ofType('event')->pluck('name', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Event::class);
        $event = new Event();
        $this->save($request, $event);

        return redirect()->route('admin.events.edit', $event)->with('success', 'Evento criado.');
    }

    public function edit(Event $event)
    {
        $this->authorize('view', $event);

        return view('admin.events.form', ['event' => $event, 'categories' => Category::ofType('event')->pluck('name', 'id')]);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);
        $this->save($request, $event);

        return redirect()->route('admin.events.edit', $event)->with('success', 'Evento guardado.');
    }

    private function save(Request $request, Event $event): void
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:220'],
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('events', 'slug')->ignore($event->id)],
            'excerpt' => ['nullable', 'string', 'max:600'],
            'body' => ['nullable', 'string', 'max:500000'],
            'location' => ['nullable', 'string', 'max:200'],
            'start_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'all_day' => ['nullable', 'boolean'],
            'featured_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'event')],
            'registration_url' => ['nullable', 'string', 'max:2048', SafeUrl::rule()],
            'registration_info' => ['nullable', 'string', 'max:200'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'status' => ['required', Rule::in(array_keys(PublicationStatus::options()))],
            'published_at' => ['nullable', 'date'],
        ], [], ['start_date' => 'data de início', 'end_date' => 'data de fim', 'start_time' => 'hora de início', 'end_time' => 'hora de fim']);

        $allDay = $request->boolean('all_day');
        $startsAt = Carbon::parse($data['start_date'].' '.($allDay ? '00:00' : ($data['start_time'] ?? '00:00')));
        $endsAt = null;
        if (! empty($data['end_date']) || (! $allDay && ! empty($data['end_time']))) {
            $endsAt = Carbon::parse(($data['end_date'] ?? $data['start_date']).' '.($allDay ? '23:59' : ($data['end_time'] ?? '23:59')));
            if ($endsAt->lt($startsAt)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['end_time' => 'O fim do evento não pode ser anterior ao início.']);
            }
        }

        DB::transaction(function () use ($request, $event, $data, $allDay, $startsAt, $endsAt) {
            $created = ! $event->exists;
            $event->fill([
                'title' => $data['title'],
                'slug' => ($data['slug'] ?? null) ?: ($created ? Str::slug($data['title']) : $event->slug),
                'excerpt' => $data['excerpt'] ?? null,
                'body' => HtmlSanitizer::clean($data['body'] ?? null),
                'location' => $data['location'] ?? null,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'all_day' => $allDay,
                'featured_media_id' => $data['featured_media_id'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'registration_url' => $data['registration_url'] ?? null,
                'registration_info' => $data['registration_info'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
            ]);
            $event->fill($this->publicationFromRequest($request, $created ? null : $event));
            if ($created) {
                $event->author_id = $request->user()->id;
            }
            $event->updated_by = $request->user()->id;
            $event->save();
            $this->afterSave($event, $created);
        });
    }
}
