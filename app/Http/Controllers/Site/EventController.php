<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventController extends Controller
{
    public function show(string $slug)
    {
        $event = Event::published()->where('slug', $slug)->with(['category', 'featuredMedia'])->firstOrFail();

        return $this->display($event);
    }

    public function display(Event $event, bool $isPreview = false)
    {
        return view('public.event-show', [
            'event' => $event,
            'isPreview' => $isPreview,
            'previewBackUrl' => $isPreview ? route('admin.events.edit', $event) : null,
        ]);
    }
}
