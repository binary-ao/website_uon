{{--
    FOOTER — convertido de website_template/components/footer.html.
    Mesma grelha (4 + 2 + 2 + 2 + 2), mesma ordem e mesmas áreas inferiores.
    Conteúdos geridos no CMS (Menus: footer_1/2/3/bottom; Configurações: Rodapé, Contactos, Redes sociais).
--}}
@php
    $siteName = setting('site_name');
    $social = \App\Support\Settings::socialLinks();
    $about = setting('footer_about') ?: setting('site_description');
    $contactsPage = \App\Models\Page::published()->where('path', 'contactos')->first();
    $extAttr = fn ($item) => ($item->isExternal() || $item->open_in_new_tab) ? ' target="_blank" rel="noopener"' : '';
@endphp
<footer class="site-footer">
  <div class="container-xl">
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <img src="{{ \App\Support\Settings::logoUrl(true) }}" alt="{{ $siteName }}" class="footer-brand-logo" loading="lazy">
        @if ($about)
          <p class="footer-about">{{ $about }}</p>
        @endif
        @if ($social)
          <div class="footer-social mt-3">
            @foreach ($social as $s)
              <a href="{{ $s['url'] }}" target="_blank" rel="noopener" aria-label="{{ $s['label'] }}"><i class="bi {{ $s['icon'] }}"></i></a>
            @endforeach
          </div>
        @endif
      </div>

      @foreach (['footer_1', 'footer_2', 'footer_3'] as $loc)
        @php $items = $menus->tree($loc); $title = $menus->title($loc); @endphp
        <div class="col-lg-2 col-md-6 col-6">
          @if ($title && $items->isNotEmpty())<h6>{{ $title }}</h6>@endif
          <ul>
            @foreach ($items as $item)
              @if ($item->display !== 'divider')
                <li><a href="{{ $item->href() }}"{!! $extAttr($item) !!}>{{ $item->label }}@if ($item->isExternal()) <i class="bi bi-box-arrow-up-right small"></i>@endif</a></li>
              @endif
            @endforeach
          </ul>
          @if ($loc === 'footer_3' && $items->contains(fn ($i) => $i->isExternal()) && setting('footer_external_note'))
            <p class="footer-ext-note mt-2 mb-0">{{ setting('footer_external_note') }}</p>
          @endif
        </div>
      @endforeach

      <div class="col-lg-2 col-md-6 col-6">
        <h6>{{ setting('footer_contacts_title') }}</h6>
        <ul>
          @if (setting('contact_address'))
            <li class="d-flex gap-2"><i class="bi bi-geo-alt mt-1"></i><span>{{ setting('contact_address') }}</span></li>
          @endif
          @if (setting('contact_phone'))
            <li class="d-flex gap-2"><i class="bi bi-telephone mt-1"></i><span>{{ setting('contact_phone') }}</span></li>
          @endif
          @if (setting('contact_email'))
            <li class="d-flex gap-2"><i class="bi bi-envelope mt-1"></i><span>{{ setting('contact_email') }}</span></li>
          @endif
        </ul>
        @if ($contactsPage && setting('footer_contacts_button'))
          <a href="{{ $contactsPage->url() }}" class="btn btn-sm btn-outline-light-brand mt-2">{{ setting('footer_contacts_button') }}</a>
        @endif
      </div>
    </div>

    <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 text-center text-md-start">
      <span>© {{ now()->year }} {{ $siteName }}. {{ setting('footer_copyright') }}</span>
      <div class="d-flex gap-3 flex-wrap justify-content-center">
        @foreach ($menus->tree('footer_bottom') as $item)
          @if ($item->display !== 'divider')
            <a href="{{ $item->href() }}"{!! $extAttr($item) !!}>{{ $item->label }}</a>
          @endif
        @endforeach
      </div>
    </div>
  </div>
</footer>

<button id="backToTop" aria-label="Voltar ao topo"><i class="bi bi-arrow-up"></i></button>
