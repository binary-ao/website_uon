<?php

namespace App\Policies;

use App\Enums\PublicationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Política genérica para conteúdos editoriais.
 * - "{módulo}.create": cria e edita os PRÓPRIOS conteúdos enquanto não estiverem publicados.
 * - "{módulo}.edit": edita qualquer conteúdo.
 * - "{módulo}.publish": publica, agenda, despublica.
 * - "{módulo}.delete": arquiva, elimina e restaura.
 */
abstract class ContentPolicy
{
    abstract protected function module(): string;

    protected function can(User $user, string $action): bool
    {
        return $user->can($this->module().'.'.$action);
    }

    protected function owns(User $user, Model $model): bool
    {
        return (int) $model->getAttribute('author_id') === (int) $user->id;
    }

    protected function editableByAuthor(Model $model): bool
    {
        $status = $model->getAttribute('status');

        return in_array($status, PublicationStatus::nonPublishing(), true);
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'create') || $this->can($user, 'edit');
    }

    public function view(User $user, Model $model): bool
    {
        return $this->can($user, 'edit') || ($this->can($user, 'create') && $this->owns($user, $model));
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create') || $this->can($user, 'edit');
    }

    public function update(User $user, Model $model): bool
    {
        if (method_exists($model, 'trashed') && $model->trashed()) {
            return false;
        }

        return $this->can($user, 'edit')
            || ($this->can($user, 'create') && $this->owns($user, $model) && $this->editableByAuthor($model));
    }

    public function publish(User $user, ?Model $model = null): bool
    {
        return $this->can($user, 'publish');
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->can($user, 'delete')
            || ($this->can($user, 'create') && $this->owns($user, $model) && $model->getAttribute('status') === PublicationStatus::Draft);
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->can($user, 'delete');
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $this->can($user, 'delete');
    }
}
