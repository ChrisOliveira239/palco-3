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
| 3 | Models Eloquent + relacionamentos + Factories | ✅ concluída — 8 de 8 grupos (`categories`, `venues`, `skills`, `artist_profiles`, `groups`, `group_members`, `events`, `event_sessions`, `event_media` + pivots `event_artist`/`event_group` + `ticket_types`, `tickets` + `sponsorships`, `accepted_support_types` + `follows`, `feed_posts`, `event_reviews` + relação `favorites` sem Model dedicado + `opportunities`, `opportunity_applications` + `notifications`, `reports`) |
| 4 | Autenticação e autorização (usuário, papéis: usuário/artista/grupo/admin) | ✅ concluída — Sanctum token-based (register/login/logout/me) + gate/middleware admin; papéis artista/grupo ficam pra Etapa 5 |
| 5 | API REST — módulo de Usuários e Perfis (artista/grupo) | ✅ concluída — perfil de usuário, perfil de artista, grupo (CRUD) e gestão de membros |
| 6 | API REST — módulo de Eventos (CRUD, sessões, aprovação admin) | 🔄 em andamento — grupo 1 concluído (categoria + evento + aprovação admin); grupo 2 parte 1 concluída (venue + sessões, com endereço livre opcional); grupo 2 parte 2 (mídia + participantes) pendente |
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

- **Enums centralizados em PHP** (✅ superado — ver item abaixo): esteve concluído com 11 enums PHP nativos (`enum ... : string`) em `app/Enums/Type.php` até 2026-07-23. Nessa mesma data foi substituído a pedido do usuário por uma abordagem mais simples (constantes array numa classe `Types`) — ver próximo item, que é o estado atual.
- **`App\Enums\Types` — constantes array em vez de enum nativo** (✅ concluído, 2026-07-23): `app/Enums/Type.php` (11 enums nativos) foi deletado e substituído por `app/Enums/Types.php`, uma classe única com 11 constantes array, valores em UPPERCASE (ex: `Types::TIPO_CONTA = ['PESSOA', 'EMPRESA']`). Motivo: pedido explícito do usuário pra simplificar — sem cast tipado do Eloquent, valor referenciado direto como string literal (ex: `'tic_status' => 'VALIDO'`, não `Types::TICKET_STATUS_VALIDO` — decisão confirmada com o usuário: não criar constante escalar por valor). Os 12 `enum(...)` de coluna MySQL (11 grupos, `sponsorships` tem 2 colunas) também mudaram de minúsculo pra **maiúsculo**, pra bater com as novas constantes — exigiu editar as 11 migrations e rodar `migrate:fresh --seed` de novo. As migrations agora referenciam `Types::CONST` direto (`$table->enum('use_tipo_conta', Types::TIPO_CONTA)`) em vez de repetir o array — isso fecha a parte de migrations do item de backlog abaixo. `notifications.not_tipo` continua fora (string livre, sem `enum(...)` de banco, mesma exceção de sempre). Todos os Models (`casts()`), Factories e testes que referenciavam os enums antigos foram atualizados — `GroupMember`/`AcceptedSupportType` perderam o método `casts()` inteiro (ficou vazio sem o enum). `composer.json` mantém `"classmap": ["app/Enums"]` (não precisa mudar, cobre qualquer classe na pasta).
- **Seeders ainda duplicam array de valores** (📋 registrado, não implementado): diferente das migrations (que agora puxam de `Types::CONST`), os ~10 seeders continuam com arrays de string literal hardcoded (agora em maiúsculo) — decisão deliberada de manter o escopo do refactor de 2026-07-23 restrito a migrations+models (pedido explícito do usuário), sem tocar seeders além do necessário pra sincronizar o case dos valores. Puxar seeders de `Types::CONST` também fica pra uma sessão futura, se fizer sentido.
- **Doação facilitada sem cadastro completo**: repensar `sponsorships.sponsor_type`/`sponsor_id` pra permitir doação com só uma identificação leve (nome/e-mail/telefone), sem exigir conta de `User` completa. Ainda em aberto — precisa decidir se vira um tipo de "sponsor" polimórfico novo (ex: `GuestSponsor`) ou um flag/coluna nullable em `sponsorships` pra dado de contato avulso. Registrado como pendência, não decidido — retomar quando chegar na Etapa 8 (API de Patrocínio).

## Status atual do projeto

