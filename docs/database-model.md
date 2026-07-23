# Modelagem do Banco de Dados

Status: **rascunho em revisão** — validar com o time antes de gerar as migrations definitivas.

Convenções: nomes de tabela em `snake_case`, plural. Chaves primárias `id` (bigint, auto increment). Timestamps `created_at`/`updated_at` em todas as tabelas, salvo indicação contrária.

---

## 1. Usuários e perfis

### `users`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| name | string | |
| email | string | unique |
| password | string | |
| avatar_url | string | nullable |
| bio | text | nullable |
| city | string | nullable |
| state | string | nullable |
| latitude | decimal(10,7) | nullable, para descoberta por raio |
| longitude | decimal(10,7) | nullable |
| is_admin | boolean | default false — administrador é um papel do usuário, não entidade separada |
| tipo_conta | enum(pessoa, empresa) | default pessoa — permite empresa patrocinadora sem criar entidade `companies` separada (decisão provisória, revisar se demanda de empresas crescer) |
| email_verified_at | timestamp | nullable |
| active | boolean | default true — soft delete manual (flag, não `deleted_at` do Laravel) |

### `artist_profiles`
Perfil artístico opcional de um usuário (1 usuário → no máximo 1 perfil de artista).

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK → users | unique (garante 1:0..1) |
| nome_artistico | string | |
| bio | text | nullable |
| capa_url | string | nullable |
| verificado | boolean | default false |
| drt | string | nullable — registro profissional de artista, opcional; quando preenchido, exibe selo de verificação no perfil (implementação de frontend futura) |
| telefone | string | nullable — contato público, independente do email de login |
| email | string | nullable — idem |
| site | string | nullable |
| active | boolean | default true — soft delete manual |

### `groups`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| nome | string | |
| descricao | text | nullable |
| avatar_url | string | nullable |
| capa_url | string | nullable |
| cnpj | string | nullable — grupo pode ser informal (sem CNPJ) ou pessoa jurídica |
| telefone | string | nullable — contato público do grupo |
| email | string | nullable — idem |
| site | string | nullable |
| user_id | bigint FK → users | dono/criador do grupo (nomeado `user_id` para seguir padrão Eloquent `{tabela_singular}_id`) |
| active | boolean | default true — soft delete manual |

### `group_members` (pivot)
| Campo | Tipo | Notas |
|---|---|---|
| group_id | bigint FK → groups | |
| user_id | bigint FK → users | |
| papel | enum(admin, membro) | default membro |
| created_at | timestamp | |

Sem `active`: desfazer a ligação (membro saiu do grupo) é exclusão de verdade, não soft delete.

### `skills` (competências)
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| nome | string | ex: guitarrista, iluminador, fotógrafo |
| active | boolean | default true — soft delete manual |

### `artist_profile_skill` (pivot N:N)
| artist_profile_id | bigint FK | |
| skill_id | bigint FK | |

Sem `active`: pivot N:N puro, remoção de competência é exclusão de verdade.

---

## 2. Eventos, locais e categorias

### `categories`
| id | bigint PK |
| nome | string |
| slug | string unique |
| icone | string nullable |
| active | boolean default true — soft delete manual |

### `venues` (locais)
| id | bigint PK |
| nome | string |
| endereco | string |
| cidade | string |
| estado | string |
| latitude | decimal(10,7) |
| longitude | decimal(10,7) |
| active | boolean default true — soft delete manual |

### `events`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| titulo | string | |
| descricao | text | |
| category_id | bigint FK → categories | `restrictOnDelete` — categoria em uso não pode ser apagada |
| organizador_type / organizador_id | string / bigint | polimórfico (`$table->morphs('organizador')`): `App\Models\User`, `ArtistProfile` ou `Group`; sem prefixo, tratado como equivalente a FK |
| status | enum(rascunho, pendente, aprovado, rejeitado, publicado) | default rascunho — fluxo de aprovação do admin |
| gratuito | boolean | default false |
| cartaz_url | string | nullable — URL do cartaz/flyer do evento |
| links_externos | json | nullable — links de venda externa, redes sociais |
| aprovado_por_id | bigint FK → users | nullable, `nullOnDelete` — admin que aprovou (renomeado de `aprovado_por` pra terminar em `_id` e ficar isento de prefixo) |
| aprovado_em | timestamp | nullable |
| active | boolean | default true — soft delete manual |

### `event_sessions` (sessões: data + local)
Resolve o caso de 1 evento com múltiplas datas/locais.

| id | bigint PK |
| event_id | bigint FK → events, `cascadeOnDelete` |
| venue_id | bigint FK → venues, `restrictOnDelete` |
| data_inicio | datetime — inclui hora |
| data_fim | datetime nullable — inclui hora |
| active | boolean default true — soft delete manual |

### `event_artist` (pivot N:N — artistas participantes)
| event_id | bigint FK |
| artist_profile_id | bigint FK |

Sem `active`: remover artista do evento é exclusão de verdade.

