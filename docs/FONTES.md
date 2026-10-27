# Fontes do conteúdo institucional

Conteúdo carregado com `php artisan uon:official-content` (seeder `OfficialContentSeeder`).

**Método.** O website oficial https://uon.edu.ao bloqueia acessos automatizados (página de
verificação anti-bot "One moment, please…"). Essa protecção não foi contornada. A informação foi
recolhida dos **excertos das páginas oficiais indexadas pelos motores de pesquisa** (consulta de
22/10/2026). Alguns textos podem ter sido resumidos pelos índices: **a UON deve validar todos os
dados abaixo no CMS antes da publicação.**

| Dado | Valor carregado | Página de origem |
|---|---|---|
| Nome | Universidade 11 de Novembro (UON) | título das páginas de uon.edu.ao |
| Email geral | geral@uon.edu.ao | https://uon.edu.ao/contact/ |
| Telefone | (+244) 928 220 700 | https://uon.edu.ao/contact/ |
| Morada | Rua Comendador Henrique Serrano, Cabinda, Angola | https://uon.edu.ao/contact/ |
| Criação | Decreto Presidencial n.º 7/09, de 12 de Maio; sede em Cabinda; presença académica no Zaire | https://uon.edu.ao/sobre-a-uon/ |
| Reorganização | Decreto Presidencial n.º 285/20, de 29 de Outubro (autonomização da Escola Superior Politécnica de M'banza-Kongo) | https://uon.edu.ao/sobre-a-uon/ |
| Missão | Formar profissionais qualificados, promover a investigação científica e a extensão universitária em diversas áreas, contribuindo para o desenvolvimento técnico-científico e para a soberania nacional | https://uon.edu.ao/faqs/missao-visao-valores/ e /sobre-a-uon/ (duas redacções ligeiramente diferentes) |
| Visão | Ser reconhecida até 2030 como universidade de excelência no país e com prestígio internacional | https://uon.edu.ao/faqs/missao-visao-valores/ |
| Valores | Responsabilidade, honestidade, patriotismo, solidariedade, humanismo, laboriosidade, honradez e justiça | https://uon.edu.ao/faqs/missao-visao-valores/ |
| Reitor | Prof. Doutor Kianvu Tamo | notícia do campus do Caio (uon.edu.ao); também cabinda.gov.ao (2023). A Wikipédia indica outro nome (desactualizado). **Validar.** |
| Faculdade de Direito | Rua Comendador Henrique Serrano, Bairro Marien Ngouabi, Cidade de Cabinda · (+244) 231 220 491 · f.direito@uon.edu.ao | https://uon.edu.ao/faculdade-de-direito/ |
| Faculdade de Medicina | f.medicina@uon.edu.ao · 2 cursos · +64 docentes | https://uon.edu.ao/faculdade-de-medicina-2/ |
| Faculdade de Economia | cursos (ver abaixo); **contactos não encontrados** | páginas dos cursos; notícia do concurso público |
| ISP-Cabinda | Rua das Mangueiras, Bairro 4 de Fevereiro, Cidade de Cabinda · +244 972 329 758 · ispcabinda@uon.edu.ao | https://uon.edu.ao/instituto-politecnico-2/ |
| Cursos — FD | Direito (licenciatura, 5 anos, diurno, 6.000 h); Ciências Jurídico-Forenses (mestrado, 2 anos, diurno); Ciências Jurídico-Políticas (mestrado; sem detalhes) | /faculdade-de-direito/, /cursos/direito/, /cursos/ciencias-juridico-forenses/ |
| Cursos — FE | Gestão de Empresas (licenciatura, 4 anos); Contabilidade e Auditoria (mestrado, 2 anos); Economia e Gestão (mestrado, 2 anos) | /cursos/gestao-de-empresas/, /cursos/contabilidade-e-auditoria/, /cursos/economia-e-gestao/ |
| Cursos — FM | Medicina Geral (licenciatura, 6 anos, diurno); Saúde Pública (mestrado, 2 anos) | /faculdade-de-medicina-2/ |
| Cursos — ISP | Enfermagem, Análises Clínicas, Psicologia Clínica (licenciaturas, 4 anos); Engenharia Florestal (licenciatura, 5 anos, diurno); Enfermagem Médico-Cirúrgica (mestrado, 2 anos) | /instituto-politecnico-2/, /cursos/enfermagem/, /cursos/analises-clinicas/ |
| Candidaturas | Exame de Acesso 2026/2027 — Portal de Candidatura https://www.candidaturas.sga-ed.info/uon | https://uon.edu.ao/exame-de-acesso-2026-2027/ |
| Notícia | Abertura do Concurso Público de Ingresso Externo e Interno (23/06/2026) | https://uon.edu.ao/abertura-do-concurso-publico-de-ingresso-externo-e-interno/ |
| Notícia | Jornada Científica «África o Nosso Continente», edição 2026 (28/05/2026) | https://uon.edu.ao/africa-numa-globalizacao-vacilante/ |
| Notícia | Segunda fase da construção do campus do Caio, conclusão em Nov. 2027 (21/05/2026) | https://uon.edu.ao/segunda-fase-da-construcao-do-campus-universitario-do-caio-que-teve-inicio-esta-quinta-feira-fica-concluida-em-novembro-de-2027/ |
| Notícia / parceiro | Parceria com a Okutala — saúde ocular dos funcionários (10/05/2026) | https://uon.edu.ao/noticias/ |

## O que **não** foi carregado (não encontrado ou não confirmado)

Planos curriculares; textos completos das notícias (o website mostra um resumo e o link para a
publicação original); eventos (só datas sem títulos); documentos oficiais; fotografias (as imagens
são ilustrações marcadas como "Imagem ilustrativa" na biblioteca); decanos/directores; contactos da
Faculdade de Economia; redes sociais oficiais; números institucionais; mensagem do Reitor; órgãos de
gestão além da Reitoria; departamentos. Estas páginas mostram "Conteúdo em preparação" até serem
preenchidas no CMS.

## Conteúdo misto (oficial + exemplos)

`php artisan uon:official-content --force --with-examples` (seeder `MixedContentSeeder`) carrega o
conteúdo oficial acima e mantém **exemplos fictícios apenas onde o website oficial não tem
informação**:

- **Listas que o site oficial publica** (unidades orgânicas, cursos, notícias, banner): só os itens
  oficiais. Os campos em falta nesses itens (decanos/directores, contactos da FE, modalidade,
  objectivos, saídas profissionais, planos curriculares, imagens) são completados com exemplos
  assinalados com "(exemplo)" ou com uma nota "Plano curricular de exemplo".
- **O que o site oficial não publica** (eventos, documentos, galeria, vídeos, parceiros além da
  Okutala, departamentos, órgãos de gestão além da Reitoria, números de estudantes/docentes, redes
  sociais, plataformas de investigação, páginas sem texto oficial): exemplos (`is_demo = 1`).
- **Exemplos que contradizem dados oficiais** (faculdades e cursos inventados, notícias fictícias,
  datas históricas, nome do Reitor) não são usados. Os departamentos de exemplo compatíveis foram
  associados às unidades oficiais (Direito → FD, Economia e Gestão → FE, Saúde Pública → FM,
  Enfermagem → ISP-Cabinda).

`php artisan uon:demo-content --remove --force` remove os exemplos, limpa os campos de exemplo dos
registos oficiais e volta a aplicar o conteúdo oficial.

A data de publicação do comunicado "Exame de Acesso 2026/2027" não estava disponível: foi usada a
data da importação.