**Fase atual:** Etapa 2 concluída por completo (migrations + seeders básicos, 2026-07-21/22). **Etapa 3 (Models Eloquent + relacionamentos + Factories) concluída por completo em 2026-07-23** — 8 de 8 grupos, todas as 27 tabelas de negócio com Model/relacionamentos/Factory. Logo depois, ainda em 2026-07-23, refactor a pedido do usuário: os 11 enums PHP nativos viraram uma classe `Types` com constantes array (ver backlog). **Etapa 4 (Autenticação e autorização) concluída em 2026-07-26** — Sanctum token-based, `AuthController` (register/login/logout/me), gate/middleware de admin (ver detalhe abaixo). **Etapa 5 concluída por completo em 2026-07-28** — grupo 1 (perfil de usuário + perfil de artista) e grupo 2 (grupo + gestão de membros), ver detalhe abaixo. **Etapa 6 grupo 1 (categoria + evento + aprovação admin) concluído em 2026-07-28** — ver detalhe abaixo. **Etapa 6 grupo 2 parte 1 (venue + sessões) concluído em 2026-07-28** — ver detalhe abaixo. Próxima sessão: Etapa 6 grupo 2 parte 2 (mídia + participantes).

Refactor pós-Etapa 3 (2026-07-23): `app/Enums/Type.php` (11 `enum ... : string`) substituído por `app/Enums/Types.php` (classe única, 11 constantes array, ex: `Types::TICKET_STATUS = ['VALIDO', 'USADO', 'CANCELADO']`). Pedido do usuário: sem cast tipado do Eloquent, valor individual referenciado como string literal direto no código (`'tic_status' => 'VALIDO'`), não uma constante escalar por valor. Os 12 `enum(...)` de coluna MySQL mudaram de minúsculo pra maiúsculo pra bater — as 11 migrations envolvidas, os 11 Models (`casts()`), as 9 Factories e os 11 arquivos de teste que referenciavam os enums antigos foram todos atualizados, e os ~10 seeders tiveram os arrays de valores literais sincronizados pra maiúsculo (sem virar `Types::CONST` — fora do escopo pedido, registrado como backlog). `GroupMember`/`AcceptedSupportType` perderam o método `casts()` inteiro (só tinham o enum, ficaria vazio). Migrations agora referenciam `Types::CONST` em vez de repetir o array (`$table->enum('col', Types::CONST)`), fechando a parte de migrations do backlog antigo de duplicação. `composer dump-autoload` + `migrate:fresh --seed` rodados; `php artisan test` continua em 77 testes/110 assertions, sem regressão (só os valores mudaram, a cobertura é a mesma).

Modelagem do banco de dados concluída e documentada em `docs/database-model.md` (inclui decisões registradas sobre patrocinador pessoa/empresa, comentários em avaliações, QR Code por ingresso, busca por raio de distância, DRT/CNPJ e contato de artista/grupo, FK `aprovado_por_id`, morph `organizador` sem prefixo, cartaz de evento, flag `active` de soft delete manual, colisões de prefixo `tic_`/`tit_`, `eve_`/`evr_` e `opp_`/`opa_`, prefixos `spo_`/`ast_`/`fee_`/`not_`/`rep_` sem colisão, e decisões dos seeders).

Backend Laravel 12 instalado em `backend/`, conectado a MySQL local (banco `palco-3`). Migrations criadas e rodadas (27 tabelas no total): `users` (estendida), `categories`, `venues`, `skills` (grupo 1) + `artist_profiles`, `groups`, `group_members`, `artist_profile_skill` (grupo 2) + `events`, `event_sessions`, `event_artist`, `event_group`, `event_media` (grupo 3) + `ticket_types`, `tickets` (grupo 4) + `sponsorships`, `accepted_support_types` (grupo 5) + `follows`, `favorites`, `feed_posts`, `event_reviews` (grupo 6) + `opportunities`, `opportunity_applications` (grupo 7) + `notifications`, `reports` (grupo 8) — seguindo a convenção de prefixo de 3 letras por tabela (ver "Nomenclatura de colunas"), incluindo os ajustes de colisão `gro_`/`grm_`, `eve_`/`evs_`/`evm_`/`evr_`, `tic_`/`tit_` e `opp_`/`opa_`. Flag `active` (boolean, default true) presente em `users`, `categories`, `venues`, `skills`, `artist_profiles`, `groups`, `events`, `event_sessions`, `event_media`, `ticket_types`, `tickets`, `sponsorships`, `feed_posts`, `event_reviews`, `opportunities`, `opportunity_applications`, `notifications`, `reports` — pivots/ligações puras (`group_members`, `artist_profile_skill`, `event_artist`, `event_group`, `favorites`) e as relações/config polimórficas (`accepted_support_types`, `follows`) ficam de fora. `tickets.ticket_type_id`/`tickets.user_id`/`event_reviews.user_id`/`opportunity_applications.user_id`/`reports.denunciante_id` usam `restrictOnDelete` pra proteger histórico; `opportunities.skill_id` usa `nullOnDelete` (campo opcional); `sponsorships`/`accepted_support_types`/`follows`/`feed_posts`/`opportunities`/`reports` usam `$table->morphs()` (sem `constrained()`, são polimórficas).

