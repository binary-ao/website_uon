@props(['model'])
<div class="u-card">
  <div class="u-card-title"><i class="bi bi-search"></i> SEO e partilha</div>
  <x-admin.field name="meta_title" label="Título para motores de pesquisa" :value="$model->meta_title" help="Opcional. Se vazio, usa o título." />
  <x-admin.field name="meta_description" label="Meta description" type="textarea" rows="3" :value="$model->meta_description" help="Até 320 caracteres. Se vazio, usa o resumo." class="mb-0" />
</div>
