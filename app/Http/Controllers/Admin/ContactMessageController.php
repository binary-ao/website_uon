<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $status = array_key_exists((string) $request->query('status'), ContactMessage::STATUSES) ? $request->query('status') : null;
        $items = ContactMessage::query()
            ->when($status, fn ($q) => $q->where('status', $status), fn ($q) => $q->where('status', '!=', 'archived'))
            ->when($request->filled('q'), function ($q) use ($request) {
                $like = '%'.addcslashes((string) $request->query('q'), '%_\\').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('subject', 'like', $like)->orWhere('message', 'like', $like));
            })
            ->latest('id')->paginate(config('uon.pagination.admin'))->withQueryString();

        return view('admin.messages.index', compact('items', 'status'));
    }

    public function show(ContactMessage $message)
    {
        if ($message->status === 'new') {
            $message->forceFill(['status' => 'read', 'read_at' => now(), 'read_by' => auth()->id()])->save();
        }

        return view('admin.messages.show', compact('message'));
    }

    public function update(Request $request, ContactMessage $message)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(ContactMessage::STATUSES))]]);
        $message->forceFill(['status' => $data['status']])->save();
        ActivityLogger::log('messages.updated', $message, 'Alterou o estado de uma mensagem de contacto para "'.ContactMessage::STATUSES[$data['status']].'"');

        return back()->with('success', 'Estado da mensagem actualizado.');
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();
        ActivityLogger::log('messages.deleted', null, 'Eliminou uma mensagem de contacto de '.$message->email);

        return redirect()->route('admin.messages.index')->with('success', 'Mensagem eliminada.');
    }
}