Seeders básicos criados (`backend/database/seeders/`): 24 classes (uma por tabela, seguindo a mesma ordem dos 8 grupos de migration) + `DatabaseSeeder` orquestrando via `$this->call([...])`. Todas usam `DB::table()->insert()` + `fake()` (query builder puro), exceto `UsersSeeder` que usa `User::factory()` (único Model/Factory existente até agora). Colunas polimórficas gravam o FQCN literal como string (ex: `'App\Models\ArtistProfile'`) — sem morph map configurado ainda, decisão fica pra Etapa 3 se fizer sentido. Populado com `php artisan migrate:fresh --seed`: 20 usuários, 12 eventos, 27 tabelas todas com dados, sem violação de FK/unique, `php artisan test` sem regressão (2/2). Corrigido de passagem um bug pré-existente em `App\Models\User::$fillable` (citava `name`, mas a coluna real é `use_name` desde a migration do grupo 1) — nunca tinha sido exercitado porque os testes padrão do Laravel não tocam o banco.

Etapa 3 grupo 1+2 concluído (2026-07-23): Models `Category`, `Venue`, `Skill`, `ArtistProfile`, `Group`, `GroupMember` (+ Factories correspondentes, exceto `GroupMember` que é pivot) criados em `backend/app/Models/`. `User.php` ganhou relacionamentos novos (`artistProfile()`, `ownedGroups()`, `groups()`) e cast de `use_tipo_conta` pro enum `TipoConta`. Pivots sem coluna extra (`artist_profile_skill`) não têm Model — só `belongsToMany` nos dois lados. `group_members` (tem `id` próprio + `grm_papel` + só `created_at`) exigiu Model `GroupMember extends Pivot` dedicado, com `$timestamps = false` (ver detalhe do bug de `withTimestamps()`/`withPivot()` com timestamp assimétrico em `docs/database-model.md`, seção "Decisões registradas 2026-07-23"). Enums `TipoConta` e `GroupMemberRole` centralizados em `app/Enums/Type.php` (arquivo único, ver item de backlog "Enums centralizados em PHP"). 6 arquivos de teste novos em `tests/Feature/Models/` (14 testes, 19 assertions), `php artisan test` sem regressão.

Etapa 3 grupo 3 concluído (2026-07-23): Models `Event`, `EventSession`, `EventMedia` criados em `backend/app/Models/`, com `Event::organizador()` como `morphTo()` padrão (sem morph map — decisão registrada em `docs/database-model.md`, seção "Decisões registradas 2026-07-23, grupo 3"). Pivots `event_artist`/`event_group` sem Model dedicado (sem coluna extra, mesmo caso de `artist_profile_skill`) — relações `Event::artists()`/`Event::groups()` e as inversas `ArtistProfile::events()`/`Group::events()`. Enums `EventStatus` (`eve_status`) e `EventMediaType` (`evm_tipo`) adicionados em `Type.php`. `EventFactory` usa `ArtistProfile` como organizador default, com estado `porGrupo()` pra testar o outro lado do polimorfismo. 3 arquivos de teste novos (`EventTest`, `EventSessionTest`, `EventMediaTest`), suite completa em 25 testes/35 assertions, sem regressão.

