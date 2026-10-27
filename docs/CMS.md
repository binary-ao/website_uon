# Guia do CMS

Acesso: `/admin` (sessão expira após 120 min de inactividade; 5 tentativas falhadas bloqueiam
temporariamente o login).

## Perfis

| Função | Pode |
|---|---|
| Superadministrador | Tudo, incluindo utilizadores, funções/permissões e registo de actividade |
| Administrador | Todos os conteúdos e módulos do website (menus, Header/Footer, configurações, multimédia, redireccionamentos, mensagens) — não gere utilizadores |
| Editor | Criar, editar, rever e publicar páginas, notícias, eventos, documentos, cursos, unidades e galeria; avisos; carregar ficheiros |
| Autor | Criar e editar os **próprios** rascunhos de notícias, eventos e documentos; enviar para revisão |

As permissões de cada função (excepto Superadministrador) são ajustáveis em *Funções e permissões*.
Todas as verificações são feitas no servidor.

## Fluxo editorial

Estados: **Rascunho → Em revisão → Agendado/Publicado → Arquivado**.

- Sem permissão de publicação só é possível gravar como rascunho ou enviar para revisão.
- *Data de publicação* futura = agendamento (fuso Africa/Luanda).
- *Pré-visualizar* mostra o conteúdo com o aspecto do website, sem o publicar (página privada, não indexada).
- Cada gravação de páginas, notícias, eventos e cursos cria uma **revisão** (utilizador, data, tipo);
  é possível comparar e repor uma revisão.
- Eliminar move para a **Reciclagem** (restaurável); a eliminação definitiva é feita a partir daí.
- Alterar o endereço de um conteúdo publicado cria automaticamente um **redireccionamento 301**.

## Páginas e blocos

Cada página tem um **layout** correspondente a um tipo de página do template:

- *Institucional* (texto + blocos) e *Institucional com coluna lateral*;
- módulos: notícias, comunicados, eventos, agenda, galeria, multimédia, oferta formativa
  (opcionalmente filtrada por grau), directório de unidades (por tipo), documentos (por categoria),
  parceiros e contactos.

**Blocos controlados** (acrescentados após o texto): grelha de cartões, directório, cronologia,
acordeão, tabela, organograma, caixa de destaque, texto simples, imagem, vídeo, lista de documentos,
parceiros, painel de acessos externos e chamada à acção. Só aceitam texto simples, URLs validados e
ficheiros da biblioteca — não é possível inserir HTML/scripts nem alterar a estrutura do website.

Páginas marcadas como **Estrutura** pertencem à hierarquia do template: o endereço é fixo e não
podem ser eliminadas (podem ser despublicadas). O painel lista as páginas ainda **por preencher**.

## Menus, Header e Footer

- *Menus*: itens da navegação principal (link, dropdown, mega menu com colunas), da barra superior e
  das três colunas e links inferiores do rodapé. O menu móvel é gerado da mesma árvore. Reordenação
  com os botões ↑/↓. A composição do Header/Footer do template não é alterável.
- *Header, Footer e configurações*: nome, logótipo, favicon, contactos, redes sociais, URLs das
  plataformas de investigação, botão de destaque do Header, textos do rodapé, SEO e página inicial.
  **Campos vazios não aparecem no website** — preencha apenas informação oficial confirmada.

## Biblioteca multimédia

Imagens (JPG, PNG, WEBP, GIF) e documentos (PDF, Office, ODF, TXT/CSV). O tipo é verificado pelo
conteúdo. Preencha o **texto alternativo** das imagens. Ficheiros em uso não podem ser eliminados
(a página do ficheiro mostra onde é usado).

## Documentos

Visibilidade *pública* ou *restrita* (só utilizadores autenticados). *Substituir ficheiro* cria uma
nova versão e mantém as anteriores, que podem ser repostas.

## Conteúdo de demonstração

Registos com o selo **Exemplo** são fictícios e servem apenas para testes. Remover antes da
publicação: `php artisan uon:demo-content --remove`.

## Unidades orgânicas — página própria

Cada unidade publicada tem uma página automática em `/instituicao/unidades-organicas/{endereço}`,
gerada a partir dos dados do módulo *Unidades orgânicas*: apresentação (editor de texto),
imagem, responsável, contactos, departamentos (unidades com "Pertence a" esta unidade) e cursos
(da própria unidade e dos seus departamentos). Editar a unidade actualiza a página. Se indicar uma
*Página institucional da unidade*, os links passam a apontar para essa página do CMS.

## Página inicial

As secções "Sobre a Universidade", "Números institucionais" e "Investigação" seguem a composição do
template e são editadas em *Header, Footer e configurações → Página inicial* (textos, imagem,
destaque numérico, números no formato `+15 | Anos de História`) e *→ Plataformas externas*
(ligações do painel de investigação). Cada secção só aparece quando tem conteúdo.

## Conteúdo fictício de demonstração

`php artisan uon:demo-content` preenche todo o website com dados **fictícios**: configurações e
contactos, página inicial, todas as páginas institucionais, 3 faculdades com departamentos, órgãos
de gestão, 12 cursos com plano curricular, notícias, comunicados, eventos, documentos (PDF), galeria,
vídeos, parceiros e avisos. As imagens são ilustrações geradas com as cores da UON (sem fotografias
de terceiros); os vídeos usam o filme aberto *Big Buck Bunny* (Blender Foundation, CC BY 3.0).

- O selo "Exemplo" pode ser ocultado em *Configurações → SEO*.
- `php artisan uon:demo-content --remove` apaga todos os registos fictícios e **repõe o estado
  original** das páginas e configurações que foram preenchidas.
- `php artisan uon:official-content --force` carrega só o conteúdo recolhido de uon.edu.ao;
  com `--with-examples` mantém exemplos onde o website oficial não tem informação (estado
  actual do website — regras em [FONTES.md](FONTES.md)). Nesse modo, `uon:demo-content --remove`
  remove os exemplos e volta a aplicar o conteúdo oficial.

## Banner da página inicial (carrossel)

*CMS → Banner da página inicial* (permissão "Gerir o banner", atribuída a Administradores e Editores).
Cada diapositivo tem: imagem de fundo (recomendado 1920×800 px), ponto focal, sobretítulo, título e
continuação do título, texto, dois botões (texto + destino) e período de apresentação opcional.
A ordem é definida com ↑/↓. O carrossel roda a cada 7 s, pára ao passar o rato, tem botão de pausa e
não roda automaticamente para quem activou "reduzir movimento". Sem diapositivos activos, a página
inicial mostra o banner simples de *Configurações → Página inicial*.

## Plano curricular e PDF

No curso, cada unidade curricular tem ano, semestre (1.º, 2.º ou anual), código, nome, horas,
créditos e a indicação "optativa". O website apresenta o plano por anos (separadores) e semestres
(cartões com totais) e disponibiliza **Descarregar PDF**, gerado na hora com o logótipo, os dados gerais
do curso (grau, duração, modalidade, regime, unidade orgânica, coordenação, contacto), as tabelas por
semestre e os totais.

## Página da unidade orgânica

Mostra a apresentação, o resumo, os departamentos, **3 cursos** com o botão **Ver todos os cursos**
(oferta formativa filtrada pela unidade, incluindo os cursos dos seus departamentos) e o bloco
**Localização + Contactos** (mapa, morada, telefones separados por "/", email, responsável, horário,
redes sociais). O mapa usa o campo *URL de incorporação do mapa* (Google Maps → Partilhar → Incorporar,
ou OpenStreetMap → Partilhar → HTML). O directório de unidades mostra a imagem de cada unidade.
