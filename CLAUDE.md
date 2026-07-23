# CLAUDE.md

Este arquivo fornece contexto para o Claude (via Claude Code ou chat) trabalhar neste projeto de forma consistente entre sessões.

## Visão do Projeto

Nome do projeto : Palco

Plataforma de cultura local que vai além da venda de ingressos: funciona como uma **rede social voltada para cultura**, conectando artistas, grupos, produtores, público e patrocinadores.

Mais do que um "site de eventos", o objetivo é fortalecer a cena cultural local — descoberta de eventos, divulgação de artistas/grupos, comunidades, patrocínio (financeiro e material) e conexão entre profissionais da área.

## Problemas que o projeto resolve

- Dificuldade em descobrir eventos culturais próximos
- Divulgação fragmentada (hoje concentrada em redes sociais genéricas)
- Baixa visibilidade para artistas, grupos e produtores independentes
- Falta de ferramentas para fortalecer a comunidade cultural local
- Dificuldade em encontrar profissionais da área (atores, músicos, técnicos, fotógrafos etc.)
- Dificuldade em conseguir patrocínio financeiro ou material
- Alto custo das plataformas tradicionais de venda de ingressos

## Objetivos principais

- Centralizar a divulgação de eventos culturais
- Facilitar descoberta por cidade, região ou proximidade
- Aproximar artistas, grupos, produtores e público
- Incentivar apoio financeiro/material à cultura
- Facilitar a organização de eventos
- Fortalecer comunidades culturais locais

## Stack Técnica

| Camada | Tecnologia |
|---|---|
| Backend | Laravel |
| Frontend | React (PWA) |
| UI / Componentes | shadcn/ui + Tailwind CSS |
| Mobile | React Native (futuro) |
| Banco de dados | MySQL |
| API | REST |
| Storage | Object Storage (S3 ou equivalente) |
| Testes de backend | PHPUnit |
| Testes de API | Feature tests do Laravel (PHPUnit) |
| Testes de frontend | Jest |
| Outros serviços | Mail Provider, Notification Provider, Payment Provider, CDN, Queue/Jobs |

## Estrutura do Repositório (monorepo)

```
/
├── backend/          # API Laravel (instalado — Laravel 12, MySQL)
├── frontend/          # React (PWA) — ainda não iniciado
├── mobile/            # React Native (futuro, ainda não iniciado)
├── docs/              # Documentação, specs, diagramas
└── CLAUDE.md
```

## Domínio (entidades principais)

`Usuário`, `Artista`, `Grupo`, `Evento`, `Local`, `Categoria`, `Competência`, `Patrocínio`, `Ingresso`, `Feed`, `Oportunidade`, `Administrador`

### Resumo das regras de negócio por entidade

**Administrador** — gerencia todas as entidades, aprova eventos antes da publicação, modera conteúdo, gerencia usuários/categorias/competências/locais/denúncias, visualiza métricas.

**Artista** — cria perfil artístico, publica portfólio/fotos/vídeos, cadastra competências, publica e organiza eventos, publica atualizações, participa de grupos, recebe seguidores/notificações/patrocínios, cria oportunidades.

**Grupo** — identidade própria, adiciona membros, organiza eventos, publica atualizações/fotos/vídeos/portfólio, recebe seguidores e patrocínios, cria oportunidades.

**Evento** — tem artistas e grupos participantes, patrocinadores, galeria, links externos; vende ingressos pela plataforma ou redireciona para venda externa; tem lista de presença e QR Code de validação; recebe avaliações e (futuramente) comentários.

**Usuário** — compra ingressos, segue artistas/grupos/eventos, favorita eventos, cria agenda pessoal, compartilha e demonstra interesse em eventos, patrocina artistas/grupos/eventos, recebe recomendações personalizadas.

**Patrocínio** — suporte financeiro ou material (dinheiro, equipamentos, figurino, alimentação, transporte, hospedagem, fotografia, filmagem, iluminação, som). Cada organizador define quais tipos aceita.