Etapa 3 grupo 4 concluído (2026-07-23): Models `TicketType`, `Ticket` criados em `backend/app/Models/` (+ `ticketTypes()` novo em `EventSession`, `tickets()` novo em `User`). Sem pivot novo neste grupo — `tickets` é FK simples pra `ticket_types`/`users`, não ligação N:N. `TicketType::tit_preco` usa cast `decimal:2` (evita float impreciso em valor monetário). Enum `TicketStatus` (`tic_status`) adicionado em `Type.php` — `composer dump-autoload` rodado antes de testar. `TicketFactory` tem estados `usado()`/`cancelado()`. 2 arquivos de teste novos (`TicketTypeTest`, `TicketTest`), suite completa em 32 testes/44 assertions, sem regressão.

Etapa 3 grupo 5 concluído (2026-07-23): Models `Sponsorship` (`sponsor()`/`alvo()`, ambos `morphTo()` sem morph map) e `AcceptedSupportType` (`alvo()`, `$timestamps = false` — mesmo caso de `GroupMember`) criados em `backend/app/Models/` (+ `sponsorships()` novo em `User`; `sponsorships()`/`acceptedSupportTypes()` novos em `Event`/`ArtistProfile`/`Group`). Nesta sessão também foi pedido adicionar `outro` à lista de tipos de apoio — como é `enum(...)` no nível da coluna MySQL, exigiu editar as 2 migrations (não só o enum PHP) e rodar `migrate:fresh --seed` de novo (projeto ainda em dev). Enums `TipoApoio` (compartilhado entre `spo_tipo_apoio`/`ast_tipo_apoio`, evita duplicar os 11 valores em dois enums) e `SponsorshipStatus` (`spo_status`) adicionados em `Type.php`. `SponsorshipFactory` tem estados `porArtistProfile()`/`porGroup()`/`dinheiro()`. Registrado como backlog (não implementado): migrations/seeders ainda repetem o array de 11 valores manualmente (4 lugares) em vez de puxar de `TipoApoio::cases()` — refactor adiado pra próxima sessão. 2 arquivos de teste novos (`SponsorshipTest`, `AcceptedSupportTypeTest`), suite completa em 42 testes/58 assertions, sem regressão.

Etapa 3 grupo 6 concluído (2026-07-23): Models `Follow` (`user()`/`seguivel()`, `$timestamps = false` — mesmo caso de `GroupMember`/`AcceptedSupportType`), `FeedPost` (`autor()`) e `EventReview` (`event()`/`user()`) criados em `backend/app/Models/`. `favorites` não ganhou Model dedicado — pivot puro (PK composta, sem timestamps, sem coluna própria), mesmo formato de `event_artist`/`event_group`/`artist_profile_skill`: só `belongsToMany` direto (`User::favoriteEvents()`/`Event::favoritedBy()`). `User` ganhou `follows()`, `favoriteEvents()`, `eventReviews()`; `ArtistProfile`/`Group` ganharam `followers()`/`feedPosts()`; `Event` ganhou `followers()`/`favoritedBy()`/`reviews()` (sem `feedPosts()` — seeder confirma que `Event` não é autor de feed post). Enum `FeedPostType` (`fee_tipo`) adicionado em `Type.php`. 4 arquivos de teste novos (`FollowTest`, `FeedPostTest`, `EventReviewTest`, `FavoriteTest`), suite completa em 56 testes/81 assertions, sem regressão. **Nota**: o wrap-up desta sessão marcou o backlog "Enums centralizados em PHP" como concluído por completo, mas isso foi um engano — corrigido no grupo 7 seguinte (faltava `opp_status`/`opa_status`, e ainda falta `rep_status` do grupo 8).

Etapa 3 grupo 7 concluído (2026-07-23): Models `Opportunity` (`criador()` morphTo — `ArtistProfile`/`Group`, sem `Event`, confirmado pelo seeder —, `skill()` belongsTo, `applications()` hasMany) e `OpportunityApplication` (`opportunity()`/`user()` belongsTo) criados em `backend/app/Models/`. `ArtistProfile`/`Group` ganharam `opportunities()` (`morphMany` via `criador`); `Skill` ganhou `opportunities()` (`hasMany` — não tinha nenhuma relação com `Opportunity` antes); `User` ganhou `opportunityApplications()` (`hasMany`). Enums `OpportunityStatus` (`opp_status`) e `OpportunityApplicationStatus` (`opa_status`) adicionados em `Type.php` — fecham a lacuna deixada pelo wrap-up do grupo 6, só falta `rep_status` (grupo 8) agora. `OpportunityFactory` tem estado `porGroup()`; `OpportunityApplicationFactory` tem estados `aceita()`/`recusada()` (mesmo padrão de `usado()`/`cancelado()` da `TicketFactory`). 2 arquivos de teste novos (`OpportunityTest`, `OpportunityApplicationTest`), suite completa em 67 testes/97 assertions, sem regressão.

