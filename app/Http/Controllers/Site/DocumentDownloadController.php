<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Download de documentos através de rota autorizada. Os ficheiros estão fora
 * da pasta pública; documentos restritos exigem sessão autenticada; rascunhos
 * só podem ser descarregados por quem os pode editar no CMS.
 */
class DocumentDownloadController extends Controller
{
    public function __invoke(string $slug)
    {
        $document = Document::where('slug', $slug)->with('currentVersion')->firstOrFail();
        $user = auth()->user();

        if (! $document->isPubliclyVisible()) {
            abort_unless($user && $user->can('view', $document), 404);
        }
        if (! $document->isPublic() && ! $user) {
            return redirect()->guest(route('login'))->with('status', 'Este documento é de acesso restrito. Inicie sessão para continuar.');
        }

        $version = $document->currentVersion;
        if ((int) request()->query('versao') > 0 && $user && $user->can('view', $document)) {
            $version = $document->versions()->where('version', (int) request()->query('versao'))->first();
        }
        abort_unless($version && Storage::disk($version->disk)->exists($version->path), 404);

        $name = Str::slug($document->title).'.'.$version->extension;
        $inline = $version->extension === 'pdf' && request()->boolean('ver');

        return Storage::disk($version->disk)->response($version->path, $name, [
            'Content-Type' => $version->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => $document->isPublic() ? 'public, max-age=3600' : 'private, no-store',
        ], $inline ? 'inline' : 'attachment');
    }
}