**Oportunidade** — marketplace para conectar profissionais da cultura (ex: "procura-se ator", "procura-se músico", "procura-se fotógrafo").

## Funcionalidades principais

- Descoberta de eventos por cidade, raio de distância, categoria e artistas/grupos seguidos
- Feed com novidades culturais
- Agenda pessoal do usuário
- Notificações inteligentes
- Ranking de patrocinadores e de artistas/grupos mais seguidos
- Venda de ingressos (própria ou via link externo) com taxas acessíveis
- Cadastro de eventos gratuitos

## Diferenciais competitivos

- Rede social exclusiva para cultura (não é só um marketplace de ingressos)
- Descoberta inteligente e por localização
- Marketplace de oportunidades culturais
- Sistema de patrocínio financeiro e material

## Convenções de código e qualidade

### Clean Code e S.O.L.I.D.

- Seguir princípios de Clean Code: nomes descritivos, funções pequenas e com uma única responsabilidade, evitar duplicação, evitar comentários que só repetem o que o código já diz.
- Aplicar S.O.L.I.D. sempre que fizer sentido para o contexto Laravel/React (não forçar abstração desnecessária em código simples):
  - **S** — Controllers e Services enxutos, cada um com uma responsabilidade (ex: `TicketPurchaseService` não deve também enviar e-mail e gerar relatório).
  - **O** — preferir extensão (novas classes/estratégias) a alterar código existente que já funciona, especialmente em regras de negócio (ex: tipos de patrocínio).
  - **L** — subclasses/implementações devem poder substituir a base sem quebrar contrato (ex: diferentes `NotificationChannel`).
  - **I** — interfaces pequenas e específicas em vez de uma única interface genérica "faz tudo".
  - **D** — depender de abstrações (interfaces/contracts do Laravel) em vez de classes concretas, especialmente para integrações externas (pagamento, storage, notificação).

### Frontend: reuso de componentes

Antes de criar qualquer componente novo:
1. Buscar no repositório (`frontend/src/components` ou equivalente) se já existe um componente igual ou similar.
2. Se existir e estiver quase bom o suficiente → **atualizar/estender** o componente existente em vez de duplicar.
3. Só criar um componente novo quando nada existente atender, ou quando adaptar o existente comprometer sua responsabilidade original.
4. Priorizar componentes do **shadcn/ui** como base antes de construir do zero — customizar via Tailwind em vez de reescrever do zero.

### Testes

- **Backend**: PHPUnit — cobrir regras de negócio críticas (compra de ingresso, aprovação de evento, patrocínio) com testes unitários dos Services/Models.
- **API**: Feature tests do Laravel (PHPUnit) — cobrir os principais endpoints REST (autenticação, CRUD de eventos, compra de ingresso, patrocínio, oportunidades).
- **Frontend**: Jest — cobrir componentes com lógica (formulários, listas com filtro, fluxo de compra) e funções utilitárias.
- Toda funcionalidade nova relevante (não trivial) deve vir acompanhada de teste correspondente, mesmo que básico.

### Nomenclatura de colunas (tabelas)

