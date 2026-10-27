/**
 * uon.js
 * Comportamentos complementares do website UON (o template original fica em
 * navigation.js). Os filtros, a pesquisa e os formulários são processados no
 * servidor; aqui apenas: validação no cliente (sem impedir a validação do
 * servidor), lightbox da galeria e prevenção de duplo envio.
 */
(function () {
  "use strict";

  function initValidation() {
    document.querySelectorAll("form[data-validate]").forEach(function (form) {
      form.addEventListener("submit", function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
          form.classList.add("was-validated");
          var firstInvalid = form.querySelector(":invalid");
          if (firstInvalid) firstInvalid.focus();
          return;
        }
        var btn = form.querySelector("[type='submit']");
        if (btn) {
          btn.disabled = true;
          btn.dataset.originalText = btn.textContent;
          btn.textContent = "A enviar...";
        }
      });
    });
  }

  function initLightbox() {
    var modalEl = document.getElementById("galleryLightbox");
    if (!modalEl || !window.bootstrap) return;
    var img = modalEl.querySelector("img");
    var caption = modalEl.querySelector(".lightbox-caption");
    document.querySelectorAll("[data-lightbox-src]").forEach(function (trigger) {
      trigger.addEventListener("click", function () {
        img.src = trigger.getAttribute("data-lightbox-src");
        img.alt = trigger.getAttribute("data-lightbox-alt") || "";
        caption.textContent = trigger.getAttribute("data-lightbox-caption") || "";
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
      });
    });
    modalEl.addEventListener("hidden.bs.modal", function () { img.removeAttribute("src"); });
  }

  document.addEventListener("DOMContentLoaded", function () {
    initValidation();
    initLightbox();
  });
})();

/* Banner da página inicial: contador, pausa e "reduzir movimento". */
(function () {
  "use strict";
  document.addEventListener("DOMContentLoaded", function () {
    var el = document.querySelector("[data-uon-hero]");
    if (!el || !window.bootstrap) return;
    var reduce = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var carousel = bootstrap.Carousel.getOrCreateInstance(el, reduce ? { interval: false, ride: false } : {});
    var current = el.querySelector("[data-hero-current]");
    var pauseBtn = el.querySelector("[data-hero-pause]");
    var paused = reduce;
    function setPaused(p) {
      paused = p;
      if (!pauseBtn) return;
      pauseBtn.setAttribute("aria-pressed", String(p));
      pauseBtn.setAttribute("aria-label", p ? "Retomar a rotação automática" : "Pausar a rotação automática");
      pauseBtn.querySelector("i").className = p ? "bi bi-play-fill" : "bi bi-pause-fill";
    }
    setPaused(paused);
    if (reduce) carousel.pause();
    el.addEventListener("slid.bs.carousel", function (e) {
      if (current) current.textContent = String(e.to + 1).padStart(2, "0");
      if (paused) carousel.pause();
    });
    if (pauseBtn) {
      pauseBtn.addEventListener("click", function () {
        if (paused) { setPaused(false); carousel.cycle(); } else { setPaused(true); carousel.pause(); }
      });
    }
  });
})();