Etapa 3 grupo 8 concluído (2026-07-23) — **último grupo, Etapa 3 encerrada**: Models `Notification` (`user()`) e `Report` (`denunciante()` belongsTo, `alvo()` morphTo — `Event`/`ArtistProfile`/`Group`/`FeedPost`, sem `User`, confirmado pelo seeder) criados em `backend/app/Models/`. `User` ganhou `notifications()` e `reports()` (denúncias feitas, via `denunciante_id`); `Event`/`ArtistProfile`/`Group`/`FeedPost` ganharam `reports()` (`morphMany` via `alvo`, denúncias recebidas). Decisão consciente: `not_tipo` fica `string` livre — não é `enum(...)` de banco, então não virou enum PHP (diferente de `rep_status`, que é enum de banco de verdade). Enum `ReportStatus` (`rep_status`) adicionado em `Type.php` — **fecha o backlog "Enums centralizados em PHP" de vez** (o grupo 6 tinha fechado cedo demais, o grupo 7 corrigiu parcialmente, este grupo fecha o resto). `ReportFactory` tem estados `porArtistProfile()`/`porGroup()`/`porFeedPost()`. 2 arquivos de teste novos (`NotificationTest`, `ReportTest`), suite completa em 77 testes/110 assertions, sem regressão. Próxima sessão: Etapa 4 (Autenticação e autorização).

Etapa 4 concluída (2026-07-26): instalado `laravel/sanctum` (token-based/Bearer, não SPA cookie — decisão confirmada com o usuário: sem frontend ainda, mobile futuro usa o mesmo mecanismo). `User.php` ganhou trait `HasApiTokens` e método `isAdmin()` (wrapper de `use_is_admin`, já existente desde a migration original). `routes/api.php` criado e ligado em `bootstrap/app.php` (`withRouting(api: ...)`) com 4 endpoints: `POST /api/register`, `POST /api/login` (públicos), `POST /api/logout`, `GET /api/me` (via `auth:sanctum`). `AuthController` (`app/Http/Controllers/Auth/`) com 4 ações finas, sem Service dedicado (one-liners, abaixo do limiar que justificaria). `RegisterRequest`/`LoginRequest` (`app/Http/Requests/Auth/`) — `RegisterRequest` valida `use_name`/`email`/`password` (`confirmed`) e `use_tipo_conta` opcional via `Rule::in(Types::TIPO_CONTA)`; **`use_is_admin` deliberadamente fora das regras de validação** — `validated()` descarta o campo mesmo se enviado no payload, única barreira contra escalonamento de privilégio num endpoint público (coberto por teste de regressão `test_register_ignores_use_is_admin_input`). Autorização de admin em 3 peças: `User::isAdmin()`, `Gate::define('admin', ...)` em `AppServiceProvider::boot()`, middleware `EnsureUserIsAdmin` (alias `admin` registrado em `bootstrap/app.php`) — sem rota real ainda pra proteger (só a partir da Etapa 6, aprovação de evento), testado via rota ad-hoc registrada inline no teste. Papéis artista/grupo (`isArtist()`, `isGroupOwner()` etc.) deliberadamente **não** criados nesta etapa — decisão confirmada com o usuário, ficam pra Etapa 5 quando os endpoints de perfil existirem de fato pra proteger. 5 arquivos de teste novos em `tests/Feature/Auth/` (`RegisterTest`, `LoginTest`, `LogoutTest`, `MeTest`, `AdminGateTest`, 18 testes/36 assertions) — um ajuste no meio do caminho: o teste de revogação de token inicialmente reusava o mesmo token num segundo request HTTP simulado dentro do mesmo método de teste e falhava (esperava 401, recebia 200) por causa de um artefato de teste do Laravel (guard do Sanctum cacheia o usuário resolvido em memória por chamada, e múltiplas chamadas HTTP simuladas dentro do mesmo método de teste compartilham a mesma instância de aplicação — não acontece em produção, onde cada request é um processo novo); corrigido verificando diretamente no banco (`assertDatabaseMissing`) que o token foi removido, sem depender de um segundo request simulado. `composer dump-autoload` + `php artisan migrate` (aditivo, só `personal_access_tokens` — sem `migrate:fresh`) + `php artisan test`: suite completa em **95 testes/146 assertions**, sem regressão.

