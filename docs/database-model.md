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
| id | bigint PK |
| event_id | bigint FK → events |
| user_id | bigint FK → users |
| nota | tinyint (1-5) |
| comentario | text nullable — coluna já criada agora; tela de comentários pode vir depois, sem precisar de migration extra |

---

## 3. Ingressos

### `ticket_types` (tipos/lotes — ligados à sessão, não ao evento)
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| event_session_id | bigint FK → event_sessions | |
| nome | string | ex: "Meia-entrada", "VIP", "Lote 1" |
| preco | decimal(10,2) | |
| quantidade_total | integer | |
| quantidade_vendida | integer | default 0 |
| venda_inicio | datetime | nullable |
| venda_fim | datetime | nullable |
| ativo | boolean | default true |

### `tickets` (ingressos individuais)
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| ticket_type_id | bigint FK → ticket_types | |
| user_id | bigint FK → users | |
| codigo_qr | string unique | |
| status | enum(valido, usado, cancelado) | default valido |
| comprado_em | timestamp | |
| usado_em | timestamp | nullable |

---

## 4. Patrocínio

### `sponsorships`
Relação polimórfica dupla: quem patrocina e o que é patrocinado.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| sponsor_type | string | polimórfico: `User` (pode ser pessoa física ou, futuramente, empresa) |
| sponsor_id | bigint | |
| alvo_type | string | polimórfico: `Event`, `ArtistProfile` ou `Group` |
| alvo_id | bigint | |
| tipo_apoio | enum(dinheiro, equipamento, figurino, alimentacao, transporte, hospedagem, fotografia, filmagem, iluminacao, som) | |
| valor | decimal(10,2) | nullable — só para apoio financeiro |
| descricao | text | nullable — detalha apoio material |
| status | enum(proposto, aceito, recusado, concluido) | |

### `accepted_support_types`
Permite que cada organizador defina quais tipos de apoio aceita.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| alvo_type | string | polimórfico: `Event`, `ArtistProfile`, `Group` |
| alvo_id | bigint | |
| tipo_apoio | enum (mesmo enum de sponsorships) | |

---

## 5. Interação social

### `follows` (seguidores — polimórfico)
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK → users | |
| seguivel_type | string | `ArtistProfile`, `Group` ou `Event` |
| seguivel_id | bigint | |

### `favorites`
| user_id | bigint FK → users |
| event_id | bigint FK → events |

### `feed_posts`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| autor_type | string | polimórfico: `ArtistProfile` ou `Group` |
| autor_id | bigint | |
| tipo | enum(atualizacao, foto, video, evento) | |
| conteudo | text | nullable |
| midia_url | string | nullable |

---

## 6. Oportunidades (marketplace de profissionais)

### `opportunities`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| criador_type | string | polimórfico: `ArtistProfile` ou `Group` |
| criador_id | bigint | |
| titulo | string | |
| descricao | text | |
| skill_id | bigint FK → skills | nullable |
| cidade | string | nullable |
| status | enum(aberta, fechada) | default aberta |

### `opportunity_applications`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| opportunity_id | bigint FK → opportunities | |
| user_id | bigint FK → users | |
| mensagem | text | nullable |
| status | enum(pendente, aceito, recusado) | default pendente |

---

## 7. Plataforma / moderação

### `notifications`
| id | bigint PK |
| user_id | bigint FK → users |
| tipo | string |
| conteudo | json |
| lida | boolean default false |

### `reports` (denúncias)
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| denunciante_id | bigint FK → users | |
| alvo_type | string | polimórfico |
| alvo_id | bigint | |
| motivo | string | |
| status | enum(pendente, analisado, resolvido) | default pendente |

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

## Próximo passo sugerido

Depois de validar os pontos acima, gerar as migrations Laravel na ordem de dependência:
1. `users`, `categories`, `venues`, `skills`
2. `artist_profiles`, `groups`, `group_members`, `artist_profile_skill`
3. `events`, `event_sessions`, `event_artist`, `event_group`, `event_media`
4. `ticket_types`, `tickets`
5. `sponsorships`, `accepted_support_types`
6. `follows`, `favorites`, `feed_posts`, `event_reviews`
7. `opportunities`, `opportunity_applications`
8. `notifications`, `reports`