### `event_group` (pivot N:N — grupos participantes)
| event_id | bigint FK |
| group_id | bigint FK |

Sem `active`: remover grupo do evento é exclusão de verdade.

### `event_media` (galeria)
| id | bigint PK |
| event_id | bigint FK → events, `cascadeOnDelete` |
| tipo | enum(foto, video) |
| url | string |
| ordem | integer default 0 |
| active | boolean default true — soft delete manual |

### `event_reviews` (avaliações)
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| event_id | bigint FK → events | `cascadeOnDelete` |
| user_id | bigint FK → users | `restrictOnDelete` — protege histórico de avaliações |
| evr_nota | tinyint unsigned (1-5) | validação de range fica na aplicação/Model |
| evr_comentario | text | nullable — coluna já criada agora; tela de comentários pode vir depois, sem precisar de migration extra |
| evr_active | boolean | default true — soft delete manual |

Unique em `(event_id, user_id)` — um usuário avalia um evento uma vez só. Prefixo `evr_` (não `eve_`, que já é de `events`) — mesmo padrão de colisão de `evs_`/`evm_`.

---

## 3. Ingressos

### `ticket_types` (tipos/lotes — ligados à sessão, não ao evento)
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| event_session_id | bigint FK → event_sessions | `cascadeOnDelete` — tipo de ingresso só existe no contexto da sessão |
| nome | string | ex: "Meia-entrada", "VIP", "Lote 1" |
| preco | decimal(10,2) | |
| quantidade_total | integer | |
| quantidade_vendida | integer | default 0 |
| venda_inicio | datetime | nullable — inclui hora |
| venda_fim | datetime | nullable — inclui hora |
| active | boolean | default true — soft delete manual (renomeado de `ativo` pra seguir a convenção `active`) |

### `tickets` (ingressos individuais)
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| ticket_type_id | bigint FK → ticket_types | `restrictOnDelete` — não pode apagar lote com ingressos vendidos |
| user_id | bigint FK → users | `restrictOnDelete` — não pode apagar usuário com ingressos comprados |
| codigo_qr | string unique | |
| status | enum(valido, usado, cancelado) | default valido |
| comprado_em | timestamp | default CURRENT_TIMESTAMP |
| usado_em | timestamp | nullable |
| active | boolean | default true — soft delete manual (ortogonal ao `status`) |

---

## 4. Patrocínio

### `sponsorships`
Relação polimórfica dupla: quem patrocina e o que é patrocinado.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| sponsor_type / sponsor_id | string / bigint | polimórfico (`$table->morphs('sponsor')`): hoje só `User` (pessoa física ou, futuramente, empresa); sem prefixo, tratado como equivalente a FK |
| alvo_type / alvo_id | string / bigint | polimórfico (`$table->morphs('alvo')`): `Event`, `ArtistProfile` ou `Group`; sem prefixo, tratado como equivalente a FK |
| spo_tipo_apoio | enum(dinheiro, equipamento, figurino, alimentacao, transporte, hospedagem, fotografia, filmagem, iluminacao, som, outro) | |
| spo_valor | decimal(10,2) | nullable — só para apoio financeiro |
| spo_descricao | text | nullable — detalha apoio material |
| spo_status | enum(proposto, aceito, recusado, concluido) | default `proposto` |
| spo_active | boolean | default true — soft delete manual |

### `accepted_support_types`
Permite que cada organizador defina quais tipos de apoio aceita.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| alvo_type / alvo_id | string / bigint | polimórfico (`$table->morphs('alvo')`): `Event`, `ArtistProfile`, `Group`; sem prefixo, tratado como equivalente a FK |
| ast_tipo_apoio | enum (mesmo enum de sponsorships, 11 valores incluindo `outro`) | |
| created_at | timestamp | default CURRENT_TIMESTAMP, sem `updated_at` |

Sem `active` — remover um tipo aceito é exclusão de verdade (mesma exceção dos pivots puros). Unique em `(alvo_type, alvo_id, ast_tipo_apoio)` evita duplicar o mesmo tipo aceito pro mesmo alvo.

---

## 5. Interação social

### `follows` (seguidores — polimórfico)
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK → users | `cascadeOnDelete` |
| seguivel_type / seguivel_id | string / bigint | polimórfico (`$table->morphs('seguivel')`): `ArtistProfile`, `Group` ou `Event`; sem prefixo, tratado como equivalente a FK |
| created_at | timestamp | default CURRENT_TIMESTAMP, sem `updated_at` |

Sem `active` — deixar de seguir é exclusão de verdade (mesma exceção de `accepted_support_types`). Unique em `(user_id, seguivel_type, seguivel_id)` evita seguir o mesmo alvo duas vezes.

### `favorites`
Pivot puro (sem coluna própria): `user_id` FK → `users` (`cascadeOnDelete`), `event_id` FK → `events` (`cascadeOnDelete`), chave primária composta `(user_id, event_id)` — mesmo formato de `event_artist`/`event_group`/`artist_profile_skill`.

