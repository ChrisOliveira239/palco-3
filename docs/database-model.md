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
| spo_tipo_apoio | enum(dinheiro, equipamento, figurino, alimentacao, transporte, hospedagem, fotografia, filmagem, iluminacao, som) | |
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
| ast_tipo_apoio | enum (mesmo enum de sponsorships) | |
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

Todas as migrations de schema (grupos 1-8) concluídas. Próximo passo real: seeders básicos (fecha Etapa 2) e/ou Models Eloquent + Factories (Etapa 3).