Etapa 5 grupo 1/2 concluído (2026-07-28): primeiros endpoints de perfil da API. `User` ganhou `isArtist()` (`return $this->artistProfile()->exists();`) — mesmo padrão de `isAdmin()`. Criada a primeira Policy do projeto, `ArtistProfilePolicy` (`app/Policies/`) — `update`/`delete` liberados só pro dono (`$user->id === $artistProfile->user_id`); descoberta automática por convenção do Laravel 12 (`App\Models\ArtistProfile` → `App\Policies\ArtistProfilePolicy`), sem registro manual. Isso exigiu devolver o trait `AuthorizesRequests` ao `Controller` base (`app/Http/Controllers/Controller.php`), removido por padrão desde o Laravel 11 — necessário pra `$this->authorize()` funcionar nos controllers. Dois controllers novos: `ProfileController` (`PATCH /api/profile`, atualiza dados de perfil do próprio usuário autenticado) e `ArtistProfileController` (`GET /api/artist-profiles`, `GET /api/artist-profiles/{id}`, públicos; `POST/PATCH/DELETE /api/artist-profiles/{id}`, autenticados). `DELETE` é soft-delete (`art_active = false`), não `Model::delete()`. Três Form Requests novas (`app/Http/Requests/`, fora da subpasta `Auth/` — são de perfil, não de autenticação): `UpdateProfileRequest` (`use_name`/`use_bio`/`use_avatar_url`/`use_city`/`use_state`/`use_latitude`/`use_longitude`, todos `sometimes` — email/senha/`use_is_admin`/`use_tipo_conta`/`use_active` de fora, mesmo racional de segurança do `RegisterRequest`), `StoreArtistProfileRequest` (`authorize()` retorna `! $user->isArtist()` — nega com 403 se o usuário já tem perfil, antes mesmo de checar campos) e `UpdateArtistProfileRequest`. **`art_verificado` fora das regras nas duas** — mesmo padrão do `use_is_admin`: selo de verificação não é auto-atribuível pelo próprio artista (fica pra um endpoint admin futuro), coberto por teste de regressão (`test_store_ignores_art_verificado`). Índice/exibição de perfil inativo (`art_active = false`) retornam 404 pra qualquer requisitante, inclusive o próprio dono — simplificação consciente pra não precisar de autenticação opcional numa rota pública; revisitar só se surgir necessidade real. `isGroupOwner()`/`isGroupAdmin()` deliberadamente **não** criados ainda — ficam pro grupo 2, junto com os endpoints de `Group`/`GroupMember` que vão de fato consumi-los. **Decisão de permissão de grupo confirmada com o usuário agora, pra implementar no grupo 2**: dono (`user_id`) OU membro com `grm_papel = ADMIN` podem editar o grupo e gerenciar membros; soft-delete do grupo (`gro_active = false`) é exclusivo do dono. 2 arquivos de teste novos em `tests/Feature/Profiles/` (`UserProfileTest`, `ArtistProfileTest` — nome de pasta novo pra não colidir com `tests/Feature/Models/ArtistProfileTest`, que testa o Model, não a API; 15 testes/30 assertions). `composer dump-autoload` + `php artisan test` (sem migration nova — schema já existe desde a Etapa 3): suite completa em **110 testes/176 assertions**, sem regressão.