### `feed_posts`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| autor_type / autor_id | string / bigint | polimórfico (`$table->morphs('autor')`): `ArtistProfile` ou `Group`; sem prefixo, tratado como equivalente a FK |
| fee_tipo | enum(atualizacao, foto, video, evento) | |
| fee_conteudo | text | nullable |
| fee_midia_url | string | nullable |
| fee_active | boolean | default true — soft delete manual |

---

## 6. Oportunidades (marketplace de profissionais)

### `opportunities`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| criador_type / criador_id | string / bigint | polimórfico (`$table->morphs('criador')`): `ArtistProfile` ou `Group`; sem prefixo, tratado como equivalente a FK |
| opp_titulo | string | |
| opp_descricao | text | |
| skill_id | bigint FK → skills | nullable, `nullOnDelete` |
| opp_cidade | string | nullable |
| opp_status | enum(aberta, fechada) | default aberta |
| opp_active | boolean | default true — soft delete manual |

### `opportunity_applications`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| opportunity_id | bigint FK → opportunities | `cascadeOnDelete` |
| user_id | bigint FK → users | `restrictOnDelete` |
| opa_mensagem | text | nullable |
| opa_status | enum(pendente, aceito, recusado) | default pendente |
| opa_active | boolean | default true — soft delete manual |

Unique em `(opportunity_id, user_id)` — usuário se candidata a uma oportunidade uma vez só. Prefixo `opa_` (não `opp_`, que já é de `opportunities`) — mesmo padrão de colisão de `evs_`/`evm_`/`evr_`.

---

## 7. Plataforma / moderação

### `notifications`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK → users | `cascadeOnDelete` |
| not_tipo | string | |
| not_conteudo | json | |
| not_lida | boolean | default false |
| not_active | boolean | default true — soft delete manual |

### `reports` (denúncias)
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| denunciante_id | bigint FK → users | `restrictOnDelete` — protege histórico de moderação |
| alvo_type / alvo_id | string / bigint | polimórfico (`$table->morphs('alvo')`); sem prefixo, tratado como equivalente a FK |
| rep_motivo | string | |
| rep_status | enum(pendente, analisado, resolvido) | default pendente |
| rep_active | boolean | default true — soft delete manual |

---

## Decisões registradas (2026-07-21)

| Ponto | Decisão | Observação |
|---|---|---|
| Patrocinador pessoa vs. empresa | `User` com campo `tipo_conta` (pessoa/empresa) | Provisório — se o volume de empresas patrocinadoras crescer, migrar para tabela `companies` separada |
| Comentários em eventos | Coluna `comentario` já criada em `event_reviews` | Feature de UI pode vir depois; schema já preparado |
| QR Code | Um QR por `ticket` (ingresso individual), não por `ticket_type` | Necessário para controle de entrada único e lista de presença |
| Busca por raio de distância | `latitude`/`longitude` decimal + cálculo de distância em query (Haversine) | Suficiente para o volume inicial; revisar para `POINT`/`SPATIAL INDEX` se o volume de eventos crescer muito |

## Decisões registradas (2026-07-22)

| Ponto | Decisão | Observação |
|---|---|---|
| FK do aprovador de evento | `aprovado_por` (nome do doc) → `aprovado_por_id` na migration | Termina em `_id`, fica isento do prefixo `eve_` (mesma regra usada em `groups.user_id` no grupo 2), mas mantém nome semântico claro |
| Par polimórfico organizador | `organizador_type`/`organizador_id` sem prefixo, via `$table->morphs('organizador')` | Tratado como equivalente a FK (mesma exceção do `*_id`), sem customizar nome de relação nos Models |
| Cartaz do evento | Campo `cartaz_url` (nullable) adicionado a `events` | Não estava no levantamento original, pedido nesta sessão |
| Soft delete manual (`active`) | Flag booleana `{prefixo}_active` (default true) em `users`, `categories`, `venues`, `skills`, `artist_profiles`, `groups`, `events`, `event_sessions`, `event_media` | Retroaplicada nas migrations de criação do grupo 1/2 (projeto ainda em dev). Pivots/ligações puras (`group_members`, `artist_profile_skill`, `event_artist`, `event_group`) ficam de fora — desfazer a ligação é exclusão de verdade, não soft delete |

## Decisões registradas (2026-07-22, grupo 4)

| Ponto | Decisão | Observação |
|---|---|---|
| Colisão de prefixo `ticket_types`/`tickets` | `tickets` → `tic_` (base), `ticket_types` → `tit_` (ajusta 3ª letra) | Mesmo padrão de `gro_`/`grm_` e `eve_`/`evs_`/`evm_` |
| `active` em `ticket_types`/`tickets` | Ambas recebem `{prefixo}_active` | São tabelas principais, não pivot. Em `tickets`, coexiste com `tic_status` (mesmo padrão de `events`: `eve_status` + `eve_active`) |
| Campo `ativo` do doc original | Renomeado para `active` em `ticket_types` | Alinhar com a convenção `{prefixo}_active` (nome do doc era anterior a essa convenção) |

