/**
 * navigation.js
 * Comportamento de navegação: header com sombra ao fazer scroll, botão
 * "voltar ao topo", flyout de pesquisa e pequenas melhorias de acessibilidade
 * do offcanvas mobile.
 */
(function () {
  "use strict";

  function initHeaderScroll() {
    var header = document.querySelector(".site-header");
    if (!header) return;
    function onScroll() {
      header.classList.toggle("is-scrolled", window.scrollY > 8);
    }
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  function initBackToTop() {
    var btn = document.getElementById("backToTop");
    if (!btn) return;
    function onScroll() {
      btn.classList.toggle("show", window.scrollY > 500);
    }
    window.addEventListener("scroll", onScroll, { passive: true });
    btn.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  function initSearchFlyout() {
    var toggle = document.getElementById("searchToggle");
    var flyout = document.getElementById("searchFlyout");
    if (!toggle || !flyout) return;
    toggle.addEventListener("click", function () {
      var isOpen = flyout.classList.toggle("d-block");
      flyout.classList.toggle("d-none", !isOpen);
      toggle.setAttribute("aria-expanded", String(isOpen));
      if (isOpen) {
        var input = flyout.querySelector("input[type='search']");
        if (input) input.focus();
      }
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && flyout.classList.contains("d-block")) {
        flyout.classList.add("d-none");
        flyout.classList.remove("d-block");
        toggle.setAttribute("aria-expanded", "false");
        toggle.focus();
      }
    });
  }

  function initRevealOnScroll() {
    var items = document.querySelectorAll(".reveal");
    if (!items.length) return;
    if (!("IntersectionObserver" in window)) {
      items.forEach(function (el) { el.classList.add("is-visible"); });
      return;
    }
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    items.forEach(function (el) { observer.observe(el); });
  }

  document.addEventListener("DOMContentLoaded", function () {
    initHeaderScroll();
    initBackToTop();
    initSearchFlyout();
    initRevealOnScroll();
  });
})();
