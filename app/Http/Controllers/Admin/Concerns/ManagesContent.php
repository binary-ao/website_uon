<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Enums\PublicationStatus;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Comportamento partilhado pelos controladores de conteúdos editoriais:
 * estado de publicação (respeitando permissões), filtros de listagem,
 * revisões, auditoria, reciclagem e restauro.
 */
trait ManagesContent
{
    /** Classe do modelo gerido. */
    abstract protected function modelClass(): string;

    /** Prefixo das rotas (ex.: admin.news). */
    abstract protected function routePrefix(): string;

    /** Nome legível (singular) para mensagens e auditoria. */
    abstract protected function label(): string;

    protected function titleOf(Model $model): string
    {
        return (string) ($model->title ?? $model->name ?? '#'.$model->getKey());
    }

    /**
     * Determina estado e data de publicação a partir do pedido.
     * Sem permissão de publicação, o utilizador só pode gravar como
     * rascunho ou enviar para revisão; conteúdos já publicados mantêm o estado.
     */
    protected function publicationFromRequest(Request $request, ?Model $model = null): array
    {
        $requested = PublicationStatus::tryFrom((string) $request->input('status')) ?? PublicationStatus::Draft;
        $date = $request->filled('published_at') ? Carbon::parse($request->input('published_at')) : null;
        $canPublish = $request->user()->can('publish', $model ?? $this->modelClass());

        if (! $canPublish) {
            if ($model && ($model->status?->isPublic() || $model->status === PublicationStatus::Archived)) {
                return ['status' => $model->status, 'published_at' => $model->published_at];
            }
            $status = in_array($requested, PublicationStatus::nonPublishing(), true) ? $requested : PublicationStatus::Draft;

            return ['status' => $status, 'published_at' => $model?->published_at];
        }

        if ($requested->isPublic()) {
            $date ??= ($model?->published_at && $model->status?->isPublic() ? $model->published_at : now());
        }

        return ['status' => $requested, 'published_at' => $date ?? $model?->published_at];
    }

    protected function applyListFilters(Builder $query, Request $request, array $searchColumns): Builder
    {
        $q = trim((string) $request->query('q'));
        if ($q !== '') {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $query->where(function ($w) use ($searchColumns, $like) {
                foreach ($searchColumns as $col) {
                    $w->orWhere($col, 'like', $like);
                }
            });
        }
        if ($request->filled('status')) {
            $query->withStatus((string) $request->query('status'));
        }
        if ($request->filled('author')) {
            $query->where('author_id', (int) $request->query('author'));
        }
        if ($request->filled('from')) {
            $query->whereDate('updated_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('updated_at', '<=', $request->date('to'));
        }
        if ($request->query('trashed') === '1' && method_exists($this->modelClass(), 'bootSoftDeletes')) {
            $query->onlyTrashed();
        }

        // Autores sem permissão de edição global vêem apenas os próprios conteúdos.
        $module = $this->permissionModule();
        if ($module && ! $request->user()->can("$module.edit")) {
            $query->where('author_id', $request->user()->id);
        }

        $sort = (string) $request->query('sort', 'updated_at');
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';
        if (in_array($sort, $this->sortable(), true)) {
            $query->orderBy($sort, $dir);
        }

        return $query;
    }

    protected function permissionModule(): ?string
    {
        return null;
    }

    protected function sortable(): array
    {
        return ['updated_at', 'published_at', 'created_at', 'title'];
    }

    protected function log(string $action, Model $model, string $verb): void
    {
        ActivityLogger::log($action, $model, $verb.' '.$this->label().' "'.$this->titleOf($model).'"', ['status' => $model->status?->value ?? null]);
    }

    /** Acção de revisão + registo após gravação. */
    protected function afterSave(Model $model, bool $created): void
    {
        $action = $created ? 'created' : 'updated';
        if (! $created && $model->wasChanged('status')) {
            $action = match ($model->status) {
                PublicationStatus::Published, PublicationStatus::Scheduled => 'published',
                PublicationStatus::Archived => 'archived',
                default => 'unpublished',
            };
        }
        if (method_exists($model, 'recordRevision')) {
            $model->recordRevision($action);
        }
        $verb = ['created' => 'Criou', 'updated' => 'Editou', 'published' => 'Publicou', 'archived' => 'Arquivou', 'unpublished' => 'Despublicou'][$action];
        $this->log($this->logKey().'.'.$action, $model, $verb);
    }

    protected function logKey(): string
    {
        return str_replace('admin.', '', $this->routePrefix());
    }

    public function destroy(Request $request, $id)
    {
        $model = $this->findModel($id);
        $this->authorize('delete', $model);
        $model->delete();
        $this->log($this->logKey().'.trashed', $model, 'Moveu para a reciclagem');

        return redirect()->route($this->routePrefix().'.index')
            ->with('success', ucfirst($this->label()).' movido(a) para a reciclagem. Pode restaurá-lo(a) em "Reciclagem".');
    }

    public function restore(Request $request, $id)
    {
        $model = $this->modelClass()::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $model);
        $model->restore();
        $this->log($this->logKey().'.restored', $model, 'Restaurou');

        return redirect()->route($this->routePrefix().'.edit', $model)->with('success', ucfirst($this->label()).' restaurado(a).');
    }

    public function forceDelete(Request $request, $id)
    {
        $model = $this->modelClass()::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $model);
        DB::transaction(function () use ($model) {
            $this->beforeForceDelete($model);
            if (method_exists($model, 'revisions')) {
                $model->revisions()->delete();
            }
            $model->forceDelete();
        });
        $this->log($this->logKey().'.deleted', $model, 'Eliminou definitivamente');

        return redirect()->route($this->routePrefix().'.index', ['trashed' => 1])->with('success', 'Eliminado(a) definitivamente.');
    }

    protected function beforeForceDelete(Model $model): void
    {
    }

    protected function findModel($id): Model
    {
        return $this->modelClass()::findOrFail($id);
    }

    /** Autores para o filtro de listagem. */
    protected function authorOptions(): array
    {
        return \App\Models\User::orderBy('name')->pluck('name', 'id')->all();
    }
}