## Decisões registradas (2026-07-22, grupo 5)

| Ponto | Decisão | Observação |
|---|---|---|
| Prefixos `sponsorships`/`accepted_support_types` | `spo_`/`ast_` | Sem colisão com prefixos já usados (`art`, `cat`, `eve`, `evm`, `evs`, `grm`, `gro`, `ski`, `tic`, `tit`, `use`, `ven`) |
| Pares polimórficos `sponsor`/`alvo` | `$table->morphs('sponsor')` e `$table->morphs('alvo')`, sem prefixo | Mesma exceção já usada em `organizador` (tratado como equivalente a FK) |
| `active` só em `sponsorships` | `accepted_support_types` não recebe `{prefixo}_active` | É tabela de configuração/ligação (organizador define tipos aceitos) — remover é exclusão de verdade, mesma exceção dos pivots puros (`group_members`, `artist_profile_skill`, `event_artist`, `event_group`) |
| Unicidade em `accepted_support_types` | `unique(['alvo_type', 'alvo_id', 'ast_tipo_apoio'])` | Evita duplicar o mesmo tipo aceito pro mesmo alvo — mesmo papel do `unique(['group_id', 'user_id'])` em `group_members` |

## Decisões registradas (2026-07-22, grupo 6)

| Ponto | Decisão | Observação |
|---|---|---|
| Prefixo `feed_posts` | `fee_` | Sem colisão com prefixos já usados |
| Colisão `event_reviews` | `evr_` (ajusta 3ª letra) | `eve_` já é de `events` — mesmo padrão de `evs_`/`evm_` |
| `follows`/`favorites` sem prefixo próprio | Nenhuma das duas tem coluna de negócio além de FK/morph | Não há o que prefixar |
| `active` só em `feed_posts`/`event_reviews` | `follows`/`favorites` ficam de fora | São relações puras (seguir/favoritar) — desfazer é exclusão de verdade, mesma exceção de pivots e de `accepted_support_types` (grupo 5) |
| PK de `follows` | `id` próprio + `unique(user_id, seguivel_type, seguivel_id)`, não PK composta | Evita PK composta com coluna string (`seguivel_type`), mesmo motivo de `accepted_support_types` |

## Decisões registradas (2026-07-22, grupo 7)

| Ponto | Decisão | Observação |
|---|---|---|
| Colisão `opportunities`/`opportunity_applications` | `opportunities` → `opp_` (base), `opportunity_applications` → `opa_` | Mesmo padrão de `gro_`/`grm_`, `eve_`/`evs_`/`evm_`/`evr_`, `tic_`/`tit_` |
| `skill_id` nullable em `opportunities` | `nullOnDelete()` | Campo opcional — apagar a skill não deve arrastar a oportunidade, mesmo racional de `events.aprovado_por_id` |
| `active` em ambas | `opp_active`/`opa_active` | São tabelas principais (anúncio e candidatura), não pivot — coexistem com `opp_status`/`opa_status` |
| `restrictOnDelete` em `opportunity_applications.user_id` | Protege registro de candidatura | Candidatura é decisão de negócio (aceita/recusada), não preferência leve — mesmo racional de `tickets.user_id`/`event_reviews.user_id` |

## Decisões registradas (2026-07-22, grupo 8)

| Ponto | Decisão | Observação |
|---|---|---|
| Prefixos `notifications`/`reports` | `not_`/`rep_` | Sem colisão com prefixos já usados |
| `active` em ambas | `not_active`/`rep_active` | Tabelas principais, não pivot — coexistem com `not_lida`/`rep_status` |
| `restrictOnDelete` em `reports.denunciante_id` | Protege histórico de moderação | Mesmo racional de `event_reviews.user_id`/`opportunity_applications.user_id` |
| `notifications.user_id` `cascadeOnDelete` | Notificação não tem valor histórico a proteger | Mesmo racional de `follows.user_id` |

## Decisões registradas (2026-07-22, seeders)

| Ponto | Decisão | Observação |
|---|---|---|
| Seeders sem Models/Factories | `DB::table()->insert()` + `fake()`, exceto `users` (usa `User::factory()`, único Model existente) | Criar os outros 23 Models agora só pra rodar seeder seria antecipar a Etapa 3 sem necessidade |
| Colunas polimórficas nos seeders | FQCN literal como string (`'App\\Models\\ArtistProfile'` etc.) | Mesmo valor que `morphs()` gravaria por padrão sem morph map; se Etapa 3 adotar morph map, basta rodar `migrate:fresh --seed` de novo (dado de dev, sem custo) |
| Fix em `App\Models\User::$fillable` | Trocado `name` por `use_name` + demais colunas `use_*` | Bug pré-existente: fillable ainda citava `name`, mas a coluna real (migration) é `use_name` — nunca foi exercitado porque os testes padrão não tocam o banco; sem esse fix o seeder de usuários falhava |
| Volume de dados | ~20 usuários, 12 eventos, 27 tabelas todas populadas (ver contagem no `DatabaseSeeder`) | Suficiente pra exercitar relacionamentos/constraints sem virar carga de stress test |

