# Relatório técnico — Website e CMS da Universidade Onze de Novembro

Data: 14/10/2026 · Ambiente: Windows 11 Pro, XAMPP (Apache, PHP 8.2.12, MariaDB 10.4.32)

## 1. Auditoria inicial

| Ficheiro | Resultado |
|---|---|
| `website_template.zip` (94 ficheiros) | Template estático HTML5/CSS3/JS + Bootstrap 5.3.3 + Bootstrap Icons 1.11.3; fontes Montserrat/Lora locais; 46 páginas em 7 tipos (A Landing, B Institucional, C Listagem, D Detalhe, E Directório, F Galeria, G Contactos); Header = topbar + navbar + mega menu + dropdowns + pesquisa + CTA + offcanvas; Footer = 5 colunas + barra inferior. Marca de demonstração "Instituto Superior Exemplo"/Binary.ao. |
| `Universidades.ao_2.zip` (34 ficheiros) | Portal estático com a mesma versão de Bootstrap (sem conflito). Reutilizados os componentes de painel e formulários; Header/Footer **não** usados. |
| `LOGO_UON_160X100.png` | 714×342 px, PNG transparente. Cores medidas: vermelho `#FF0000`, cinzento-escuro `#2E2C2C`, preto, amarelo `#F3E800`, branco (e verde nos louros). |

Não existia aplicação no projecto. Os ZIPs foram extraídos para `reference/` sem alterar os originais.

## 2. Tecnologias

Laravel 12.70 · PHP 8.2 · MySQL/MariaDB (InnoDB, utf8mb4) · Blade · barryvdh/laravel-dompdf 3 (PDF do plano curricular) · Bootstrap 5.3.3 · Bootstrap Icons 1.11.3
· TinyMCE 6.8.5 (licença MIT, auto-alojado, interface pt_PT) · spatie/laravel-permission 6 ·
symfony/html-sanitizer 7 · intervention/image 3 (GD). Sem Vite/build step: o CSS/JS do template é
servido tal como está; as dependências de frontend estão em `public/vendor` (sem CDN).

## 3. Integração dos templates

**Template principal (estrutura obrigatória)**
- Header e Footer convertidos 1:1 de `components/header.html` e `footer.html` para
  `resources/views/partials/` — mesmas classes, ordem, mega menu (colunas 4/4/4 e 6/6), dropdowns,
  pesquisa em flyout, CTA, offcanvas móvel em acordeão, coluna de plataformas externas, barra inferior
  e botão "voltar ao topo". Os conteúdos passam a vir do CMS.
- CSS original reutilizado (`variables.css`, `components.css`, `pages.css`, `responsive.css`) e JS
  `navigation.js`. Alterações: só **valores de cor** (os nomes dos tokens mantêm-se) e quatro
  gradientes azulados substituídos por neutros quentes.
- Todos os tipos de página foram mapeados para layouts do CMS (ver §5).
- Removidos por serem demonstrativos ou substituídos pelo backend: `config.js`/`main.js` (configuração
  agora na BD), `components.js` (filtros agora no servidor), `forms.js` (formulário agora real).

**Template complementar (componentes)** — em `public/css/admin.css`: `.d-wrap/.d-nav/.d-stat/.d-box`
(painel), `.s-badge` (estados), `.u-card/.u-form`, `.u-upload`, `.pc-table` (plano curricular),
`.g-grid/.g-item` (selector multimédia), `.a-wrap/.a-side/.a-main` (login), `.u-iconbtn`, `.r-empty`,
`.cf-actions`; e no website o padrão de lightbox (`u-lightbox`). Tipografia unificada (Montserrat)
para evitar duas famílias de letra e dependência do Google Fonts.

**Ajustes justificados**
- Logótipo: a UON usa um logótipo ≈2,1:1 (o de demonstração era ≈4,2:1). A altura passou de 42 px
  para 64 px (46 px em mobile, 68 px no rodapé) para manter a legibilidade, sem alterar posição,
  proporção nem a altura do header.
- Padrão gráfico `pattern-white.png` (circuitos, da marca de demonstração) mantido como elemento
  decorativo do template; pode ser substituído por um padrão institucional mantendo o nome do ficheiro.
- Rótulos adaptados ao contexto universitário: "Sobre a Universidade", "Unidades Orgânicas".

## 4. Identidade visual

