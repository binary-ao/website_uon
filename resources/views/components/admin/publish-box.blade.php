@props(['model', 'previewType' => null, 'routePrefix', 'label' => 'conteúdo'])
@php
    use App\Enums\PublicationStatus;
    $user = auth()->user();
    $canPublish = $user->can('publish', $model->exists ? $model : get_class($model));
    $options = $canPublish ? PublicationStatus::options() : collect(PublicationStatus::nonPublishing())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
    $current = $model->status instanceof PublicationStatus ? $model->status : PublicationStatus::Draft;
    $locked = ! $canPublish && ($current->isPublic() || $current === PublicationStatus::Archived);
@endphp
<div class="u-card">
  <div class="u-card-title"><i class="bi bi-send"></i> Publicação</div>
  @if ($model->exists)
    <p class="small mb-3">Estado actual: <x-admin.status :status="$model->status" :date="$model->published_at" />
      @if ($model->is_demo ?? false)<span class="u-badge demo ms-1">Exemplo</span>@endif
    </p>
  @endif
  @if ($locked)
    <input type="hidden" name="status" value="{{ $current->value }}">
    <p class="small text-muted mb-3"><i class="bi bi-lock"></i> Este conteúdo já foi publicado. Só um utilizador com permissão de publicação pode alterar o estado.</p>
  @else
    <x-admin.field name="status" label="Estado" type="select" :options="$options" :value="$current->value" required />
    @if ($canPublish)
      <x-admin.field name="published_at" label="Data de publicação" type="datetime-local" :value="$model->published_at"
        help="Deixe vazio para publicar de imediato. Uma data futura agenda a publicação ({{ config('app.timezone') }})." />
    @else
      <p class="form-text mt-0 mb-3">Sem permissão de publicação: grave como rascunho ou envie para revisão.</p>
    @endif
  @endif
  <div class="d-grid gap-2">
    <button type="submit" class="btn btn-uon"><i class="bi bi-check2"></i> Guardar</button>
    @if ($model->exists && $previewType)
      <a class="btn btn-u-light" href="{{ route('admin.preview', [$previewType, $model->id]) }}" target="_blank" rel="noopener"><i class="bi bi-eye"></i> Pré-visualizar</a>
    @endif
    @if ($model->exists && method_exists($model, 'url') && $model->isPubliclyVisible())
      <a class="btn btn-u-light" href="{{ $model->url() }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Ver no website</a>
    @endif
  </div>
  @if ($model->exists)
    <dl class="small text-muted mt-3 mb-0 row g-1">
      <dt class="col-5 fw-normal">Criado</dt><dd class="col-7 mb-0">{{ $model->created_at?->format('d/m/Y H:i') }}@if ($model->author ?? null)<br>{{ $model->author->name }}@endif</dd>
      <dt class="col-5 fw-normal">Actualizado</dt><dd class="col-7 mb-0">{{ $model->updated_at?->format('d/m/Y H:i') }}</dd>
    </dl>
  @endif
</div>