## Decisões registradas (2026-07-23, Etapa 3 — grupo 1+2)

| Ponto | Decisão | Observação |
|---|---|---|
| Enums de coluna → PHP nativo | `App\Enums\Type.php` — um arquivo único com todos os `enum` backed do projeto (`TipoConta`, `GroupMemberRole` por enquanto) | Fecha item do backlog. Múltiplas classes num arquivo só quebram PSR-4 autoload (composer recusa); resolvido com `"classmap": ["app/Enums"]` no `composer.json` (o diretório é escaneado por conteúdo, não por nome de arquivo) — rodar `composer dump-autoload` sempre que um novo enum for adicionado ao arquivo |
| Pivot `group_members` precisa de Model dedicado | `App\Models\GroupMember extends Pivot`, usado via `->using(GroupMember::class)` | Tabela tem `id` próprio + coluna extra (`grm_papel`) + só `created_at` (sem `updated_at`) — não dá pra usar pivot simples nem `withTimestamps()` padrão |
| `GroupMember` timestamps | `public $timestamps = false;` no Model, `created_at` fica só por conta do `useCurrent()` do banco | Verificado empiricamente: `withTimestamps()` na relação usa as colunas de timestamp do Model **pai** (`Group`/`User`), não do Pivot — ignora qualquer `UPDATED_AT = null` custom no Pivot e sempre tenta inserir `updated_at`, quebrando porque a coluna não existe. Colocar `'created_at'` em `withPivot()` reativa o mesmo problema (Eloquent trata presença de qualquer coluna de timestamp no pivot como sinal pra inserir as duas). Solução: `withPivot('grm_papel')` (sem `created_at`) + `$timestamps = false` no Model — o banco preenche `created_at` sozinho. Efeito colateral: `$pivot->created_at` não vem populado ao ler a relação (não está no `withPivot`); se precisar dele no futuro, consultar a tabela direto |
| Models/Factories criados | `Category`, `Venue`, `Skill`, `ArtistProfile`, `Group`, `GroupMember` (+ relacionamentos novos em `User`: `artistProfile()`, `ownedGroups()`, `groups()`) | Pivots sem coluna extra (`artist_profile_skill`) seguem sem Model, só `belongsToMany` nos dois lados |
| Testes | `tests/Feature/Models/` — um teste básico de factory+persistência pra Models "planos" (`Category`/`Venue`/`Skill`), testes de relacionamento pra `ArtistProfile`/`Group`/`User` (incluindo o pivot com `grm_papel`) | `RefreshDatabase`, roda em sqlite (`phpunit.xml`) |

## Decisões registradas (2026-07-23, Etapa 3 — grupo 3)

| Ponto | Decisão | Observação |
|---|---|---|
| `organizador` sem morph map | `Event::organizador(): MorphTo` usa `morphTo()` padrão, sem `Relation::morphMap()` | Seeders já gravam FQCN literal (`'App\Models\User'`/`ArtistProfile`/`Group`) e o `morphTo()` default resolve isso direto — configurar morph map agora seria abstração antecipada sem necessidade concreta (nada quebra, nada pede desacoplamento do nome da classe ainda) |
| `event_artist`/`event_group` sem Model dedicado | `belongsToMany` simples nos dois lados (`Event::artists()`/`Event::groups()` e `ArtistProfile::events()`/`Group::events()`) | Pivots puros — PK composta, sem coluna extra, sem timestamps — mesmo caso de `artist_profile_skill` (grupo 2) |
| Enums de coluna → PHP nativo (continuação) | `EventStatus` (`eve_status`) e `EventMediaType` (`evm_tipo`) adicionados em `App\Enums\Type.php` | Fecha mais 2 itens do backlog. Lembrete reforçado: `composer dump-autoload` é obrigatório depois de adicionar um enum novo ao arquivo — o classmap não é escaneado dinamicamente, só na hora do dump (causou falha `Class ... not found` nos testes até rodar o comando) |
| Models/Factories criados | `Event`, `EventSession`, `EventMedia` (+ `events()` novo em `ArtistProfile` e `Group`) | `EventFactory` usa `ArtistProfile` como organizador default (mais central no domínio), com estado `porGrupo()` pra trocar pra `Group` — cobre os dois lados do polimorfismo nos testes |
| Testes | `tests/Feature/Models/EventTest.php`, `EventSessionTest.php`, `EventMediaTest.php` — cobrem `category()`, `organizador()` (ArtistProfile e Group), `sessions()`/`media()` (hasMany), `artists()`/`groups()` (belongsToMany via pivot puro) e casts de `eve_status`/`evm_tipo` | 25 testes / 35 assertions no total, sem regressão |

## Decisões registradas (2026-07-23, Etapa 3 — grupo 4)

