<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $items = ActivityLog::query()->with('user:id,name')
            ->when($request->filled('user'), fn ($q) => $q->where('user_id', (int) $request->query('user')))
            ->when($request->filled('action'), fn ($q) => $q->where('action', 'like', addcslashes((string) $request->query('action'), '%_\\').'%'))
            ->when($request->filled('q'), fn ($q) => $q->where('description', 'like', '%'.addcslashes((string) $request->query('q'), '%_\\').'%'))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->latest('id')->paginate(30)->withQueryString();

        return view('admin.activity.index', [
            'items' => $items,
            'users' => User::orderBy('name')->pluck('name', 'id'),
            'actions' => ['auth' => 'Autenticação', 'pages' => 'Páginas', 'news' => 'Notícias', 'events' => 'Eventos', 'documents' => 'Documentos', 'courses' => 'Cursos', 'units' => 'Unidades', 'gallery' => 'Galeria', 'media' => 'Multimédia', 'menus' => 'Menus', 'settings' => 'Configurações', 'users' => 'Utilizadores', 'roles' => 'Funções'],
        ]);
    }
}