Tokens em `public/css/variables.css`: `--uon-red #FF0000` (uso gráfico), `--primary-600 #D10000`
(texto/botões sobre branco, contraste 5,6:1 AA), `--primary-700 #A30000`, `--primary #FF3838`
(sobre fundos escuros, AA), `--ink #1D1B1B` (derivado de `#2E2C2C`), `--uon-accent #F3E800`
(uso pontual), neutros quentes e cores semânticas; aliases `--uon-primary`, `--uon-secondary`,
`--uon-accent`, `--uon-text`, `--uon-muted`, `--uon-background`, `--uon-surface`, `--uon-border`,
`--uon-success/warning/danger/info`. Contentor global `.site-shell` a 100% da largura do ecrã (fundos de header, secções e footer de
ponta a ponta); o conteúdo fica em `.container-xl` (máx. 1440 px) com espaçamento lateral
`--site-gutter: clamp(16px, 4vw, 48px)`, para nunca tocar nas bordas. Cores não editáveis no CMS (centralizadas no CSS).

## 5. Website público (rotas)

| Secção | Páginas (layout) |
|---|---|
| Início | `/` — hero, avisos, apresentação, oferta formativa, números, notícias, investigação, eventos, CTA, parceiros (cada secção só aparece com conteúdo real) |
| Instituição | `/instituicao` (cartões) · sobre (coluna lateral) · historia · missao-visao-valores · mensagem-direccao · orgaos-gestao (directório) · estrutura-organizacional · unidades-organicas (directório) · servicos · documentos (lista) · parceiros |
| Ensino | `/ensino` · oferta-formativa · licenciaturas · mestrados · pos-graduacoes (cursos com filtros) · departamentos · calendario-academico · regulamentos (lista) · informacoes-academicas · detalhe `/ensino/cursos/{slug}` |
| Investigação | `/investigacao` (painel de acessos externos configuráveis) |
| Extensão | `/extensao` · projectos · cooperacao (parceiros) · protocolos (lista) · mobilidade |
| Comunicação | `/comunicacao` · noticias (+detalhe) · eventos (próximos/anteriores, +detalhe) · comunicados (+detalhe) · agenda · galeria (lightbox) · multimedia |
| Serviços | `/servicos` · academicos · biblioteca · apoio-estudante · administrativos · documentos |
| Outras | `/contactos` (formulário real), `/pesquisa`, `/politica-de-privacidade`, `/termos-de-utilizacao`, `/acessibilidade`, `/sitemap.xml`, `/robots.txt`, `/documentos/{slug}/download`, páginas de erro 404/403/419/429/500/503 |

Funcionalidades: pesquisa global paginada com filtro por tipo e realce; filtros e paginação no
servidor; breadcrumbs; meta title/description, canonical, Open Graph, JSON-LD
(CollegeOrUniversity, NewsArticle, Event); `noindex` em pesquisa, pré-visualizações e CMS; estados
vazios; "Conteúdo em preparação" em páginas sem texto; redireccionamentos 301 automáticos.

## 6. CMS — módulos

Painel (indicadores reais, pendentes de revisão, recentes, páginas por preencher, actividade) ·
Páginas (hierarquia, layouts, blocos, documentos associados, duplicar, revisões, reciclagem) ·
Notícias e comunicados (categorias, etiquetas, destaque, agendamento, anexos) · Eventos (datas/horas,
local, inscrição) · Documentos (versões, visibilidade, download autorizado) · Galeria e multimédia
(imagens e vídeos YouTube/Vimeo) · Biblioteca multimédia (upload seguro, texto alternativo,
utilização, eliminação controlada) · Cursos (plano curricular, saídas profissionais) · Unidades
orgânicas · Graus académicos · Menus (Header, topbar, 3 colunas + barra inferior do Footer, regras
de composição) · Header, Footer e configurações (8 grupos) · Avisos · Parceiros · Categorias ·
Etiquetas · Redireccionamentos · Mensagens de contacto · Utilizadores · Funções e permissões ·
Registo de actividade · Perfil e alteração de palavra-passe · Pré-visualização privada.

## 7. Base de dados (28 tabelas de aplicação)

`users`, `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`,
`pages`, `revisions` (polimórfica: páginas, notícias, eventos, cursos), `news`, `news_tag`, `tags`,
`categories` (por tipo), `events`, `documents`, `document_versions`, `documentables`, `media`,
`organic_units`, `degrees`, `courses`, `course_subjects`, `menus`, `menu_items`, `site_settings`,
`redirects`, `activity_logs`, `contact_messages`, `notices`, `partners`, `gallery_items`
(+ `sessions`, `cache`, `jobs`, `password_reset_tokens`, `migrations`).
Chaves estrangeiras com `restrict`/`cascade`/`set null` conforme o caso, índices em estado/data,
slugs únicos (incluindo registos na reciclagem), exclusão lógica (`deleted_at`), operações
compostas em transacções (páginas, cursos + plano, documentos + versões, menus).

## 8. Segurança