| Ponto | Decisão | Observação |
|---|---|---|
| Enums de coluna → PHP nativo (continuação) | `TicketStatus` (`tic_status`) adicionado em `App\Enums\Type.php` | Fecha mais um item do backlog. `composer dump-autoload` rodado antes de testar (mesmo lembrete dos grupos anteriores) |
| Models/Factories criados | `TicketType`, `Ticket` (+ `ticketTypes()` novo em `EventSession`, `tickets()` novo em `User`) | Sem pivot novo neste grupo — `tickets` é tabela "principal" de verdade (FK simples pra `ticket_types`/`users`), não ligação N:N |
| `tit_preco` cast decimal | `'tit_preco' => 'decimal:2'` no Model `TicketType` | Cast `decimal` do Eloquent retorna string formatada (ex: `'99.90'`), evita imprecisão de float em valor monetário |
| Testes | `tests/Feature/Models/TicketTypeTest.php`, `TicketTest.php` — cobrem `eventSession()`/`tickets()` (hasMany), `ticketType()`/`user()`, cast de `tic_status` pro enum e estados de factory `usado()`/`cancelado()` | 32 testes / 44 assertions no total, sem regressão |

## Decisões registradas (2026-07-23, Etapa 3 — grupo 5)

| Ponto | Decisão | Observação |
|---|---|---|
| Valor `outro` adicionado ao tipo de apoio | `spo_tipo_apoio`/`ast_tipo_apoio` ganharam `outro` (11º valor) nas migrations, no enum PHP e nas duas seeders | Pedido nesta sessão — cobre casos de apoio fora dos 10 já previstos. Como é `enum(...)` no nível da coluna MySQL, exigiu editar as migrations (não só o enum PHP) e rodar `migrate:fresh --seed` de novo (projeto ainda em dev) |
| Enums de coluna → PHP nativo (continuação) | `TipoApoio` (compartilhado entre `spo_tipo_apoio` e `ast_tipo_apoio` — um enum só pros dois, evita duplicar os 11 valores) e `SponsorshipStatus` (`spo_status`) adicionados em `App\Enums\Type.php` | Fecha os últimos itens do backlog referentes a este grupo. Resta só `fee_tipo` (grupo 6). `composer dump-autoload` rodado antes de testar |
| Models/Factories criados | `Sponsorship` (`sponsor()`/`alvo()`, ambos `morphTo()` sem morph map — mesma decisão do `organizador`), `AcceptedSupportType` (`alvo()`) | `AcceptedSupportType` usa `$timestamps = false` (só `created_at`, preenchido pelo `useCurrent()` do banco) — mesmo caso de `GroupMember` |
| Relações inversas | `User::sponsorships()` (`morphMany` via `sponsor`); `Event`/`ArtistProfile`/`Group` ganham `sponsorships()` e `acceptedSupportTypes()` (`morphMany` via `alvo`) | `SponsorshipFactory` tem estados `porArtistProfile()`/`porGroup()` (troca `alvo`, mesmo padrão do `porGrupo()` da `EventFactory`) e `dinheiro()` (preenche `spo_valor`, zera `spo_descricao`) |
| Testes | `tests/Feature/Models/SponsorshipTest.php`, `AcceptedSupportTypeTest.php` — cobrem os dois lados do `morphTo` duplo em `Sponsorship`, os 3 alvos possíveis, casts de enum, estado `dinheiro()` e as relações inversas | 42 testes / 58 assertions no total, sem regressão |

**Backlog registrado**: refatorar migrations/seeders pra puxarem a lista de valores diretamente de `TipoApoio::cases()` (via `array_column` ou similar) em vez de repetir o array de 11 strings em 4 lugares (2 migrations + 2 seeders) — adiado pra não acoplar migration a código de app numa mudança já em andamento; fica pra uma próxima sessão.

## Decisões registradas (2026-07-23, Etapa 3 — grupo 6)

| Ponto | Decisão | Observação |
|---|---|---|
| Enum de coluna → PHP nativo (último item) | `FeedPostType` (`fee_tipo`) adicionado em `App\Enums\Type.php` | Fecha o backlog "Enums centralizados em PHP" por completo — todos os enums do projeto agora vivem em `Type.php`. `composer dump-autoload` rodado antes de testar |
| Models/Factories criados | `Follow` (`user()`/`seguivel()`), `FeedPost` (`autor()`), `EventReview` (`event()`/`user()`) | `Follow` usa `$timestamps = false` (só `created_at`, `useCurrent()` do banco) — mesmo caso de `GroupMember`/`AcceptedSupportType` |
| `favorites` sem Model dedicado | Pivot puro (PK composta `user_id`+`event_id`, sem timestamps, sem coluna própria) — mesmo formato de `event_artist`/`event_group`/`artist_profile_skill` | Só `belongsToMany` nos dois lados: `User::favoriteEvents()` / `Event::favoritedBy()` |
| Relações inversas | `User` ganha `follows()`, `favoriteEvents()`, `eventReviews()`; `ArtistProfile`/`Group` ganham `followers()`/`feedPosts()`; `Event` ganha `followers()`/`favoritedBy()`/`reviews()` | `Event` não ganha `feedPosts()` — seeder confirma que só `ArtistProfile`/`Group` publicam feed |
| Testes | `tests/Feature/Models/FollowTest.php`, `FeedPostTest.php`, `EventReviewTest.php`, `FavoriteTest.php` — cobrem os 3 alvos possíveis do `morphTo` de `Follow`, os 2 autores possíveis de `FeedPost`, casts, e as relações inversas (incluindo `belongsToMany` de favoritos) | 56 testes / 81 assertions no total, sem regressão |

