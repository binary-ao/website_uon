{{-- Contactos — contactos/index.html (Template G). Formulário ligado ao backend. --}}
@php
    $mapUrl = setting('map_embed_url');
    $mapOk = $mapUrl && preg_match('#^https://(www\.google\.com/maps/embed\?|www\.openstreetmap\.org/export/embed\.html\?)#', $mapUrl);
    $social = \App\Support\Settings::socialLinks();
    $cards = array_filter([
        setting('contact_address') ? ['bi-geo-alt', 'Morada', [setting('contact_address')]] : null,
        (setting('contact_phone') || setting('contact_phone_alt')) ? ['bi-telephone', 'Telefone', array_filter([setting('contact_phone'), setting('contact_phone_alt')])] : null,
        (setting('contact_email') || setting('contact_email_secondary')) ? ['bi-envelope', 'Email', array_filter([setting('contact_email'), setting('contact_email_secondary')])] : null,
    ]);
@endphp
<section class="section">
  <div class="container-xl">
    @if ($cards)
      <div class="row g-4 mb-5">
        @foreach ($cards as [$icon, $label, $lines])
          <div class="col-md-{{ 12 / count($cards) }}">
            <div class="contact-info-card h-100">
              <i class="bi {{ $icon }} mb-2 d-block"></i>
              <h3 class="h6">{{ $label }}</h3>
              @foreach ($lines as $line)
                <p class="text-muted-brand small {{ $loop->last ? 'mb-0' : 'mb-1' }}">
                  @if ($icon === 'bi-envelope')<a href="mailto:{{ $line }}">{{ $line }}</a>
                  @elseif ($icon === 'bi-telephone')<a href="tel:{{ preg_replace('/[^0-9+]/', '', $line) }}" class="text-reset">{{ $line }}</a>
                  @else{!! nl2br(e($line)) !!}@endif
                </p>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>
    @endif

    <div class="row g-5">
      <div class="col-lg-6">
        <h2 class="h4 mb-4">Envie-nos uma mensagem</h2>
        <div class="form-panel">
          @if (session('contact_success'))
            <div class="alert alert-brand-success mb-4" role="alert" tabindex="-1" id="contactSuccess">
              <i class="bi bi-check-circle"></i> Mensagem enviada com sucesso. Entraremos em contacto brevemente.
            </div>
          @endif
          @if ($errors->any())
            <div class="alert alert-brand-danger mb-4" role="alert">
              <i class="bi bi-exclamation-triangle"></i> Verifique os campos assinalados e tente novamente.
            </div>
          @endif
          <form method="post" action="{{ route('contact.store') }}" data-validate novalidate>
            @csrf
            <div class="hp-field" aria-hidden="true">
              <label for="contactWebsite">Não preencher este campo</label>
              <input type="text" id="contactWebsite" name="website" tabindex="-1" autocomplete="off">
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label for="contactName" class="form-label">Nome completo</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="contactName" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name">
                <div class="invalid-feedback">@error('name'){{ $message }}@else Indique o seu nome.@enderror</div>
              </div>
              <div class="col-md-6">
                <label for="contactEmail" class="form-label">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="contactEmail" name="email" value="{{ old('email') }}" required maxlength="190" autocomplete="email">
                <div class="invalid-feedback">@error('email'){{ $message }}@else Indique um email válido.@enderror</div>
              </div>
              <div class="col-md-6">
                <label for="contactPhone" class="form-label">Telefone <span class="text-muted-brand fw-normal">(opcional)</span></label>
                <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="contactPhone" name="phone" value="{{ old('phone') }}" maxlength="40" autocomplete="tel" pattern="[0-9 +()\-]*">
                <div class="invalid-feedback">@error('phone'){{ $message }}@else Indique um número de telefone válido.@enderror</div>
              </div>
              <div class="col-md-6">
                <label for="contactSubject" class="form-label">Assunto</label>
                @if ($subjects)
                  <select class="form-select @error('subject') is-invalid @enderror" id="contactSubject" name="subject" required>
                    <option value="" @selected(! old('subject')) disabled>Seleccione um assunto</option>
                    @foreach ($subjects as $s)
                      <option @selected(old('subject') === $s)>{{ $s }}</option>
                    @endforeach
                  </select>
                @else
                  <input type="text" class="form-control @error('subject') is-invalid @enderror" id="contactSubject" name="subject" value="{{ old('subject') }}" required maxlength="150">
                @endif
                <div class="invalid-feedback">@error('subject'){{ $message }}@else Seleccione um assunto.@enderror</div>
              </div>
              <div class="col-12">
                <label for="contactMessage" class="form-label">Mensagem</label>
                <textarea class="form-control @error('message') is-invalid @enderror" id="contactMessage" name="message" rows="5" required minlength="10" maxlength="5000">{{ old('message') }}</textarea>
                <div class="invalid-feedback">@error('message'){{ $message }}@else Escreva a sua mensagem (mínimo 10 caracteres).@enderror</div>
              </div>
              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input @error('consent') is-invalid @enderror" type="checkbox" value="1" id="contactConsent" name="consent" required @checked(old('consent'))>
                  <label class="form-check-label small" for="contactConsent">Autorizo o tratamento dos dados indicados para efeitos de resposta a este pedido.</label>
                  <div class="invalid-feedback">É necessário autorizar o tratamento dos dados.</div>
                </div>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-brand">Enviar Mensagem</button>
              </div>
            </div>
          </form>
        </div>
      </div>
      <div class="col-lg-6">
        <h2 class="h4 mb-4">Localização</h2>
        @if ($mapOk)
          <div class="contact-map-embed">
            <iframe src="{{ $mapUrl }}" title="Mapa de localização — {{ setting('site_name') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        @else
          <div class="ph-media dark contact-map-ph">
            <i class="bi bi-map"></i>
            <span class="ph-label">Mapa em preparação</span>
          </div>
        @endif
        <div class="row g-3 mt-1">
          @if (setting('office_hours'))
            <div class="col-md-6">
              <div class="card-directory">
                <h3 class="h6">Horário</h3>
                <p class="text-muted-brand small mb-0">{{ setting('office_hours') }}</p>
              </div>
            </div>
          @endif
          @if ($social)
            <div class="col-md-6">
              <div class="card-directory">
                <h3 class="h6">Redes Sociais</h3>
                <div class="d-flex gap-2 mt-2">
                  @foreach ($social as $s)
                    <a href="{{ $s['url'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-brand" aria-label="{{ $s['label'] }}"><i class="bi {{ $s['icon'] }}"></i></a>
                  @endforeach
                </div>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>