Autorização no servidor (middleware `can:` + policies por módulo); protecção contra escalada
(só superadministrador gere utilizadores/funções; ninguém altera a própria função; último
superadministrador protegido; `$fillable` restrito); bcrypt; política de palavras-passe (10+,
maiúsculas, minúsculas, números); limitação de tentativas (5/min por conta, 20/10 min por IP);
sessões cifradas na BD e invalidadas ao desactivar contas/alterar palavra-passe; CSRF; sanitização
HTML no servidor (Symfony HtmlSanitizer); blocos só com texto simples; validação de URLs (bloqueia
`javascript:`, `data:`, `//host`); uploads validados pelo conteúdo (fileinfo + lista branca),
imagens re-codificadas, nomes aleatórios, SVG/HTML/PHP rejeitados, execução de scripts bloqueada em
`public/uploads`; documentos fora da pasta pública; cabeçalhos `X-Frame-Options`,
`X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, HSTS em HTTPS; honeypot e
limite no formulário de contacto; registo de auditoria. **Correcção crítica aplicada:** o Apache do
XAMPP servia a raiz do projecto (`/uon_edu/.env` devolvia 200); o `.htaccess` da raiz bloqueia agora
segredos/pastas internas (verificado: 403) e redirecciona para `public/`.

## 9. Testes executados

**Automatizados — `php artisan test`: 58 testes, 584 asserções, todos aprovados**, executados em
SQLite (memória) **e** em MariaDB 10.4 (`uon_website_test`).

- Website (16): composição do Header/Footer do template e ordem dos menus; todos os ~40 destinos de
  menu devolvem 200; plataformas externas ocultas sem URL; rascunho/agendado/arquivado não públicos e
  agendamento sem cron; paginação, filtro por categoria e pesquisa nas notícias; eventos
  próximos/anteriores; cursos por grau e detalhe com plano; pesquisa global com filtro; formulário de
  contacto (validação, gravação, honeypot); 404 e redireccionamentos; redirect automático ao mudar
  slug; sitemap/robots/noindex; regras de download (público/restrito/rascunho); cabeçalhos e escaping
  XSS; estado "em preparação"; configurações reflectidas no Header/Footer.
- Autenticação (9): protecção das rotas, login/logout, bloqueio por tentativas, contas inactivas,
  utilizador sem função, recuperação de palavra-passe, alteração de palavra-passe, `noindex`,
  comando `uon:create-admin`.
- CMS (15): 43 ecrãs abrem para superadministrador; CRUD de páginas com sanitização de HTML e
  blocos; slugs/caminhos reservados/ciclos; páginas estruturais; reciclagem/restauro/duplicação;
  notícias com etiquetas, agendamento, pré-visualização e comando de publicação; permissões de
  autor e editor; escalada de privilégios e *mass assignment*; uploads (PHP disfarçado, SVG, HTML
  rejeitados; redimensionamento) e eliminação controlada; documentos com versões e armazenamento
  privado; cursos com plano curricular; regras de composição dos menus e URLs perigosos;
  validação das configurações; indicadores do painel.
- Base de dados (10): tabelas criadas; seeders idempotentes e sem contas por omissão; unicidade de
  slugs; FKs restrict/cascade; categorias em uso; consultas com dados vazios; arquivados; validadores.

**Manuais/visuais (Chrome headless):** comparação lado a lado do Header, Footer e página inicial com
o template original a 1440 px; mobile (390/360 px) da página inicial, notícias e login; medição de
overflow horizontal em 11 páginas a 360 px (scrollWidth = clientWidth em todas); ecrãs do CMS
renderizados (painel, edição de página, menus). Verificação HTTP de 25+ rotas públicas e da
protecção do Apache/XAMPP. Os testes de pré-visualização visual do CMS foram feitos a partir de HTML
renderizado num teste isolado (não foi criado nenhum atalho de autenticação).

**Não executado:** envio real de email (requer SMTP — o código está testado com *fake*); testes em
Safari/Firefox e leitores de ecrã; testes de carga.

## 10. Dados institucionais e pendências

Só o nome "Universidade Onze de Novembro"/"UON" e o logótipo foram usados como dados oficiais.
**Por fornecer/validar pela UON** (campos já existentes no CMS, ocultos enquanto vazios): contactos,
morada, horário, mapa, redes sociais, URLs de investigação/revistas, texto institucional do rodapé e
da página inicial, números institucionais, conteúdos das 23 páginas institucionais ("por preencher"
no painel), órgãos de gestão, unidades orgânicas, cursos, documentos oficiais, parceiros, favicon
quadrado e imagens.

A base de desenvolvimento `uon_website` está preenchida com **conteúdo fictício completo** (pedido do
responsável), marcado no CMS e com o selo "Exemplo"; a remoção (`php artisan uon:demo-content --remove`)
repõe páginas e configurações ao estado original. Foram acrescentadas páginas automáticas para as
unidades orgânicas (`/instituicao/unidades-organicas/{slug}`).

Melhorias possíveis: padrão gráfico institucional para substituir `pattern-white.png`; CSP
estrita (exige remover os poucos `onchange` inline); autenticação de dois factores; fila de emails.
