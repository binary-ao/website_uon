/**
 * admin.js — CMS da Universidade Onze de Novembro
 * - Confirmação de acções destrutivas (modal)
 * - Selector da biblioteca multimédia (campos de imagem, blocos, TinyMCE)
 * - Editor de texto rico (TinyMCE 6, licença MIT, auto-alojado)
 * - Editor de blocos controlados (gera JSON validado e sanitizado no servidor)
 * - Linhas repetíveis (plano curricular)
 * Toda a validação e autorização são feitas no servidor; este ficheiro
 * apenas melhora a experiência de edição.
 */
(function () {
  "use strict";

  var U = window.UON || {};

  function el(tag, attrs, children) {
    var node = document.createElement(tag);
    Object.keys(attrs || {}).forEach(function (k) {
      if (k === "class") node.className = attrs[k];
      else if (k === "text") node.textContent = attrs[k];
      else if (k.indexOf("on") === 0) node.addEventListener(k.slice(2), attrs[k]);
      else node.setAttribute(k, attrs[k]);
    });
    (children || []).forEach(function (c) { if (c) node.appendChild(typeof c === "string" ? document.createTextNode(c) : c); });
    return node;
  }

  /* ------------------------------------------------------------------ */
  /* Confirmação                                                         */
  /* ------------------------------------------------------------------ */
  function initConfirm() {
    var modalEl = document.getElementById("confirmModal");
    if (!modalEl || !window.bootstrap) return;
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    var pending = null;
    document.addEventListener("submit", function (e) {
      var form = e.target;
      if (!form.matches("form[data-confirm]") || form.dataset.confirmed === "1") return;
      e.preventDefault();
      pending = form;
      modalEl.querySelector("[data-confirm-text]").textContent = form.getAttribute("data-confirm");
      modal.show();
    }, true);
    modalEl.querySelector("[data-confirm-ok]").addEventListener("click", function () {
      if (!pending) return;
      pending.dataset.confirmed = "1";
      modal.hide();
      pending.submit();
    });
  }

  /* ------------------------------------------------------------------ */
  /* Selector multimédia                                                 */
  /* ------------------------------------------------------------------ */
  var Picker = (function () {
    var modalEl, grid, search, prevBtn, nextBtn, totalEl, statusEl, fileInput;
    var state = { kind: "image", callback: null, url: null };
    var timer;

    function setup() {
      modalEl = document.getElementById("mediaPicker");
      if (!modalEl) return false;
      grid = modalEl.querySelector("[data-mp-grid]");
      search = modalEl.querySelector("#mpSearch");
      prevBtn = modalEl.querySelector("[data-mp-prev]");
      nextBtn = modalEl.querySelector("[data-mp-next]");
      totalEl = modalEl.querySelector("[data-mp-total]");
      statusEl = modalEl.querySelector("[data-mp-status]");
      fileInput = modalEl.querySelector("[data-mp-file]");
      search.addEventListener("input", function () {
        clearTimeout(timer);
        timer = setTimeout(function () { load(buildUrl()); }, 300);
      });
      prevBtn.addEventListener("click", function () { if (prevBtn.dataset.url) load(prevBtn.dataset.url); });
      nextBtn.addEventListener("click", function () { if (nextBtn.dataset.url) load(nextBtn.dataset.url); });
      fileInput.addEventListener("change", function () { if (fileInput.files[0]) upload(fileInput.files[0]); });
      var drop = modalEl.querySelector("[data-mp-drop]");
      ["dragenter", "dragover"].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add("drag"); }); });
      ["dragleave", "drop"].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove("drag"); }); });
      drop.addEventListener("drop", function (e) { if (e.dataTransfer.files[0]) upload(e.dataTransfer.files[0]); });
      if (!U.canUpload) drop.classList.add("d-none");
      return true;
    }

    function buildUrl() {
      var u = new URL(U.pickerUrl, window.location.origin);
      if (state.kind && state.kind !== "any") u.searchParams.set("type", state.kind);
      if (search.value.trim()) u.searchParams.set("q", search.value.trim());
      return u.toString();
    }

    function load(url) {
      grid.innerHTML = '<div class="r-empty"><i class="bi bi-hourglass-split"></i>A carregar…</div>';
      fetch(url, { headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" }, credentials: "same-origin" })
        .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(render)
        .catch(function () { grid.innerHTML = '<div class="r-empty"><i class="bi bi-exclamation-triangle"></i>Não foi possível carregar a biblioteca.</div>'; });
    }

    function render(payload) {
      grid.innerHTML = "";
      if (!payload.data.length) {
        grid.appendChild(el("div", { class: "r-empty", style: "grid-column:1/-1" }, [el("i", { class: "bi bi-inbox" }), "Sem ficheiros. Carregue um novo ficheiro acima."]));
      }
      payload.data.forEach(function (m) {
        var thumb = m.thumb ? el("img", { src: m.thumb, alt: "", loading: "lazy" }) : el("i", { class: "bi " + m.icon });
        var fig = el("figure", { class: "g-item" }, [
          el("div", { class: "g-thumb" }, [thumb]),
          el("figcaption", {}, [document.createTextNode(m.title || m.name), el("small", { text: (m.dimensions ? m.dimensions + " · " : "") + m.size })]),
          el("button", { type: "button", class: "g-pick", "aria-label": "Seleccionar " + (m.title || m.name), onclick: function () { choose(m); } })
        ]);
        grid.appendChild(fig);
      });
      totalEl.textContent = payload.total + (payload.total === 1 ? " ficheiro" : " ficheiros");
      prevBtn.disabled = !payload.prev; prevBtn.dataset.url = payload.prev || "";
      nextBtn.disabled = !payload.next; nextBtn.dataset.url = payload.next || "";
    }

    function choose(m) {
      if (state.kind === "image" && !m.is_image) { statusEl.textContent = "Escolha uma imagem."; return; }
      var cb = state.callback;
      bootstrap.Modal.getOrCreateInstance(modalEl).hide();
      if (cb) cb(m);
    }

    function upload(file) {
      var fd = new FormData();
      fd.append("file", file);
      if (state.kind === "image") fd.append("kind", "image");
      statusEl.textContent = "A carregar " + file.name + "…";
      fetch(U.uploadUrl, { method: "POST", body: fd, credentials: "same-origin", headers: { "X-CSRF-TOKEN": U.csrf, "Accept": "application/json" } })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
        .then(function (res) {
          if (!res.ok) { statusEl.textContent = (res.body && res.body.message) || "Falha no carregamento."; return; }
          statusEl.textContent = "Ficheiro carregado.";
          fileInput.value = "";
          load(buildUrl());
        })
        .catch(function () { statusEl.textContent = "Falha no carregamento."; });
    }

    return {
      open: function (opts, callback) {
        if (!modalEl && !setup()) return;
        state.kind = (opts && opts.kind) || "image";
        state.callback = callback;
        statusEl.textContent = "";
        search.value = "";
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
        load(buildUrl());
      }
    };
  })();
  window.UonMediaPicker = Picker;

  function initMediaFields(root) {
    (root || document).querySelectorAll("[data-media-field]").forEach(function (wrap) {
      if (wrap.dataset.ready) return;
      wrap.dataset.ready = "1";
      var input = wrap.querySelector("[data-mf-input]");
      var preview = wrap.querySelector("[data-mf-preview]");
      var name = wrap.querySelector("[data-mf-name]");
      var clear = wrap.querySelector("[data-mf-clear]");
      wrap.querySelector("[data-mf-choose]").addEventListener("click", function () {
        Picker.open({ kind: wrap.dataset.kind || "image" }, function (m) {
          input.value = m.id;
          input.dispatchEvent(new Event("change", { bubbles: true }));
          preview.innerHTML = "";
          preview.appendChild(m.thumb ? el("img", { src: m.thumb, alt: "" }) : el("i", { class: "bi " + m.icon }));
          name.textContent = m.title || m.name;
          clear.classList.remove("d-none");
        });
      });
      clear.addEventListener("click", function () {
        input.value = "";
        input.dispatchEvent(new Event("change", { bubbles: true }));
        preview.innerHTML = '<i class="bi bi-image"></i>';
        name.textContent = "Nenhum ficheiro seleccionado";
        clear.classList.add("d-none");
      });
    });
  }

  /* ------------------------------------------------------------------ */
  /* TinyMCE                                                             */
  /* ------------------------------------------------------------------ */
  function initEditors() {
    var areas = document.querySelectorAll("textarea[data-editor]");
    if (!areas.length) return;
    var script = document.createElement("script");
    script.src = U.tinymceBase + "/tinymce.min.js";
    script.onload = function () {
      window.tinymce.init({
        selector: "textarea[data-editor]",
        base_url: U.tinymceBase,
        suffix: ".min",
        license_key: "gpl",
        promotion: false,
        branding: false,
        menubar: false,
        height: 460,
        language: "pt_PT",
        language_url: U.tinymceBase + "/langs/pt_PT.js",
        plugins: "lists link image table autolink code wordcount",
        toolbar: "undo redo | blocks | bold italic underline | bullist numlist blockquote | link image table | removeformat code",
        block_formats: "Parágrafo=p; Título 2=h2; Título 3=h3; Título 4=h4",
        valid_elements: "p,br,h2,h3,h4,strong/b,em/i,u,s,sub,sup,ul,ol,li,blockquote,hr,code,pre,a[href|title|target],img[src|alt|width|height|title],figure,figcaption,table,caption,thead,tbody,tfoot,tr,th[colspan|rowspan|scope],td[colspan|rowspan]",
        content_css: U.contentCss,
        body_class: "article-body",
        content_style: "body{padding:1rem;max-width:760px}",
        link_default_target: "",
        link_assume_external_targets: "https",
        relative_urls: false,
        convert_urls: false,
        image_caption: false,
        image_dimensions: true,
        object_resizing: "img",
        paste_data_images: false,
        images_upload_handler: function (blobInfo) {
          var fd = new FormData();
          fd.append("file", blobInfo.blob(), blobInfo.filename());
          fd.append("kind", "image");
          return fetch(U.uploadUrl, { method: "POST", body: fd, credentials: "same-origin", headers: { "X-CSRF-TOKEN": U.csrf, "Accept": "application/json" } })
            .then(function (r) { return r.json().then(function (j) { if (!r.ok) throw new Error(j.message || "Falha"); return j.location; }); });
        },
        file_picker_types: "image file",
        file_picker_callback: function (cb, value, meta) {
          Picker.open({ kind: meta.filetype === "image" ? "image" : "any" }, function (m) {
            cb(m.url, { alt: m.alt || m.title || "", text: m.title || m.name, title: m.title || "" });
          });
        },
        setup: function (editor) {
          editor.on("change", function () { editor.save(); });
        }
      });
    };
    document.head.appendChild(script);
  }

  /* ------------------------------------------------------------------ */
  /* Editor de blocos                                                    */
  /* ------------------------------------------------------------------ */
  function initBlocks() {
    var root = document.querySelector("[data-blocks-editor]");
    if (!root) return;
    var schema = JSON.parse(root.querySelector("[data-blocks-schema]").textContent || "{}");
    var blocks = JSON.parse(root.querySelector("[data-blocks-value]").textContent || "[]") || [];
    var output = root.querySelector("input[name='blocks_json']");
    var list = root.querySelector("[data-blocks-list]");
    var typeSelect = root.querySelector("[data-blocks-type]");
    var uid = 0;

    Object.keys(schema).forEach(function (t) {
      typeSelect.appendChild(el("option", { value: t, text: schema[t].label }));
    });

    function fieldInput(def, value, onChange) {
      var id = "blk_f_" + (++uid);
      var wrap = el("div", { class: "mb-2" });
      if (def.type === "checkbox") {
        var cb = el("input", { class: "form-check-input", type: "checkbox", id: id });
        cb.checked = !!value;
        cb.addEventListener("change", function () { onChange(cb.checked); });
        wrap.appendChild(el("div", { class: "form-check" }, [cb, el("label", { class: "form-check-label small", for: id, text: def.label })]));
        return wrap;
      }
      wrap.appendChild(el("label", { class: "form-label small mb-1" + (def.required ? " req" : ""), for: id, text: def.label }));
      var input;
      if (def.type === "textarea") {
        input = el("textarea", { class: "form-control form-control-sm", id: id, rows: def.rows || 3 });
        input.value = value || "";
      } else if (def.type === "select") {
        input = el("select", { class: "form-select form-select-sm", id: id });
        var opts = def.options || {};
        if (def.required && !Object.keys(opts).length) input.appendChild(el("option", { value: "", text: "— sem opções disponíveis —" }));
        Object.keys(opts).forEach(function (k) {
          var o = el("option", { value: k, text: opts[k] });
          if (String(value != null ? value : (def.default || "")) === String(k)) o.selected = true;
          input.appendChild(o);
        });
        if (value == null && input.value) setTimeout(function () { onChange(input.value); });
      } else if (def.type === "media") {
        var hidden = el("input", { type: "hidden", value: value || "" });
        var label = el("span", { class: "small text-muted ms-2", text: value ? "Ficheiro #" + value : "Nenhuma imagem" });
        var btn = el("button", { type: "button", class: "btn btn-sm btn-u-light", id: id }, [el("i", { class: "bi bi-image" }), " Escolher imagem"]);
        btn.addEventListener("click", function () {
          Picker.open({ kind: "image" }, function (m) { hidden.value = m.id; label.textContent = m.title || m.name; onChange(m.id); });
        });
        wrap.appendChild(el("div", {}, [btn, label, hidden]));
        return wrap;
      } else {
        input = el("input", { class: "form-control form-control-sm", id: id, type: def.type === "url" || def.type === "video" ? "text" : "text" });
        if (def.type === "url") input.placeholder = "/caminho-interno ou https://…";
        if (def.type === "video") input.placeholder = "https://www.youtube.com/watch?v=…";
        if (def.type === "icon") input.placeholder = "bi-building";
        input.value = value || "";
      }
      input.addEventListener("input", function () { onChange(input.value); });
      input.addEventListener("change", function () { onChange(input.value); });
      wrap.appendChild(input);
      return wrap;
    }

    function summary(block) {
      var d = block.data || {};
      return d.title || d.heading || d.text || d.headers || (block.items && block.items.length ? block.items.length + " item(s)" : "");
    }

    function render() {
      list.innerHTML = "";
      if (!blocks.length) {
        list.appendChild(el("div", { class: "r-empty py-3" }, [el("i", { class: "bi bi-grid" }), "Sem blocos. Adicione blocos abaixo para compor a página com componentes do template."]));
      }
      blocks.forEach(function (block, bi) {
        var def = schema[block.type];
        if (!def) return;
        block.data = block.data || {};
        var body = el("div", { class: "blk-body" });
        Object.keys(def.fields || {}).forEach(function (fname) {
          body.appendChild(fieldInput(def.fields[fname], block.data[fname], function (v) { block.data[fname] = v; sync(); }));
        });
        if (def.items) {
          block.items = block.items || [];
          var itemsWrap = el("div", { class: "blk-items" });
          block.items.forEach(function (item, ii) {
            var itemBox = el("div", { class: "blk-item" });
            var tools = el("div", { class: "blk-item-tools" }, [
              el("button", { type: "button", class: "u-iconbtn", title: "Subir", "aria-label": "Subir item", onclick: function () { move(block.items, ii, -1); } }, [el("i", { class: "bi bi-arrow-up" })]),
              el("button", { type: "button", class: "u-iconbtn", title: "Descer", "aria-label": "Descer item", onclick: function () { move(block.items, ii, 1); } }, [el("i", { class: "bi bi-arrow-down" })]),
              el("button", { type: "button", class: "u-iconbtn danger", title: "Remover", "aria-label": "Remover item", onclick: function () { block.items.splice(ii, 1); render(); } }, [el("i", { class: "bi bi-x-lg" })])
            ]);
            itemBox.appendChild(tools);
            itemBox.appendChild(el("div", { class: "small fw-semibold mb-2", text: (def.item_label || "Item") + " " + (ii + 1) }));
            Object.keys(def.items).forEach(function (fname) {
              itemBox.appendChild(fieldInput(def.items[fname], item[fname], function (v) { item[fname] = v; sync(); }));
            });
            itemsWrap.appendChild(itemBox);
          });
          body.appendChild(itemsWrap);
          body.appendChild(el("button", { type: "button", class: "pc-add mt-2", onclick: function () { block.items.push({}); render(); } }, [el("i", { class: "bi bi-plus-lg" }), " Adicionar " + (def.item_label || "item").toLowerCase()]));
        }
        var box = el("div", { class: "blk" + (block._collapsed ? " collapsed" : "") });
        var head = el("div", { class: "blk-head" }, [
          el("span", { class: "blk-type" }, [el("i", { class: "bi " + (def.icon || "bi-square") }), def.label]),
          el("span", { class: "blk-summary", text: summary(block) }),
          el("button", { type: "button", class: "u-iconbtn", title: block._collapsed ? "Expandir" : "Recolher", "aria-label": block._collapsed ? "Expandir bloco" : "Recolher bloco", "aria-expanded": block._collapsed ? "false" : "true", onclick: function () { block._collapsed = !block._collapsed; render(); } }, [el("i", { class: "bi " + (block._collapsed ? "bi-chevron-down" : "bi-chevron-up") })]),
          el("button", { type: "button", class: "u-iconbtn", title: "Subir", "aria-label": "Subir bloco", onclick: function () { move(blocks, bi, -1); } }, [el("i", { class: "bi bi-arrow-up" })]),
          el("button", { type: "button", class: "u-iconbtn", title: "Descer", "aria-label": "Descer bloco", onclick: function () { move(blocks, bi, 1); } }, [el("i", { class: "bi bi-arrow-down" })]),
          el("button", { type: "button", class: "u-iconbtn danger", title: "Remover bloco", "aria-label": "Remover bloco", onclick: function () { if (window.confirm("Remover este bloco?")) { blocks.splice(bi, 1); render(); } } }, [el("i", { class: "bi bi-trash" })])
        ]);
        box.appendChild(head);
        box.appendChild(body);
        list.appendChild(box);
      });
      sync();
    }

    function move(arr, i, d) {
      var j = i + d;
      if (j < 0 || j >= arr.length) return;
      var t = arr[i]; arr[i] = arr[j]; arr[j] = t;
      render();
    }

    function sync() {
      output.value = JSON.stringify(blocks.map(function (b) {
        var out = { type: b.type, data: b.data || {} };
        if (b.items) out.items = b.items;
        return out;
      }));
    }

    root.querySelector("[data-blocks-add]").addEventListener("click", function () {
      var t = typeSelect.value;
      if (!schema[t]) return;
      var block = { type: t, data: {} };
      if (schema[t].items) block.items = [{}];
      blocks.push(block);
      render();
      list.lastElementChild && list.lastElementChild.scrollIntoView({ behavior: "smooth", block: "center" });
    });

    render();
    var form = root.closest("form");
    if (form) form.addEventListener("submit", sync);
  }

  /* ------------------------------------------------------------------ */
  /* Linhas repetíveis (plano curricular)                                */
  /* ------------------------------------------------------------------ */
  function initRepeaters() {
    document.querySelectorAll("[data-repeater]").forEach(function (rep) {
      var tbody = rep.querySelector("tbody");
      var tpl = rep.querySelector("template");
      var counter = tbody.children.length + 1000;
      function renumber() {
        Array.prototype.forEach.call(tbody.children, function (tr, i) {
          var n = tr.querySelector("[data-row-number]");
          if (n) n.textContent = i + 1;
        });
      }
      rep.querySelector("[data-repeater-add]").addEventListener("click", function () {
        var html = tpl.innerHTML.replace(/__INDEX__/g, String(counter++));
        tbody.insertAdjacentHTML("beforeend", html);
        var last = tbody.lastElementChild;
        var prev = last.previousElementSibling;
        if (prev) {
          var py = prev.querySelector("[data-copy-year]"), ly = last.querySelector("[data-copy-year]");
          if (py && ly) ly.value = py.value;
        }
        var first = last.querySelector("input[type=text]");
        if (first) first.focus();
        renumber();
      });
      tbody.addEventListener("click", function (e) {
        var btn = e.target.closest("[data-repeater-remove]");
        if (btn) { btn.closest("tr").remove(); renumber(); }
      });
    });
  }

  /* ------------------------------------------------------------------ */
  /* Diversos                                                            */
  /* ------------------------------------------------------------------ */
  function initUploadLabels() {
    document.querySelectorAll(".u-upload input[type=file]").forEach(function (input) {
      if (input.hasAttribute("data-mp-file")) return;
      var label = input.closest(".u-upload").querySelector(".u-file");
      input.addEventListener("change", function () {
        if (label) label.textContent = input.files[0] ? input.files[0].name + " (" + Math.round(input.files[0].size / 1024) + " KB)" : "";
      });
    });
  }

  function initLayoutOptions() {
    var select = document.querySelector("[data-layout-select]");
    if (!select) return;
    function apply() {
      document.querySelectorAll("[data-layout-only]").forEach(function (box) {
        var allowed = box.getAttribute("data-layout-only").split(" ");
        box.classList.toggle("d-none", allowed.indexOf(select.value) === -1);
      });
    }
    select.addEventListener("change", apply);
    apply();
  }

  function initTypeToggle() {
    var select = document.querySelector("[data-type-toggle]");
    if (!select) return;
    function apply() {
      document.querySelectorAll("[data-type-only]").forEach(function (box) {
        box.classList.toggle("d-none", box.getAttribute("data-type-only") !== select.value);
      });
    }
    select.addEventListener("change", apply);
    apply();
  }

  function initLinkType() {
    document.querySelectorAll("[data-link-type]").forEach(function (select) {
      var scope = select.closest("form");
      function apply() {
        scope.querySelectorAll("[data-link-only]").forEach(function (box) {
          box.classList.toggle("d-none", box.getAttribute("data-link-only") !== select.value);
        });
      }
      select.addEventListener("change", apply);
      apply();
    });
  }

  function initDirtyWarning() {
    var form = document.querySelector("form[data-dirty-check]");
    if (!form) return;
    var dirty = false;
    form.addEventListener("input", function () { dirty = true; });
    form.addEventListener("submit", function () { dirty = false; });
    window.addEventListener("beforeunload", function (e) {
      if (dirty) { e.preventDefault(); e.returnValue = ""; }
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    initConfirm();
    initMediaFields();
    initEditors();
    initBlocks();
    initRepeaters();
    initUploadLabels();
    initLayoutOptions();
    initTypeToggle();
    initLinkType();
    initDirtyWarning();
  });
})();
