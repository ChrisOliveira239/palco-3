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
- Se duas tabelas gerarem o mesmo prefixo de 3 letras, ajustar uma letra pra manter unicidade — decidir a variação no momento de criar aquela migration e registrar aqui. Já aconteceu com `groups` → `gro_` e `group_members` → `grm_`, e com o cluster `events`/`event_sessions`/`event_media` → `eve`/`evs`/`evm`.
- **Não prefixar**: `id`, `created_at`/`updated_at`, chaves estrangeiras (`*_id`, seguem o padrão Eloquent `{tabela_singular}_id`), e colunas reservadas do Laravel Auth (`email`, `password`, `remember_token`, `email_verified_at`) — evita quebrar `UserProvider`, notificações, reset de senha etc.
- Exemplo: tabela `users`, coluna de negócio `name` → `use_name`; `bio` → `use_bio`.
- **Soft delete manual**: toda tabela "principal" (não pivot/ligação N:N) recebe uma coluna `{prefixo}_active` (boolean, default `true`) — ex: `use_active`, `eve_active`. Pivots/ligações puras (`group_members`, `artist_profile_skill`, `event_artist`, `event_group`) não recebem, porque desfazer a ligação é exclusão de verdade, não soft delete. Decisão tomada no grupo 3 (2026-07-22), retroaplicada nas tabelas dos grupos 1 e 2 enquanto o projeto estava em dev.

### Outras convenções (a definir conforme o projeto avança)

- [ ] Padrão de nomenclatura de branches
- [ ] Padrão de commits (ex: Conventional Commits)
- [x] Comandos de setup/build/test do backend — `cd backend && composer install`, `php artisan migrate`, `php artisan test`
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
| 2 | Migrations Laravel + seeders básicos | 🔄 em andamento — grupos 1, 2 e 3 prontos (`users`, `categories`, `venues`, `skills`, `artist_profiles`, `groups`, `group_members`, `artist_profile_skill`, `events`, `event_sessions`, `event_artist`, `event_group`, `event_media`); grupos 4-8 e seeders pendentes |
| 3 | Models Eloquent + relacionamentos + Factories | Pendente |
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

## Status atual do projeto

**Fase atual:** Etapa 2 — Migrations Laravel (grupos 1, 2 e 3 concluídos em 2026-07-21/22).

Modelagem do banco de dados concluída e documentada em `docs/database-model.md` (inclui decisões registradas sobre patrocinador pessoa/empresa, comentários em avaliações, QR Code por ingresso, busca por raio de distância, DRT/CNPJ e contato de artista/grupo, FK `aprovado_por_id`, morph `organizador` sem prefixo, cartaz de evento e a flag `active` de soft delete manual).

Backend Laravel 12 instalado em `backend/`, conectado a MySQL local (banco `palco-3`). Migrations criadas e rodadas: `users` (estendida), `categories`, `venues`, `skills` (grupo 1) + `artist_profiles`, `groups`, `group_members`, `artist_profile_skill` (grupo 2) + `events`, `event_sessions`, `event_artist`, `event_group`, `event_media` (grupo 3) — seguindo a convenção de prefixo de 3 letras por tabela (ver "Nomenclatura de colunas"), incluindo os ajustes de colisão `gro_`/`grm_` e `eve_`/`evs_`/`evm_`. Flag `active` (boolean, default true) retroaplicada em `users`, `categories`, `venues`, `skills`, `artist_profiles`, `groups`, `events`, `event_sessions`, `event_media` — pivots/ligações puras (`group_members`, `artist_profile_skill`, `event_artist`, `event_group`) ficam de fora. Próxima sessão: migrations do grupo 4 (`ticket_types`, `tickets`).

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