Etapa 5 grupo 2/2 concluído (2026-07-28) — **fecha a Etapa 5 por completo**: `User` ganhou `isGroupOwner(Group $group)` (compara `user_id`) e `isGroupAdmin(Group $group)` (`$group->members()->wherePivot('user_id', $this->id)->wherePivot('grm_papel', 'ADMIN')->exists()`). `GroupPolicy` (`app/Policies/`) — `update`/`manageMembers` liberados pra dono OU membro `ADMIN`; `delete` só dono, decisão confirmada com o usuário na sessão do grupo 1. `manageMembers` é habilidade customizada (não uma das 7 padrão do Laravel), consumida pelas 3 ações de `GroupMemberController` que mexem em membro de terceiro. `GroupController` espelha `ArtistProfileController` (índice/exibição públicos filtrando `gro_active`, `destroy` é soft-delete). `GroupMemberController` separado (SRP — gestão de membro é responsabilidade distinta de CRUD do grupo): `store`/`update`/`destroy` de membro, com uma exceção deliberada — `destroy` libera **auto-remoção** (sair do grupo) sem exigir `manageMembers`, só exige a permissão pra remover *outra* pessoa; `update` (trocar papel) não tem essa exceção, ninguém promove a si mesmo. `AddGroupMemberRequest` usa `Rule::unique('group_members')->where(...)` escopado ao grupo da rota, pra virar 422 tratável em vez da unique constraint do banco estourar 500. Rotas novas: `GET/POST/PATCH/DELETE /api/groups`(`/{id}`), `GET/POST/PATCH/DELETE /api/groups/{id}/members`(`/{user}`).

**Bug real encontrado e corrigido nesta sessão** (não era esperado pelo plano, descoberto ao testar `update` de papel de membro): `GroupMember::updateExistingPivot()` (usado pela troca de papel) quebrava com `SQLSTATE... no such column: updated_at`, mesmo a tabela `group_members` nunca tendo tido essa coluna e o Model já declarando `public $timestamps = false` desde a Etapa 3. Causa raiz, em duas camadas: (1) `Pivot::fromRawAttributes()` (chamado internamente por `updateExistingPivot()` pra reidratar a linha antes de salvar) **ignora** `$timestamps = false` do Model e força `$instance->timestamps = true` sempre que a linha bruta trouxer uma chave `created_at` — que `group_members` sempre traz; (2) uma vez forçado pra `true`, `Pivot::getUpdatedAtColumn()` não usa a própria classe do pivot — delega pro **model pai da relação** (`Group`, que tem `updated_at` de verdade), então nem `const UPDATED_AT = null` no `GroupMember` teria efeito (tentativa intermediária que não resolveu, descartada). Fix real: sobrescrever o hook `GroupMember::hasTimestampAttributes()` pra sempre retornar `false` — é exatamente esse método que o `fromRawAttributes()`/`fromAttributes()` chama pra decidir o override, então sobrescrevê-lo impede o problema na raiz, cobrindo qualquer caminho de hidratação futuro (não só `updateExistingPivot()`). `attach()` (grupo 1/Etapa 3) nunca esbarrou nisso por não passar pelo `save()` do Model. 2 arquivos de teste novos em `tests/Feature/Groups/` (`GroupTest`, `GroupMemberTest` — nomes novos de pasta, sem colidir com `tests/Feature/Models/GroupTest.php`; 23 testes/41 assertions — foi o teste `test_owner_can_update_member_role` que expôs o bug). `composer dump-autoload` + `php artisan test` (sem migration nova): suite completa em **133 testes/217 assertions**, sem regressão.

Etapa 6 grupo 1 concluído (2026-07-28): primeiros endpoints de `Category` e `Event`. Duas decisões de fluxo confirmadas com o usuário antes de implementar: (1) criar evento manda direto pra `eve_status = PENDENTE` (sem rascunho editável nem endpoint de submit — `RASCUNHO`/`APROVADO` do enum ficam reservados, sem uso nesta etapa); (2) admin aprova → vira `PUBLICADO` direto (aprovar e publicar são a mesma ação); admin rejeita → `REJEITADO`; se o organizador editar um evento rejeitado, `EventController::update()` reverte automaticamente pra `PENDENTE`. `CategoryController` (`GET /api/categories` público; `POST/PATCH/DELETE` só admin) é o **primeiro consumidor real do middleware `admin`** (alias criado na Etapa 4, nunca usado até agora) — sem Policy, protegido só pela rota (`Route::middleware('admin')`, aninhada dentro de `auth:sanctum`). `cat_slug` é sempre derivado server-side de `cat_nome` via `Str::slug()`, nunca aceito do cliente; `cat_nome` tem `unique` na validação (evita colisão de slug). `EventController` (`GET /api/events`(`/{id}`) públicos; `POST/PATCH/DELETE` autenticado; `PATCH .../approve`(`/reject`) admin) + `EventPolicy` (`update`/`delete` liberados só pro organizador — reaproveita `isGroupOwner()`/`isGroupAdmin()` da Etapa 5 quando `organizador_type = Group`). Organizador só pode ser `ArtistProfile` (o próprio perfil do usuário) ou `Group` (dono ou membro `ADMIN`) — `User` como organizador existe no schema/doc mas nunca foi usado em factory/teste, ficou de fora. Cliente manda `organizador_type` como string curta (`'artist_profile'`/`'group'`), não a FQCN direto — `StoreEventRequest::authorize()` já valida posse (perfil próprio, ou grupo onde é dono/admin) antes mesmo das regras de campo, e o controller mapeia pra `ArtistProfile::class`/`Group::class` na hora de criar. `organizador_type`/`organizador_id`/`eve_status`/`eve_active`/`aprovado_por_id`/`eve_aprovado_em` de fora do `UpdateEventRequest` — mudam só por ação dedicada (aprovar/rejeitar/desativar), nunca por update livre. **Simplificação consciente, mesmo padrão do grupo 1 da Etapa 5**: `index`/`show` de evento só mostram `PUBLICADO`+ativo pra qualquer requisitante, inclusive o próprio organizador (sem "meus eventos" nem autenticação opcional numa rota pública) — organizador ainda cria/edita/desativa via rota autenticada normalmente, só não conseque reconsultar via `GET` um evento próprio ainda pendente/rejeitado; revisitar só se virar necessidade real. 3 arquivos de teste novos em `tests/Feature/Events/` (`CategoryTest`, `EventTest`, `EventApprovalTest`; 27 testes/53 assertions). `composer dump-autoload` + `php artisan test` (sem migration nova): suite completa em **160 testes/270 assertions**, sem regressão.