- Colunas de negócio de cada tabela recebem prefixo de 3 letras derivado do **nome da tabela SQL** (inglês, como escrito na migration) — ex: `users` → `use_`, `categories` → `cat_`, `venues` → `ven_`, `skills` → `ski_`.
- Se duas tabelas gerarem o mesmo prefixo de 3 letras, ajustar uma letra pra manter unicidade — decidir a variação no momento de criar aquela migration e registrar aqui. Já aconteceu com `groups` → `gro_` e `group_members` → `grm_`; com o cluster `events`/`event_sessions`/`event_media`/`event_reviews` → `eve`/`evs`/`evm`/`evr`; com `tickets` → `tic_`/`ticket_types` → `tit_`; e com `opportunities` → `opp_`/`opportunity_applications` → `opa_`. `notifications` (`not_`) e `reports` (`rep_`) fecharam sem colisão.
- **Não prefixar**: `id`, `created_at`/`updated_at`, chaves estrangeiras (`*_id`, seguem o padrão Eloquent `{tabela_singular}_id`), e colunas reservadas do Laravel Auth (`email`, `password`, `remember_token`, `email_verified_at`) — evita quebrar `UserProvider`, notificações, reset de senha etc.
- Exemplo: tabela `users`, coluna de negócio `name` → `use_name`; `bio` → `use_bio`.
- **Soft delete manual**: toda tabela "principal" (não pivot/ligação N:N) recebe uma coluna `{prefixo}_active` (boolean, default `true`) — ex: `use_active`, `eve_active`. Pivots/ligações puras (`group_members`, `artist_profile_skill`, `event_artist`, `event_group`) não recebem, porque desfazer a ligação é exclusão de verdade, não soft delete. Decisão tomada no grupo 3 (2026-07-22), retroaplicada nas tabelas dos grupos 1 e 2 enquanto o projeto estava em dev. Mesma exceção estendida em 2026-07-22 (grupo 5) a `accepted_support_types` — não é pivot N:N clássico (é config polimórfica), mas o mesmo racional se aplica: remover um tipo aceito é exclusão de verdade.

### Outras convenções (a definir conforme o projeto avança)

- [ ] Padrão de nomenclatura de branches
- [ ] Padrão de commits (ex: Conventional Commits)
- [x] Comandos de setup/build/test do backend — `cd backend && composer install`, `php artisan migrate --seed` (ou `migrate:fresh --seed` do zero), `php artisan test`
- [ ] Comandos de setup/build/test do frontend
- [ ] Linter/formatter usados (ex: Pint no Laravel, ESLint/Prettier no React)

## Metodologia de trabalho (sessões de desenvolvimento)

- No início de cada sessão de trabalho, o Claude propõe uma **"etapa do dia"**: um recorte pequeno e concluível do roadmap abaixo (não a etapa inteira, se ela for grande) — ex: "hoje: migrations de usuários, artistas e grupos" em vez de "hoje: todo o backend".
- Ao concluir o que foi proposto para a etapa do dia, o Claude sinaliza explicitamente **"já podemos parar por aqui"**, resume o que foi feito e o que fica para a próxima sessão.
- Se o usuário quiser continuar além do ponto de parada sugerido, o Claude segue normalmente — o aviso é um checkpoint, não um limite rígido.
- Etapas maiores do roadmap são quebradas em várias "etapas do dia" menores conforme o trabalho avança — a quebra exata é decidida na hora, olhando o que já foi feito.

## Explicações sob demanda

Quando o usuário pedir uma explicação (ex: "explica essa decisão", "por que assim?", "como isso funciona?"), o Claude deve:
- Explicar o racional técnico da decisão/código (trade-offs, alternativas consideradas, por que essa opção em vez de outra).
- Usar exemplos concretos do próprio projeto sempre que possível (não só teoria genérica).
- Não pressupor que pedir explicação significa que algo está errado — é parte natural do processo de aprendizado no projeto.

## Roadmap do projeto (desde o início)