**Correção (registrada no grupo 7)**: a linha acima dizia que `fee_tipo` fechava o backlog "Enums centralizados em PHP" por completo — isso ignorou que `opportunities.opp_status`/`opportunity_applications.opa_status` (grupo 7) e `reports.rep_status` (grupo 8) ainda não tinham enum PHP. Corrigido no grupo 7 (ver seção abaixo).

## Decisões registradas (2026-07-23, Etapa 3 — grupo 7)

| Ponto | Decisão | Observação |
|---|---|---|
| Enums de coluna → PHP nativo (correção) | `OpportunityStatus` (`opp_status`) e `OpportunityApplicationStatus` (`opa_status`) adicionados em `App\Enums\Type.php` | Backlog "Enums centralizados em PHP" tinha sido fechado cedo demais no grupo 6 (esqueceu esses 2 + `rep_status`). Agora só falta `rep_status` (grupo 8, último) |
| Models/Factories criados | `Opportunity` (`criador()` morphTo, `skill()` belongsTo, `applications()` hasMany), `OpportunityApplication` (`opportunity()`/`user()` belongsTo) | `criador` varia entre `ArtistProfile`/`Group` (sem `Event`, confirmado pelo seeder) — mesma decisão de `organizador`/`autor`/`seguivel`, sem morph map |
| Relações inversas | `ArtistProfile`/`Group` ganham `opportunities()` (`morphMany` via `criador`); `Skill` ganha `opportunities()` (`hasMany`); `User` ganha `opportunityApplications()` (`hasMany`) | `OpportunityFactory` tem estado `porGroup()` (troca `criador`); `OpportunityApplicationFactory` tem estados `aceita()`/`recusada()` (mesmo padrão de `usado()`/`cancelado()` da `TicketFactory`) |
| Testes | `tests/Feature/Models/OpportunityTest.php`, `OpportunityApplicationTest.php` — cobrem os 2 criadores possíveis, `skill()`, `applications()`, casts de enum, estados de factory e as relações inversas | 67 testes / 97 assertions no total, sem regressão |

## Decisões registradas (2026-07-23, Etapa 3 — grupo 8, final da Etapa 3)

| Ponto | Decisão | Observação |
|---|---|---|
| `not_tipo` fica string, sem enum PHP | Decisão consciente — `not_tipo` é `string` livre no schema (não é `enum(...)` de banco), diferente de `rep_status` | Criar enum PHP pra ele seria introduzir restrição que o schema não tem; os 5 valores do seeder continuam sendo só convenção informal |
| Enum de coluna → PHP nativo (último item) | `ReportStatus` (`rep_status`) adicionado em `App\Enums\Type.php` | Fecha o backlog "Enums centralizados em PHP" **de verdade** — o grupo 6 tinha marcado como concluído por engano, o grupo 7 corrigiu parcialmente, este grupo fecha o resto |
| Models/Factories criados | `Notification` (`user()`), `Report` (`denunciante()` belongsTo, `alvo()` morphTo) | `alvo` varia entre `Event`/`ArtistProfile`/`Group`/`FeedPost` (sem `User` — confirmado pelo seeder), sem morph map, mesma decisão de sempre |
| Relações inversas | `User` ganha `notifications()` e `reports()` (denúncias feitas, via `denunciante_id`); `Event`/`ArtistProfile`/`Group`/`FeedPost` ganham `reports()` (`morphMany` via `alvo`, denúncias recebidas) | `ReportFactory` tem estados `porArtistProfile()`/`porGroup()`/`porFeedPost()` (mesmo padrão de `SponsorshipFactory`/`FollowFactory`/`OpportunityFactory`) |
| Testes | `tests/Feature/Models/NotificationTest.php`, `ReportTest.php` — cobrem os 4 alvos possíveis do `morphTo` de `Report`, casts, e as relações inversas | 77 testes / 110 assertions no total, sem regressão |

**Etapa 3 concluída por completo** (8 de 8 grupos: Models Eloquent + relacionamentos + Factories pra todas as 27 tabelas de negócio do projeto).

## Decisões registradas (2026-07-23, refactor pós-Etapa 3)

Logo depois da Etapa 3 fechar, o usuário pediu pra substituir os 11 enums PHP nativos (`enum ... : string`, centralizados em `app/Enums/Type.php`) por uma abordagem mais simples: uma classe única `App\Enums\Types` com constantes array.

