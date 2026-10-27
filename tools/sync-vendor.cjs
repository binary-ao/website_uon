#!/usr/bin/env node
/**
 * Copia as dependências de frontend (instaladas com `npm install`) para public/vendor.
 * O website não usa Vite/build step: o CSS e o JS do template são servidos tal como estão.
 */
const fs = require("fs");
const path = require("path");

const root = path.resolve(__dirname, "..");
const nm = path.join(root, "node_modules");
const out = path.join(root, "public", "vendor");

const copies = [
  ["bootstrap/dist/css", "bootstrap/css"],
  ["bootstrap/dist/js", "bootstrap/js"],
  ["bootstrap/LICENSE", "bootstrap/LICENSE"],
  ["bootstrap-icons/font", "bootstrap-icons"],
  ["bootstrap-icons/LICENSE", "bootstrap-icons/LICENSE"],
  ["tinymce", "tinymce"],
  ["tinymce-i18n/langs7/pt_PT.js", "tinymce/langs/pt_PT.js"],
];

for (const [from, to] of copies) {
  const src = path.join(nm, from);
  if (!fs.existsSync(src)) {
    console.error("Em falta: " + src + " — corre primeiro `npm install`.");
    process.exit(1);
  }
  fs.cpSync(src, path.join(out, to), { recursive: true });
  console.log("Copiado " + from + " -> public/vendor/" + to);
}