| Etapa | Escopo | Status |
|---|---|---|
| 0 | Setup do projeto (estrutura de pastas, Laravel + React instalados, banco configurado) | ✅ backend (Laravel 12 + MySQL) instalado em 2026-07-21; frontend ainda pendente |
| 1 | Modelagem do banco de dados (entidades, relacionamentos, ER, decisões de schema) | ✅ concluída — ver `docs/database-model.md` |
| 2 | Migrations Laravel + seeders básicos | ✅ concluída — migrations dos 8 grupos (27 tabelas: `users`, `categories`, `venues`, `skills`, `artist_profiles`, `groups`, `group_members`, `artist_profile_skill`, `events`, `event_sessions`, `event_artist`, `event_group`, `event_media`, `ticket_types`, `tickets`, `sponsorships`, `accepted_support_types`, `follows`, `favorites`, `feed_posts`, `event_reviews`, `opportunities`, `opportunity_applications`, `notifications`, `reports`, + `cache`/`jobs` do Laravel) + 24 seeders básicos (um por tabela, via `DB::table()->insert()` + `fake()`, exceto `users` que usa `User::factory()`) chamados por `DatabaseSeeder` |
| 3 | Models Eloquent + relacionamentos + Factories | 🔄 em andamento — grupo 1+2+3+4+5+6 de 8 concluído (`categories`, `venues`, `skills`, `artist_profiles`, `groups`, `group_members`, `events`, `event_sessions`, `event_media` + pivots `event_artist`/`event_group` + `ticket_types`, `tickets` + `sponsorships`, `accepted_support_types` + `follows`, `feed_posts`, `event_reviews` + relação `favorites` sem Model dedicado) |
| 4 | Autenticação e autorização (usuário, papéis: usuário/artista/grupo/admin) | Pendente |
| 5 | API REST — módulo de Usuários e Perfis (artista/grupo) | Pendente |
| 6 | API REST — módulo de Eventos (CRUD, sessões, aprovação admin) | Pendente |
| 7 | API REST — módulo de Ingressos (tipos, compra, QR Code) | Pendente |
| 8 | API REST — módulo de Patrocínio | Pendente |
| 9 | API REST — módulo de Oportunidades | Pendente |
| 10 | API REST — Feed, seguidores, favoritos, notificações | Pendente |
| 11 | Testes de backend e de API (PHPUnit) cobrindo os módulos acima | Contínuo, junto com cada módulo |
| 12 | Setup do frontend (React PWA + shadcn/ui + Tailwind) | Pendente |
| 13 | Frontend — autenticação e perfil | Pendente |
| 14 | Frontend — descoberta de eventos (cidade, raio, categoria, feed) | Pendente |
| 15 | Frontend — fluxo de compra de ingresso | Pendente |
| 16 | Frontend — patrocínio e oportunidades | Pendente |
| 17 | Testes de frontend (Jest) | Contínuo, junto com cada tela |
| 18 | Deploy / infraestrutura (S3, filas, e-mail, pagamento) | Pendente |

> Este roadmap é vivo — etapas podem ser reordenadas ou quebradas em subetapas menores conforme o projeto avança.

## Backlog de ideias (planejamento futuro, não implementar ainda)

- **Enums centralizados em PHP** (✅ concluído): todos os `enum(...)` das migrations viraram PHP nativo (`enum`) em `app/Enums/Type.php` — arquivo único pra todos os enums do projeto, valores em UPPERCASE (ex: `RASCUNHO`, `PENDENTE`, `APROVADO`). Lista final: `TipoConta` (`users.use_tipo_conta`), `GroupMemberRole` (`group_members.grm_papel`), `EventStatus` (`events.eve_status`), `EventMediaType` (`event_media.evm_tipo`), `TicketStatus` (`tickets.tic_status`), `TipoApoio` (compartilhado entre `sponsorships.spo_tipo_apoio` e `accepted_support_types.ast_tipo_apoio` — 11 valores, incluindo `outro` adicionado no grupo 5), `SponsorshipStatus` (`sponsorships.spo_status`) e `FeedPostType` (`feed_posts.fee_tipo`, último item, fechado no grupo 6), aplicados nos Models da Etapa 3 grupo 1+2, grupo 3, grupo 4, grupo 5 e grupo 6. Como múltiplas classes num arquivo só quebram PSR-4, `composer.json` ganhou `"classmap": ["app/Enums"]` (rodar `composer dump-autoload` ao adicionar um enum novo nesse arquivo — sem isso os testes falham com `Class ... not found`, já aconteceu no grupo 3 e é sempre o primeiro passo depois de editar `Type.php`).
- **Refatorar migrations/seeders pra puxar valores de enum de `Type.php`** (📋 registrado, não implementado): hoje o array de 11 valores de `TipoApoio` está duplicado em 4 lugares (2 migrations + 2 seeders) além do enum PHP — idealmente migrations/seeders puxariam de `TipoApoio::cases()` direto, evitando repetição. Adiado no grupo 5 pra não acoplar a mudança pontual (adicionar `outro`) a um refactor maior; fica pra uma sessão futura.
- **Doação facilitada sem cadastro completo**: repensar `sponsorships.sponsor_type`/`sponsor_id` pra permitir doação com só uma identificação leve (nome/e-mail/telefone), sem exigir conta de `User` completa. Ainda em aberto — precisa decidir se vira um tipo de "sponsor" polimórfico novo (ex: `GuestSponsor`) ou um flag/coluna nullable em `sponsorships` pra dado de contato avulso. Registrado como pendência, não decidido — retomar quando chegar na Etapa 8 (API de Patrocínio).

