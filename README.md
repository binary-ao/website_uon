# Website institucional e CMS — Universidade Onze de Novembro (UON)

Aplicação web integrada (website público + CMS + base de dados MySQL/MariaDB) desenvolvida em
**Laravel 12 / PHP 8.2**, construída a partir da estrutura visual do `website_template.zip`
(Header, Footer, hierarquia e layouts originais) com componentes de painel reutilizados do
`Universidades.ao_2.zip`, e com a identidade cromática do logótipo oficial da UON.

| Documento | Conteúdo |
|---|---|
| [docs/INSTALACAO.md](docs/INSTALACAO.md) | Requisitos, instalação (XAMPP e servidor), configuração, primeiro administrador, tarefas agendadas, actualização, backup e restauro |
| [docs/CMS.md](docs/CMS.md) | Guia de utilização do CMS (perfis, fluxo editorial, módulos) |
| [docs/RELATORIO.md](docs/RELATORIO.md) | Relatório técnico final: arquitectura, templates reutilizados, base de dados, testes executados e pendências |

## Arranque rápido (desenvolvimento)

```bash
composer install
copy .env.example .env          # Linux: cp .env.example .env
php artisan key:generate
# editar .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL
php artisan migrate --seed       # estrutura: funções, páginas do template, menus
php artisan uon:create-admin     # primeiro superadministrador (palavra-passe pedida de forma oculta)
php artisan serve                # http://127.0.0.1:8000  ·  CMS em /admin
```

Conteúdo de demonstração (opcional, marcado como "Exemplo"): `php artisan uon:demo-content`
— remoção: `php artisan uon:demo-content --remove`.
Conteúdo oficial (uon.edu.ao): `php artisan uon:official-content --force`; com exemplos onde o site
oficial não tem informação: `--with-examples` (ver docs/FONTES.md).

Testes: `php artisan test` (50 testes, SQLite em memória) — ver relatório.

## Estrutura

```
app/
  Console/Commands/     uon:create-admin, uon:demo-content, content:publish-scheduled
  Enums/                PublicationStatus (rascunho, revisão, agendado, publicado, arquivado)
  Http/Controllers/
    Site/               website público (páginas, notícias, eventos, cursos, pesquisa, contactos, downloads, SEO)
    Admin/              CMS (um controlador por módulo + Concerns/ManagesContent)
    Auth/               login, logout, recuperação de palavra-passe
  Models/               Eloquent (+ Concerns: HasPublication, HasUniqueSlug, Revisionable)
  Policies/             autorização por módulo (ContentPolicy)
  Services/             MenuRepository, PageModules, SearchService, MediaService, DocumentStorage, ActivityLogger…
  Support/              Settings/SettingsSchema, Blocks, HtmlSanitizer, SafeUrl, VideoEmbed, Permissions
database/migrations     esquema completo (InnoDB, utf8mb4, FKs, índices)
database/seeders        estrutura base + DemoContentSeeder (opcional)
resources/views/
  layouts/              public (template), admin (CMS), auth
  partials/             header.blade.php e footer.blade.php — convertidos do template original
  public/ modules/ blocks/   páginas, listagens e blocos controlados
  admin/ components/admin/   ecrãs e componentes do CMS
public/
  css/                  variables.css, components.css, pages.css, responsive.css (template) + uon.css + admin.css
  js/                   navigation.js (template) + uon.js + admin.js
  vendor/               Bootstrap 5.3.3, Bootstrap Icons 1.11.3, TinyMCE 6.8.5 (auto-alojados)
  uploads/              biblioteca multimédia pública (execução de scripts bloqueada)
storage/app/private/documents   documentos (fora da pasta pública; download por rota autorizada)
reference/              conteúdo extraído dos ZIPs originais (apenas referência; não é servido)
```

Os ficheiros originais (`website_template.zip`, `Universidades.ao_2.zip`, `LOGO_UON_160X100.png`)
foram preservados sem alterações.
