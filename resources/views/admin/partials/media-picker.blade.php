{{-- Selector da biblioteca multimédia (usado por campos de imagem, blocos e editor TinyMCE). --}}
<div class="modal fade" id="mediaPicker" tabindex="-1" aria-labelledby="mediaPickerTitle" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fs-6" id="mediaPickerTitle"><i class="bi bi-collection"></i> Biblioteca multimédia</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3 mb-3 align-items-end">
          <div class="col-md-6">
            <label class="form-label small mb-1" for="mpSearch">Pesquisar ficheiros</label>
            <input type="search" class="form-control" id="mpSearch" placeholder="Nome, título ou texto alternativo">
          </div>
          <div class="col-md-6">
            <label class="u-upload py-2 position-relative" data-mp-drop>
              <input type="file" data-mp-file aria-label="Carregar novo ficheiro">
              <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
              <span>Carregar novo ficheiro (arraste ou clique)</span>
              <span class="u-file" data-mp-status></span>
            </label>
          </div>
        </div>
        <div class="g-grid" data-mp-grid aria-live="polite"></div>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <small class="text-muted" data-mp-total></small>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-u-light" data-mp-prev disabled><i class="bi bi-chevron-left"></i> Anterior</button>
            <button type="button" class="btn btn-sm btn-u-light" data-mp-next disabled>Seguinte <i class="bi bi-chevron-right"></i></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