## Status atual do projeto

**Fase atual:** Etapa 2 concluída por completo (migrations + seeders básicos, 2026-07-21/22). Etapa 3 (Models Eloquent + relacionamentos + Factories) em andamento — grupo 1+2+3+4+5+6 de 8 concluído em 2026-07-23. Próxima sessão: Etapa 3 grupo 7 (`opportunities`, `opportunity_applications`).

Modelagem do banco de dados concluída e documentada em `docs/database-model.md` (inclui decisões registradas sobre patrocinador pessoa/empresa, comentários em avaliações, QR Code por ingresso, busca por raio de distância, DRT/CNPJ e contato de artista/grupo, FK `aprovado_por_id`, morph `organizador` sem prefixo, cartaz de evento, flag `active` de soft delete manual, colisões de prefixo `tic_`/`tit_`, `eve_`/`evr_` e `opp_`/`opa_`, prefixos `spo_`/`ast_`/`fee_`/`not_`/`rep_` sem colisão, e decisões dos seeders).

Backend Laravel 12 instalado em `backend/`, conectado a MySQL local (banco `palco-3`). Migrations criadas e rodadas (27 tabelas no total): `users` (estendida), `categories`, `venues`, `skills` (grupo 1) + `artist_profiles`, `groups`, `group_members`, `artist_profile_skill` (grupo 2) + `events`, `event_sessions`, `event_artist`, `event_group`, `event_media` (grupo 3) + `ticket_types`, `tickets` (grupo 4) + `sponsorships`, `accepted_support_types` (grupo 5) + `follows`, `favorites`, `feed_posts`, `event_reviews` (grupo 6) + `opportunities`, `opportunity_applications` (grupo 7) + `notifications`, `reports` (grupo 8) — seguindo a convenção de prefixo de 3 letras por tabela (ver "Nomenclatura de colunas"), incluindo os ajustes de colisão `gro_`/`grm_`, `eve_`/`evs_`/`evm_`/`evr_`, `tic_`/`tit_` e `opp_`/`opa_`. Flag `active` (boolean, default true) presente em `users`, `categories`, `venues`, `skills`, `artist_profiles`, `groups`, `events`, `event_sessions`, `event_media`, `ticket_types`, `tickets`, `sponsorships`, `feed_posts`, `event_reviews`, `opportunities`, `opportunity_applications`, `notifications`, `reports` — pivots/ligações puras (`group_members`, `artist_profile_skill`, `event_artist`, `event_group`, `favorites`) e as relações/config polimórficas (`accepted_support_types`, `follows`) ficam de fora. `tickets.ticket_type_id`/`tickets.user_id`/`event_reviews.user_id`/`opportunity_applications.user_id`/`reports.denunciante_id` usam `restrictOnDelete` pra proteger histórico; `opportunities.skill_id` usa `nullOnDelete` (campo opcional); `sponsorships`/`accepted_support_types`/`follows`/`feed_posts`/`opportunities`/`reports` usam `$table->morphs()` (sem `constrained()`, são polimórficas).