| Ponto | Decisão | Observação |
|---|---|---|
| `Type.php` → `Types.php` | Arquivo com 11 `enum ... : string` deletado; nova classe `Types` com 11 `public const` array (ex: `TIPO_CONTA = ['PESSOA', 'EMPRESA']`) | `composer.json` mantém `"classmap": ["app/Enums"]` sem alteração — cobre qualquer classe na pasta, independente do nome do arquivo |
| Valor individual = string literal | `TicketStatus::VALIDO` (cast tipado) virou `'VALIDO'` direto no código | Decisão confirmada com o usuário: sem constante escalar por valor (ex: não existe `Types::TICKET_STATUS_VALIDO`), só a lista completa (`Types::TICKET_STATUS`) pra uso com `fake()->randomElement()` |
| Valores no banco também maiúsculos | Os 12 `enum(...)` de coluna MySQL (11 migrations, `sponsorships` tem 2 colunas) mudaram de minúsculo (`'pessoa'`) pra maiúsculo (`'PESSOA'`) | Decisão confirmada com o usuário — exigiu editar as 11 migrations e rodar `migrate:fresh --seed` de novo (projeto em dev, mesmo precedente do `outro` no grupo 5) |
| Migrations referenciam `Types::CONST` | `$table->enum('use_tipo_conta', Types::TIPO_CONTA)->default('PESSOA')` em vez de repetir o array | Resolve a parte de migrations do backlog antigo "migrations/seeders duplicam array de valores" — seeders continuam com array hardcoded (fora do escopo pedido) |
| Models perdem o cast de enum | Removida a entrada `'coluna' => XEnum::class` de `casts()` em 9 Models; `GroupMember`/`AcceptedSupportType` perderam o método `casts()` inteiro (só tinham essa entrada, ficaria array vazio) | Atributo passa a ser string crua — sem cast, já que não tem mais enum PHP pra mapear |
| `notifications.not_tipo` fora do escopo | Continua `string` livre, sem constante `Types` | Mesma decisão do grupo 8 — não é `enum(...)` de banco, não há o que centralizar |
| Testes/Factories atualizados | 9 Factories e 11 arquivos de teste trocaram referência de enum por string literal (ou `Types::CONST` nas Factories que sorteiam de uma lista, ex: `fake()->randomElement(Types::TIPO_APOIO)`) | Métodos de teste que diziam "casts... to enum" foram renomeados pra refletir que não tem mais cast (ex: `test_casts_tic_status_to_enum` → `test_tic_status_is_string`) |
| Verificação | `composer dump-autoload` → `migrate:fresh --seed` (schema + dados com valores maiúsculos) → `php artisan test` | 77 testes / 110 assertions, mesma contagem de antes do refactor — só os valores mudaram, cobertura idêntica |

## Próximo passo sugerido

Depois de validar os pontos acima, gerar as migrations Laravel na ordem de dependência:
1. ~~`users`, `categories`, `venues`, `skills`~~ ✅
2. ~~`artist_profiles`, `groups`, `group_members`, `artist_profile_skill`~~ ✅
3. ~~`events`, `event_sessions`, `event_artist`, `event_group`, `event_media`~~ ✅
4. ~~`ticket_types`, `tickets`~~ ✅
5. ~~`sponsorships`, `accepted_support_types`~~ ✅
6. ~~`follows`, `favorites`, `feed_posts`, `event_reviews`~~ ✅
7. ~~`opportunities`, `opportunity_applications`~~ ✅
8. ~~`notifications`, `reports`~~ ✅
9. ~~Seeders básicos (24 seeders + `DatabaseSeeder`, todas as 27 tabelas populadas)~~ ✅
10. ~~Etapa 3, grupo 1+2: Models Eloquent + Factories de `categories`/`venues`/`skills`/`artist_profiles`/`groups`/`group_members`~~ ✅
11. ~~Etapa 3, grupo 3: Models Eloquent + Factories de `events`/`event_sessions`/`event_media` + pivots `event_artist`/`event_group`~~ ✅
12. ~~Etapa 3, grupo 4: Models Eloquent + Factories de `ticket_types`/`tickets`~~ ✅
13. ~~Etapa 3, grupo 5: Models Eloquent + Factories de `sponsorships`/`accepted_support_types`~~ ✅
14. ~~Etapa 3, grupo 6: Models Eloquent + Factories de `follows`/`feed_posts`/`event_reviews` (+ relação `favorites` sem Model dedicado)~~ ✅
15. ~~Etapa 3, grupo 7: Models Eloquent + Factories de `opportunities`/`opportunity_applications`~~ ✅
16. ~~Etapa 3, grupo 8: Models Eloquent + Factories de `notifications`/`reports`~~ ✅

Etapa 2 (Migrations + seeders) concluída por completo. **Etapa 3 (Models Eloquent + relacionamentos + Factories) concluída por completo — 8 de 8 grupos.** Próximo passo: Etapa 4 (Autenticação e autorização — usuário, papéis: usuário/artista/grupo/admin).