Etapa 6 grupo 2 parte 1 concluído (2026-07-28): `Venue` (local de evento) e `EventSession` (data/horário + local de cada sessão de um evento). `venues` não tem `user_id` — sem conceito de dono, diferente de `ArtistProfile`/`Group` — então `VenueController` (`GET /venues`(`/{id}`) público; `POST/PATCH/DELETE` admin-only) espelha exatamente `CategoryController`: sem Policy, protegido só pelo middleware `admin` já reaproveitado da Etapa 6 grupo 1. **Decisão nova confirmada com o usuário**: `Venue` cadastrado não é obrigatório numa sessão — o organizador pode informar um endereço livre próprio (`evs_endereco`/`evs_cidade`/`evs_estado`, todos string) em vez de depender de um `Venue` admin-aprovado, porque exigir aprovação de admin pra todo local novo travaria o fluxo de criação de sessão. Endereço livre é **estruturado** (não um texto único) pra manter filtro por cidade funcionando mesmo sem `Venue`; não tem lat/long — busca por raio de distância continua exclusiva de `Venue` cadastrado. Migration `event_sessions` (já rodada) editada — `venue_id` virou `nullable()` e ganhou 4 colunas novas (`evs_local_nome` apelido opcional, `evs_endereco`, `evs_cidade`, `evs_estado`) — exigiu `migrate:fresh --seed` de novo, mesmo precedente de sempre pra schema em dev. `StoreEventSessionRequest` usa `required_without:venue_id` nos 3 campos de endereço livre — cobre a regra "venue OU endereço livre" sem validação customizada. `EventSessionController` é rota aninhada sob `Event` (`/events/{event}/sessions`), autorizado via ability nova `EventPolicy::manageSessions` (mesmo padrão de `GroupPolicy::manageMembers` — autoriza contra o pai, sem Policy própria pra `EventSession`); update/destroy também conferem `$session->event_id === $event->id` antes de agir, devolvendo 404 (não 403) se a sessão pertence a outro evento — mesmo padrão do `GroupMemberController` (autorização contra o pai é ortogonal a "esse filho pertence a esse pai"). **Achado corrigido durante os testes**: `ven_latitude`/`ven_longitude` são `NOT NULL` no schema desde a Etapa 3 (toda `Venue` real sempre tem coordenadas, pensado pra busca por raio) — a primeira versão do `StoreVenueRequest` os deixava `nullable`, quebrando a criação em teste; corrigido pra `required`, já que só o endereço livre (sem `Venue`) dispensa coordenadas. 2 arquivos de teste novos (`tests/Feature/Venues/VenueTest.php`, `tests/Feature/Events/EventSessionTest.php`; 20 testes/41 assertions). `composer dump-autoload` + `migrate:fresh --seed` + `php artisan test`: suite completa em **180 testes/311 assertions**, sem regressão.

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