Seeders básicos criados (`backend/database/seeders/`): 24 classes (uma por tabela, seguindo a mesma ordem dos 8 grupos de migration) + `DatabaseSeeder` orquestrando via `$this->call([...])`. Todas usam `DB::table()->insert()` + `fake()` (query builder puro), exceto `UsersSeeder` que usa `User::factory()` (único Model/Factory existente até agora). Colunas polimórficas gravam o FQCN literal como string (ex: `'App\Models\ArtistProfile'`) — sem morph map configurado ainda, decisão fica pra Etapa 3 se fizer sentido. Populado com `php artisan migrate:fresh --seed`: 20 usuários, 12 eventos, 27 tabelas todas com dados, sem violação de FK/unique, `php artisan test` sem regressão (2/2). Corrigido de passagem um bug pré-existente em `App\Models\User::$fillable` (citava `name`, mas a coluna real é `use_name` desde a migration do grupo 1) — nunca tinha sido exercitado porque os testes padrão do Laravel não tocam o banco.

Etapa 3 grupo 1+2 concluído (2026-07-23): Models `Category`, `Venue`, `Skill`, `ArtistProfile`, `Group`, `GroupMember` (+ Factories correspondentes, exceto `GroupMember` que é pivot) criados em `backend/app/Models/`. `User.php` ganhou relacionamentos novos (`artistProfile()`, `ownedGroups()`, `groups()`) e cast de `use_tipo_conta` pro enum `TipoConta`. Pivots sem coluna extra (`artist_profile_skill`) não têm Model — só `belongsToMany` nos dois lados. `group_members` (tem `id` próprio + `grm_papel` + só `created_at`) exigiu Model `GroupMember extends Pivot` dedicado, com `$timestamps = false` (ver detalhe do bug de `withTimestamps()`/`withPivot()` com timestamp assimétrico em `docs/database-model.md`, seção "Decisões registradas 2026-07-23"). Enums `TipoConta` e `GroupMemberRole` centralizados em `app/Enums/Type.php` (arquivo único, ver item de backlog "Enums centralizados em PHP"). 6 arquivos de teste novos em `tests/Feature/Models/` (14 testes, 19 assertions), `php artisan test` sem regressão.

Etapa 3 grupo 3 concluído (2026-07-23): Models `Event`, `EventSession`, `EventMedia` criados em `backend/app/Models/`, com `Event::organizador()` como `morphTo()` padrão (sem morph map — decisão registrada em `docs/database-model.md`, seção "Decisões registradas 2026-07-23, grupo 3"). Pivots `event_artist`/`event_group` sem Model dedicado (sem coluna extra, mesmo caso de `artist_profile_skill`) — relações `Event::artists()`/`Event::groups()` e as inversas `ArtistProfile::events()`/`Group::events()`. Enums `EventStatus` (`eve_status`) e `EventMediaType` (`evm_tipo`) adicionados em `Type.php`. `EventFactory` usa `ArtistProfile` como organizador default, com estado `porGrupo()` pra testar o outro lado do polimorfismo. 3 arquivos de teste novos (`EventTest`, `EventSessionTest`, `EventMediaTest`), suite completa em 25 testes/35 assertions, sem regressão.

Etapa 3 grupo 4 concluído (2026-07-23): Models `TicketType`, `Ticket` criados em `backend/app/Models/` (+ `ticketTypes()` novo em `EventSession`, `tickets()` novo em `User`). Sem pivot novo neste grupo — `tickets` é FK simples pra `ticket_types`/`users`, não ligação N:N. `TicketType::tit_preco` usa cast `decimal:2` (evita float impreciso em valor monetário). Enum `TicketStatus` (`tic_status`) adicionado em `Type.php` — `composer dump-autoload` rodado antes de testar. `TicketFactory` tem estados `usado()`/`cancelado()`. 2 arquivos de teste novos (`TicketTypeTest`, `TicketTest`), suite completa em 32 testes/44 assertions, sem regressão.

Etapa 3 grupo 5 concluído (2026-07-23): Models `Sponsorship` (`sponsor()`/`alvo()`, ambos `morphTo()` sem morph map) e `AcceptedSupportType` (`alvo()`, `$timestamps = false` — mesmo caso de `GroupMember`) criados em `backend/app/Models/` (+ `sponsorships()` novo em `User`; `sponsorships()`/`acceptedSupportTypes()` novos em `Event`/`ArtistProfile`/`Group`). Nesta sessão também foi pedido adicionar `outro` à lista de tipos de apoio — como é `enum(...)` no nível da coluna MySQL, exigiu editar as 2 migrations (não só o enum PHP) e rodar `migrate:fresh --seed` de novo (projeto ainda em dev). Enums `TipoApoio` (compartilhado entre `spo_tipo_apoio`/`ast_tipo_apoio`, evita duplicar os 11 valores em dois enums) e `SponsorshipStatus` (`spo_status`) adicionados em `Type.php`. `SponsorshipFactory` tem estados `porArtistProfile()`/`porGroup()`/`dinheiro()`. Registrado como backlog (não implementado): migrations/seeders ainda repetem o array de 11 valores manualmente (4 lugares) em vez de puxar de `TipoApoio::cases()` — refactor adiado pra próxima sessão. 2 arquivos de teste novos (`SponsorshipTest`, `AcceptedSupportTypeTest`), suite completa em 42 testes/58 assertions, sem regressão.

Etapa 3 grupo 6 concluído (2026-07-23): Models `Follow` (`user()`/`seguivel()`, `$timestamps = false` — mesmo caso de `GroupMember`/`AcceptedSupportType`), `FeedPost` (`autor()`) e `EventReview` (`event()`/`user()`) criados em `backend/app/Models/`. `favorites` não ganhou Model dedicado — pivot puro (PK composta, sem timestamps, sem coluna própria), mesmo formato de `event_artist`/`event_group`/`artist_profile_skill`: só `belongsToMany` direto (`User::favoriteEvents()`/`Event::favoritedBy()`). `User` ganhou `follows()`, `favoriteEvents()`, `eventReviews()`; `ArtistProfile`/`Group` ganharam `followers()`/`feedPosts()`; `Event` ganhou `followers()`/`favoritedBy()`/`reviews()` (sem `feedPosts()` — seeder confirma que `Event` não é autor de feed post). Enum `FeedPostType` (`fee_tipo`) adicionado em `Type.php` — **fecha o backlog "Enums centralizados em PHP" por completo**. `FollowFactory` tem estados `porGroup()`/`porEvent()`; `FeedPostFactory` tem `porGroup()`. 4 arquivos de teste novos (`FollowTest`, `FeedPostTest`, `EventReviewTest`, `FavoriteTest`), suite completa em 56 testes/81 assertions, sem regressão. Próxima sessão: Etapa 3 grupo 7 (`opportunities`, `opportunity_applications`).

## Como o Claude deve ajudar neste projeto

- Sempre considerar o domínio e as entidades acima como fonte de verdade do escopo.
- Antes de sugerir uma nova tabela/campo, verificar se já existe algo equivalente na lista de domínio.
- Seguir a stack definida (Laravel + MySQL + REST + React PWA + shadcn/ui + Tailwind) — não sugerir troca de stack sem justificativa clara.
- Priorizar simplicidade no MVP; funcionalidades marcadas como "futuro" (mobile, comentários) não são prioridade agora.
- Seguir Clean Code e S.O.L.I.D. sempre que fizer sentido para o contexto (ver seção "Convenções de código e qualidade").
- No frontend, nunca criar um componente novo sem antes verificar se já existe um reaproveitável — atualizar/estender o existente sempre que possível.
- Toda funcionalidade não trivial deve vir com teste correspondente (PHPUnit no backend/API, Jest no frontend).
- Propor uma "etapa do dia" no início de cada sessão e sinalizar "já podemos parar por aqui" ao concluir o escopo proposto.
- Quando o usuário pedir uma explicação, aprofundar o racional técnico com exemplos do próprio projeto.
