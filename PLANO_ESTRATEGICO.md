# LUMINA — Plano Estrategico e Tecnico
## Roadmap Oficial de Implementacao
### Baseado na Analise Competitiva Global & Auditoria Completa do Codebase
**Data:** 2026-02-28 | **Versao:** 1.0

---

# SECAO 0 — ESTADO ATUAL DO PROJETO (Baseline)

Antes de identificar gaps, e essencial documentar o que **ja existe e funciona**.

## Funcionalidades Implementadas

### Onboarding (Secao 1.1 do documento)
- **Onboarding por Intencao**: Implementado (`OnboardingController`). 3 perguntas: intencao, humor, preferencia. Redirecionamento contextual para crise/fogueira/diario/biblioteca/dashboard.
- **Middleware de Onboarding**: `EnsureOnboardingCompleted` garante que nenhum utilizador salta o fluxo.
- **Estado**: COMPLETO. Alinhado com o documento.

### Navegacao & Layout (Secao 1.2)
- **Bottom Navigation Adaptativa**: Implementada em `lumina-layout.blade.php`. Muda o botao central (Fogueira vs Zona Calma) baseado nas `emotional_tags` do utilizador. Se tem "Ansiedade" ou "Sobrecarregado", o icone central torna-se Zona Calma com animacao pulsante.
- **Modo Noturno com Temperatura de Cor**: Implementado. Filtro ambar com opacidade variavel (21h-00h: 0.07, 00h-05h: 0.12).
- **Modo Madrugada**: Implementado com banner de apoio nocturno, links para Zona de Crise e Grounding, e dismissivel por sessao.
- **Estado**: PARCIALMENTE COMPLETO.

### Microinteracoes (Secao 1.3)
- **Animacoes de Carregamento Terapeuticas**: Implementadas (`calm-loader` com animacao de respiracao e texto "Respira...").
- **Notificacoes com Linguagem Emocional**: Implementadas parcialmente (sistema de notificacoes via `ForumInteraction`, `GentleChallengeReceived`).
- **Estado**: PARCIALMENTE COMPLETO.

### Acessibilidade (Secao 1.4)
- **Fonte para Dislexia**: Campo `a11y_dyslexic_font` no User, toggle no perfil, classe CSS aplicada no `<html>`.
- **Reduced Motion**: Campo `a11y_reduced_motion`, respeita `prefers-reduced-motion`.
- **Tamanho de Texto Persistente**: Campo `a11y_text_size` no User, aplicado dinamicamente via classe CSS no `<html>`.
- **Navegacao por Teclado**: `focus-visible` com outline de 3px em todos os elementos. Modais com estrutura acessivel.
- **Estado**: BEM IMPLEMENTADO.

### IA & NLP (Secao 2.1)
- **Detecao de Crise Multicamada**: Implementada no `CBTAnalysisService` com 3 camadas (keywords, intent patterns, NLP via GPT-4o-mini). Fallback local se API falhar. Seguranca: camadas locais nunca sao revertidas pela IA.
- **Prompts CBT no Diario**: Implementados via `CBTAnalysisService::analyze()`. Deteta distorcoes cognitivas e sugere perguntas socraticas.
- **Analise de Sentimento no Forum**: `analyzeForumPost()` classifica posts por risco e sentimento.
- **Alerta de Crise a Moderadores**: `ModeratorCrisisAlert` notifica admins/moderadores automaticamente.
- **Estado**: FORTE. A arquitectura de 3 camadas esta robusta.

### Seguranca & Privacidade (Secao 2.2)
- **Anonimato por Pseudonimo Consistente**: Implementado via `getPseudonymAttribute()` — hash SHA-256 do user_id + app key. Irreversivel.
- **Saida Rapida (Safe House)**: Implementada com duplo-Esc, limpeza de storage, redirect para Google, logout automatico.
- **Exportacao de Dados GDPR**: `PrivacyController::exportData()` exporta todos os dados em JSON.
- **Retencao Configuravel**: `diary_retention_days` no User + `PurgeExpiredData` command com cron diario.
- **Log de Acesso a Dados**: Modelo `DataAccessLog` + visualizacao na pagina de privacidade.
- **Hibernacao de Conta**: `PrivacyController::hibernate()` pausa a conta sem apagar dados.
- **Estado**: FORTE. GDPR bem coberto.

### Gamificacao (Secao 2.5)
- **Sistema de Chamas**: Implementado. Recompensas por diario (10), reacao (2), resposta (5), respiracao (5), primeiro post (20).
- **Streak Terapeutico**: `updateGentleStreak()` — recomeça sem culpa, sem punicao visual.
- **Niveis da Fogueira**: spark (<50), flame (<200), bonfire (<500), beacon (500+).
- **Conquistas/Badges**: Tabela `achievements` + `user_achievements`. Desbloqueio automatico por marcos.
- **Missoes Diarias**: 3 missoes aleatorias atribuidas por dia via `GamificationService::assignDailyMissions()`.
- **Progresso de Missoes**: Tracked via pivot `mission_user` com progresso e `completed_at`.
- **Oferta de Apoio entre Utilizadores**: `GamificationController::sendGentleChallenge()` — utilizadores com 50+ chamas podem enviar desafios gentis.
- **Marcos Pessoais (Milestones)**: Modelo `Milestone` + CRUD no perfil. Timeline pessoal privada.
- **Resumo Semanal**: `SendWeeklySummaries` command + `WeeklyEmotionalSummary` notification (mail + database). Cron aos Domingos as 10h.
- **Estado**: MUITO FORTE. A gamificacao esta quase completa.

### Chat em Tempo Real (Secao 2.3)
- **Laravel Reverb**: Configurado para WebSocket broadcasting.
- **6 Eventos Broadcast**: MessageSent, MessageReacted, MessageDeleted, MessageUpdated, MessageRead, RoomStatusUpdated.
- **Slow Mode**: 3s normal, 15s em modo crise.
- **Moderacao Completa**: Mute, delete, pin, crisis mode, report, presence tracking.
- **Reacoes Empaticas**: hug, candle, ear (abraco, vela, ouvir).
- **Read Receipts**: Opt-in via `read_receipts_enabled`.
- **Buddy System**: Candidaturas, sessoes privadas, salas privadas, avaliacao.
- **Estado**: COMPLETO e robusto.

### Auto-avaliacao (Secao 2.4)
- **PHQ-9 e GAD-7**: Implementados com questionarios clinicos validados, scoring automatico, severidade calculada, historico com comparacao a avaliacoes anteriores.
- **Estado**: COMPLETO.

### Conteudo & Comunidade
- **Forum (Mural da Esperanca)**: Posts, comentarios, reacoes, tags emocionais, posts sensiveis com blur, save, subscricao, pin, lock, shadowban, report.
- **Biblioteca de Recursos**: Sugestoes dos utilizadores, votacao, aprovacao por admin.
- **Playlist da Comunidade**: Sugestao de musicas, votacao, musica da semana, integracao Spotify oEmbed + iTunes API.
- **Zona Calma**: Exercicios de grounding, zona de crise com plano de seguranca pessoal.
- **Estado**: FORTE.

---

# SECAO 1 — GAP ANALYSIS

Analise detalhada do que o documento propoe vs o que o codebase tem.

## 1.1 GAPS CRITICOS (Prioridade Alta — Impacto direto na seguranca ou retencao)

### GAP-01: Pesquisa Global com Filtros Emocionais
- **Documento**: Secao 1.2 — Barra de pesquisa com filtros por estado emocional
- **Estado Atual**: NAO EXISTE. Zero funcionalidade de pesquisa na plataforma.
- **Prioridade**: ALTA
- **Dependencias**: Nenhuma
- **Justificacao**: Utilizadores em crise precisam encontrar conteudo relevante rapidamente.

### GAP-02: PWA com Service Worker & Offline Mode
- **Documento**: Secao 2.3 — Cache de recursos estaticos + diario offline + exercicios de respiracao offline
- **Estado Atual**: NAO EXISTE. Nenhum service worker, nenhum manifest.json.
- **Prioridade**: ALTA
- **Dependencias**: Vite build pipeline
- **Justificacao**: Acesso em crise sem internet e fundamental. O documento classifica como Alta prioridade.

### GAP-03: Previsao de Engagement Decrescente
- **Documento**: Secao 2.1 — ML que deteta utilizadores a desligar-se (posts mais curtos, menos reacoes, ausencia crescente)
- **Estado Atual**: NAO EXISTE. Nenhum tracking de padroes de engagement.
- **Prioridade**: ALTA
- **Dependencias**: Dados de `last_activity_at`, `daily_logs`, `posts`
- **Justificacao**: Prevencao de crises silenciosas. Utilizadores que desaparecem podem estar em risco.

### GAP-04: Check-in Pos-Leitura Automatico
- **Documento**: Secao 3.2 — Apos ler post com tag "Desabafo"/"Crise", pergunta "Como te sentes agora?"
- **Estado Atual**: Existe `postCheckin` na rota, mas precisa de verificacao de implementacao completa.
- **Prioridade**: ALTA
- **Dependencias**: `post_checkins` table (migration existe)
- **Justificacao**: Funcionalidade unica sem paralelo. O documento classifica como Alta.

### GAP-05: Moderacao Assistida por IA (Priorizacao de Fila)
- **Documento**: Secao 2.1 — IA pre-processa posts e ordena por risco na fila de moderacao
- **Estado Atual**: A detecao de risco existe no `CBTAnalysisService`, mas NAO ha dashboard de fila priorizada no admin.
- **Prioridade**: ALTA
- **Dependencias**: Filament admin, CBTAnalysisService

### GAP-06: Push Notifications via Web Push API
- **Documento**: Secao 2.3 — Push notifications no browser para eventos criticos
- **Estado Atual**: NAO EXISTE. Apenas notificacoes in-app e email.
- **Prioridade**: ALTA
- **Dependencias**: VAPID keys, WebPush PHP library, Service Worker

## 1.2 GAPS MEDIOS (Prioridade Media — Melhoram significativamente a experiencia)

### GAP-07: Modo de Voz — Ditado de Entradas
- **Documento**: Secao 1.4 — Web Speech API para ditado no diario
- **Estado Atual**: NAO EXISTE.
- **Prioridade**: MEDIA
- **Dependencias**: Frontend JS (Web Speech API e nativa, sem custo de servidor)

### GAP-08: Gradientes Dinamicos por Humor
- **Documento**: Secao 1.3 — Fundo da homepage muda conforme humor mais recente
- **Estado Atual**: NAO EXISTE no dashboard. O layout e estatico.
- **Prioridade**: MEDIA
- **Dependencias**: Ultimo `mood_level` do DailyLog

### GAP-09: Visualizacao de Humor em Espiral
- **Documento**: Secao 1.3 — Historico em espiral em vez de grafico linear
- **Estado Atual**: Existe `mood-spiral.blade.php` como componente, mas precisa verificar se esta integrado.
- **Prioridade**: MEDIA
- **Dependencias**: Canvas/SVG no frontend

### GAP-10: Tour Contextual Nao-intrusivo
- **Documento**: Secao 1.1 — Tooltips organicos na primeira interacao com cada funcionalidade
- **Estado Atual**: NAO EXISTE. Nenhum sistema de tooltips contextuais.
- **Prioridade**: MEDIA
- **Dependencias**: Frontend JS + tabela de tracking de "features descobertas" por user

### GAP-11: Temas de Interface por Sala/Seccao
- **Documento**: Secao 1.2 — Cada sala tem paleta subtilmente diferente
- **Estado Atual**: PARCIAL. Salas ja tem `color` que afeta os cards na listagem, mas dentro do chat a interface nao muda.
- **Prioridade**: MEDIA
- **Dependencias**: CSS custom properties passadas pela view do chat

### GAP-12: Breadcrumb Emocional ("Onde Estou?")
- **Documento**: Secao 1.2 — Linha subtil no topo com "Zona Calma > Respiracao" em linguagem emocional
- **Estado Atual**: NAO EXISTE.
- **Prioridade**: MEDIA
- **Dependencias**: Blade component reutilizavel

### GAP-13: Conexao por Experiencia Partilhada
- **Documento**: Secao 3.2 — Algoritmo que sugere utilizadores com experiencias semelhantes
- **Estado Atual**: NAO EXISTE.
- **Prioridade**: MEDIA
- **Dependencias**: Tags emocionais do perfil, historico de posts, algoritmo de matching

### GAP-14: Expressao Artistica como Terapia
- **Documento**: Secao 3.2 — Seccao para partilhar imagens/desenhos sem palavras ("The Wall")
- **Estado Atual**: NAO EXISTE.
- **Prioridade**: MEDIA
- **Dependencias**: Upload de imagens (storage), moderacao visual

### GAP-15: Encriptacao E2E nas Mensagens Buddy 1:1
- **Documento**: Secao 2.2 — Signal Protocol ou libsodium para mensagens privadas
- **Estado Atual**: NAO EXISTE. Mensagens buddy sao plaintext na DB.
- **Prioridade**: MEDIA (alto impacto mas alto esforco)
- **Dependencias**: libsodium PHP, key exchange frontend/backend

### GAP-16: Calendario Emocional Portugues
- **Documento**: Secao 3.1 — Reconhecer datas com impacto emocional (Finados, Natal, Santos Populares)
- **Estado Atual**: NAO EXISTE.
- **Prioridade**: MEDIA
- **Dependencias**: Config de datas + mensagens contextuais no dashboard

### GAP-17: Sons de Portugal Expandidos
- **Documento**: Secao 3.1 — Biblioteca sonora com gravacoes regionais
- **Estado Atual**: NAO EXISTE como feature dedicada. Existe playlist da comunidade mas nao sons ambientes.
- **Prioridade**: MEDIA
- **Dependencias**: Audio assets, Web Audio API

### GAP-18: Recomendacao Contextual de Recursos
- **Documento**: Secao 2.1 — Motor de recomendacao baseado em humor, historico, hora do dia
- **Estado Atual**: NAO EXISTE. A biblioteca mostra todos os recursos por ordem de votos.
- **Prioridade**: MEDIA
- **Dependencias**: Dados de mood, historico de salas, hora do dia

## 1.3 GAPS MENORES (Prioridade Baixa — Polish e diferenciacao)

### GAP-19: Gestos Mobile Contextuais
- **Documento**: Secao 1.2 — Swipe para guardar posts, mudar de sala
- **Estado Atual**: NAO EXISTE.
- **Prioridade**: BAIXA
- **Dependencias**: Frontend JS (touch events)

### GAP-20: A/B Testing de Intervencoes
- **Documento**: Secao 2.4 — Laravel Pennant para feature flags
- **Estado Atual**: NAO EXISTE. Pennant nao esta instalado.
- **Prioridade**: BAIXA
- **Dependencias**: `composer require laravel/pennant`

### GAP-21: Paginacao por Cursor (Infinite Scroll)
- **Documento**: Secao 2.3 — Cursor-based pagination em vez de offset
- **Estado Atual**: NAO EXISTE. O forum usa `->get()` sem paginacao. O chat usa `->take(50)`.
- **Prioridade**: BAIXA (torna-se alta com escala)
- **Dependencias**: Nenhuma (Laravel suporta nativamente `cursorPaginate()`)

### GAP-22: Lazy Loading de Imagens & Avatares
- **Documento**: Secao 2.3 — IntersectionObserver + loading="lazy"
- **Estado Atual**: PARCIAL. Avatares usam DiceBear (SVG externo) sem lazy loading explicito.
- **Prioridade**: BAIXA
- **Dependencias**: `loading="lazy"` nos `<img>`

### GAP-23: Multilinguismo (Estrutura)
- **Documento**: Secao 1.4 — Preparar para expansao linguistica
- **Estado Atual**: NAO EXISTE. Todos os textos estao hardcoded em PT-PT nas views.
- **Prioridade**: BAIXA (mas importante preparar a estrutura)
- **Dependencias**: Laravel localization (`__()`, ficheiros `lang/`)

### GAP-24: Cache Redis para Forum e Estatisticas
- **Documento**: Secao 2.3 — `Cache::remember()` para posts populares
- **Estado Atual**: PARCIAL. `public_rooms` ja esta em cache (5min). Posts do forum NAO estao em cache.
- **Prioridade**: BAIXA (torna-se alta com escala)
- **Dependencias**: Redis configurado

### GAP-25: Funil de Conversao Emocional (Analytics)
- **Documento**: Secao 2.4 — Medir: crise -> respiracao -> permanencia vs saida
- **Estado Atual**: NAO EXISTE.
- **Prioridade**: BAIXA
- **Dependencias**: Event tracking, analytics dashboard

### GAP-26: "Temperatura" da Comunidade em Tempo Real
- **Documento**: Secao 2.4 — Indice semaforo para moderadores (verde/amarelo/vermelho)
- **Estado Atual**: NAO EXISTE.
- **Prioridade**: BAIXA
- **Dependencias**: Job horario, dashboard admin

### GAP-27: Mapa de Jornada Emocional (Admin Dashboard)
- **Documento**: Secao 2.4 — Dashboard admin com ponto de entrada, acoes, score de risco
- **Estado Atual**: NAO EXISTE. Admin Filament nao tem dashboard customizado.
- **Prioridade**: BAIXA
- **Dependencias**: Filament widgets

### GAP-28: Relatorio de Impacto Comunitario Semanal
- **Documento**: Secao 2.4 — Dashboard publico com estatisticas anonimas
- **Estado Atual**: NAO EXISTE.
- **Prioridade**: BAIXA
- **Dependencias**: Pagina publica, queries agregadas

## 1.4 INCOERENCIAS DETETADAS

### INC-01: ModerationLog Model vs Migration
- **Problema**: O modelo `ModerationLog` (ja corrigido nesta sessao) referenciava `moderator_id` mas a migration final usa `user_id`. Estava corrigido nesta sessao.
- **Estado**: RESOLVIDO.

### INC-02: Campo `is_active` em Room sem Migration
- **Problema**: O modelo Room tinha `is_active` em `$fillable` mas nao existia na DB.
- **Estado**: RESOLVIDO (migration criada nesta sessao).

### INC-03: Mission Model vs Migration Schema
- **Problema**: A migration `mission_user` usa `progress` e `completed_at`, mas o `GamificationService` referencia `action_type` e `target_count` que nao existem na tabela `missions`. A migration de `missions` tem `type` e `goal_count`.
- **Impacto**: O progresso de missoes pode nao funcionar corretamente.
- **Acao**: Alinhar `GamificationService` com os nomes reais das colunas (`type` em vez de `action_type`, `goal_count` em vez de `target_count`).

### INC-04: PostReaction Model Vazio
- **Problema**: O modelo `PostReaction` existe mas esta praticamente vazio. O `SendWeeklySummaries` referencia-o, mas o `ForumController` usa acesso direto via DB facade para reacoes.
- **Impacto**: Baixo, funciona mas e inconsistente.

### INC-05: Forum sem Paginacao
- **Problema**: `ForumController::index()` faz `Post::latest()->get()` — carrega TODOS os posts sem paginacao.
- **Impacto**: Performance vai degradar com crescimento.
- **Acao**: Implementar cursor pagination.

### INC-06: Plano de Crise Pessoal — Subaproveitado
- **Documento**: Secao 3.2 — "Evolui para pagina interativa com botao especial em crise"
- **Estado Atual**: O `safety_plan` existe como JSON no User e renderiza na pagina de crise, MAS nao tem o botao de acesso rapido universal descrito no documento (apenas visivel no perfil se tiver plano).
- **Acao**: Adicionar acesso direto ao plano na navbar/bottom bar em momentos de crise.

---

# SECAO 2 — PLANO DE IMPLEMENTACAO ESTRUTURADO

Para cada gap, detalhe completo de implementacao.

---

## FASE 1 — FUNDACOES CRITICAS (Semanas 1-3)

### 2.1 GAP-01: Pesquisa Global com Filtros Emocionais

**Objetivo Funcional**: Permitir que utilizadores pesquisem posts, recursos e salas por keywords E por estado emocional.

**Impacto no Sistema**: Novo endpoint de pesquisa, nova view, nova rota. Nao altera funcionalidades existentes.

**Alteracoes de Arquitectura**:
- Novo `SearchController` com metodo `index` que agrega resultados de Posts, Resources e Rooms.
- Filtros: keyword (LIKE), tag emocional, tipo de conteudo, excluir conteudo sensivel.

**Alteracoes de Base de Dados**: Nenhuma. As colunas `tag`, `is_sensitive`, `content` ja existem.

**Novos Ficheiros**:
- `app/Http/Controllers/SearchController.php`
- `resources/views/search/index.blade.php`
- Rota: `GET /pesquisar` com query params `?q=&emotion=&type=&safe=`

**Refatoracoes Previas**: Nenhuma.

**Riscos Tecnicos**: Performance em pesquisa full-text com SQLite. Considerar indice FTS5 se escala justificar.

**Estrategia de Testes**: Feature test com diferentes combinacoes de filtros. Testar resultados vazios, pesquisa com acentos, exclusao de conteudo sensivel.

---

### 2.2 GAP-04: Check-in Pos-Leitura Automatico

**Objetivo Funcional**: Apos ler um post com tag "Desabafo" ou "Crise", mostrar discretamente um mini-questionario de 3 icones.

**Impacto no Sistema**: A rota `postCheckin` ja existe no `ForumController`. A migration `post_checkins` ja existe. Precisa de verificar a implementacao frontend e completar se necessario.

**Alteracoes de Arquitectura**:
- Verificar se `ForumController::postCheckin()` esta implementado.
- Adicionar componente Blade/Alpine inline no `forum/show.blade.php` que aparece apos scroll de post sensivel.
- Se o utilizador reportar distress, sugerir link para Zona Calma.

**Alteracoes de Base de Dados**: Nenhuma (tabela ja existe).

**Riscos Tecnicos**: Timing do popup — demasiado cedo e intrusivo, demasiado tarde e irrelevante. Usar IntersectionObserver para detetar que o utilizador leu o post (scroll ate ao fim).

**Estrategia de Testes**: Feature test para o endpoint. Browser test para o trigger de scroll.

---

### 2.3 GAP-06: Push Notifications via Web Push API

**Objetivo Funcional**: Enviar push notifications no browser para eventos criticos (crise de alguem seguido, resposta a post importante).

**Impacto no Sistema**: Novo canal de notificacao. Adiciona-se aos canais existentes (mail, database).

**Alteracoes de Arquitectura**:
- Instalar `laravel-notification-channels/webpush` via Composer.
- Adicionar `HasPushSubscriptions` trait ao User model.
- Criar Service Worker para receber pushes.
- Gerar VAPID keys no `.env`.
- Adicionar UI de opt-in no perfil (toggle "Receber notificacoes push").

**Alteracoes de Base de Dados**: Nova tabela `push_subscriptions` (vem com o package).

**Novos Ficheiros**:
- `public/sw.js` (Service Worker)
- `resources/js/push-subscription.js`
- Migration do package

**Refatoracoes Previas**: Nenhuma.

**Riscos Tecnicos**: Compatibilidade de browser (Safari tem limitacoes). VAPID keys precisam de ser geradas e mantidas seguras. Opt-in UX deve ser nao-intrusiva.

**Estrategia de Testes**: Testar subscricao, envio, e fallback quando push nao e suportado.

---

### 2.4 INC-03: Corrigir GamificationService vs Mission Schema

**Objetivo Funcional**: Alinhar o `GamificationService` com os nomes reais das colunas da tabela `missions`.

**Impacto no Sistema**: Corrige bug silencioso no progresso de missoes.

**Alteracoes**:
- Em `GamificationService::updateMissionProgress()`: mudar `->where('action_type', $actionType)` para `->where('type', $actionType)`
- Mudar `$mission->target_count` para `$mission->goal_count`

**Riscos Tecnicos**: Nenhum. E uma correcao direta.

---

## FASE 2 — EXPERIENCIA EMOCIONAL (Semanas 4-6)

### 2.5 GAP-08: Gradientes Dinamicos por Humor

**Objetivo Funcional**: O fundo do dashboard e da landing muda de gradiente conforme o humor mais recente.

**Impacto no Sistema**: Alteracao visual no `dashboard.blade.php`. Sem impacto backend.

**Alteracoes de Arquitectura**:
- No `DashboardController`, passar o `mood_level` mais recente para a view.
- Na view, usar CSS custom properties para definir cores do gradiente baseadas no mood.
- Mapa de cores: 1 (cinza-azulado) -> 2 (azul frio) -> 3 (neutro) -> 4 (verde) -> 5 (amarelo-quente).

**Novos Ficheiros**: Nenhum. Alteracao em views existentes.

**Riscos Tecnicos**: Nenhum. CSS puro.

---

### 2.6 GAP-07: Modo de Voz — Ditado de Entradas

**Objetivo Funcional**: Botao de microfone no diario que transcreve voz para texto via Web Speech API.

**Impacto no Sistema**: Apenas frontend. Zero alteracao backend.

**Alteracoes de Arquitectura**:
- Adicionar botao de microfone no `diary/index.blade.php`.
- JS Alpine component que usa `webkitSpeechRecognition` / `SpeechRecognition`.
- Texto transcrito e inserido no textarea do diario.
- Indicador visual de "a ouvir..." durante a transcricao.

**Riscos Tecnicos**: Web Speech API nao funciona em todos os browsers (Firefox desktop precisa de flag). Fallback gracioso: esconder o botao se API nao disponivel.

**Estrategia de Testes**: Manual em Chrome e Safari. Feature detection no JS.

---

### 2.7 GAP-10: Tour Contextual Nao-intrusivo

**Objetivo Funcional**: Tooltips organicos na primeira interacao com cada funcionalidade.

**Impacto no Sistema**: Novo sistema de tracking de features descobertas.

**Alteracoes de Arquitectura**:
- Novo campo JSON `discovered_features` no User (ou localStorage para abordagem mais simples).
- Blade component `<x-contextual-tip feature="diary" title="..." description="..." />`.
- O componente verifica se a feature ja foi vista; se nao, mostra tooltip com animacao suave.
- Ao clicar/fechar, marca como vista via AJAX ou localStorage.

**Alteracoes de Base de Dados**: Opcional — campo JSON no User ou usar localStorage.

**Riscos Tecnicos**: Excesso de tooltips pode ser irritante. Limitar a 1 tooltip por pagina.

---

### 2.8 GAP-12: Breadcrumb Emocional

**Objetivo Funcional**: Linha subtil no topo mostra "Zona Calma > Respiracao" em linguagem emocional.

**Impacto no Sistema**: Novo Blade component usado em todas as paginas.

**Alteracoes de Arquitectura**:
- Blade component `<x-emotional-breadcrumb :items="[['label' => 'A Fogueira', 'route' => 'rooms.index'], ['label' => 'Ansiedade Social']]" />`
- Cada view passa o breadcrumb contextual como slot ou prop.

**Novos Ficheiros**: `resources/views/components/emotional-breadcrumb.blade.php`

**Riscos Tecnicos**: Nenhum.

---

### 2.9 GAP-11: Temas de Interface por Sala

**Objetivo Funcional**: Dentro do chat, a paleta muda subtilmente conforme a cor da sala.

**Impacto no Sistema**: Alteracao na view `chat/show.blade.php`.

**Alteracoes**:
- No `ChatController::show()`, a `$room->color` ja e passada a view.
- Na view, usar CSS custom properties: `--room-accent: theme('colors.{{ $room->color }}.500')`.
- Aplicar aos elementos do header, borda de mensagens, botoes de reacao.

**Riscos Tecnicos**: Dinamismo de cor com Tailwind. Usar classes pre-definidas em vez de interpolacao em runtime (Tailwind purga classes nao usadas).

---

### 2.10 GAP-16: Calendario Emocional Portugues

**Objetivo Funcional**: Mensagens contextuais em datas com impacto emocional cultural.

**Impacto no Sistema**: Nova config + logica no dashboard.

**Alteracoes de Arquitectura**:
- Novo ficheiro `config/emotional-calendar.php` com array de datas:
  ```
  '11-01' => ['title' => 'Dia de Todos os Santos', 'message' => '...', 'type' => 'grief'],
  '11-02' => ['title' => 'Dia de Finados', ...],
  '12-24' => ['title' => 'Consoada', ...],
  '12-25' => ['title' => 'Natal', ...],
  ```
- No `DashboardController`, verificar se a data atual esta no calendario e passar a mensagem.
- Na view, mostrar um card especial se houver data relevante.

**Riscos Tecnicos**: Nenhum. Config estatica.

---

## FASE 3 — PLATAFORMA AVANCADA (Semanas 7-10)

### 2.11 GAP-02: PWA com Service Worker & Offline Mode

**Objetivo Funcional**: Instalar como app, aceder ao diario e zona calma sem internet.

**Impacto no Sistema**: Significativo — requer Service Worker, manifest.json, e estrategia de cache.

**Alteracoes de Arquitectura**:
- Criar `public/manifest.json` com icones, cores, nome.
- Criar `public/sw.js` com estrategia de cache:
  - Cache-first para assets estaticos (CSS, JS, fontes).
  - Network-first para API calls.
  - Offline fallback para paginas de diario e zona calma.
- Adicionar `<link rel="manifest">` no layout.
- No `diary/index.blade.php`, implementar `IndexedDB` ou `localStorage` para guardar rascunhos offline.
- Ao voltar online, sync automatico via background sync.

**Novos Ficheiros**:
- `public/manifest.json`
- `public/sw.js`
- `resources/js/offline-sync.js`
- Icones PWA (192x192, 512x512)

**Refatoracoes Previas**: Consolidar assets Vite para facilitar cache.

**Riscos Tecnicos**: Service Worker lifecycle e complexo. Cache invalidation e o problema classico. Testar em Chrome, Safari, Firefox. Safari tem limitacoes em background sync.

**Estrategia de Testes**: Lighthouse PWA audit. Teste manual de offline mode. Verificar que cache nao serve dados stale.

---

### 2.12 GAP-03: Previsao de Engagement Decrescente

**Objetivo Funcional**: Detetar utilizadores que estao a desligar-se e intervir proativamente.

**Impacto no Sistema**: Novo command scheduled + nova notificacao.

**Alteracoes de Arquitectura**:
- Novo `app/Console/Commands/DetectDisengagement.php` (cron diario).
- Algoritmo baseado em regras heuristicas (nao precisa de ML):
  - Dias desde ultima atividade > media pessoal * 2
  - Posts/comentarios da ultima semana < 50% da media
  - Nao abriu a plataforma em 3+ dias (tendo historico de uso diario)
- Se detetado: enviar notificacao calorosa ("Sentimos a tua falta. Quando quiseres, estamos aqui.")
- Novo `app/Notifications/GentleReEngagement.php`

**Alteracoes de Base de Dados**: Nenhuma. Usa dados existentes (`last_activity_at`, contagem de posts/logs).

**Riscos Tecnicos**: Falsos positivos — notificar alguem que simplesmente esta ocupado pode ser intrusivo. Respeitar `quiet_hours` e `wants_weekly_summary`. Limitar a 1 notificacao por semana.

**Estrategia de Testes**: Unit test para o algoritmo de detecao. Feature test para a notificacao.

---

### 2.13 GAP-05: Moderacao Assistida por IA (Dashboard Admin)

**Objetivo Funcional**: Dashboard Filament que prioriza conteudo por nivel de risco.

**Impacto no Sistema**: Novo widget no painel admin.

**Alteracoes de Arquitectura**:
- Novo Filament Widget `app/Filament/Widgets/ModerationQueueWidget.php`.
- O widget lista posts e mensagens recentes ordenados por `risk_level` (high -> medium -> low).
- Integrar com `CBTAnalysisService` — armazenar `risk_level` e `sentiment` na tabela de posts/mensagens.
- Adicionar acoes rapidas: Aprovar, Remover, Shadowban autor.

**Alteracoes de Base de Dados**:
- Adicionar `risk_level` (enum: low/medium/high) e `sentiment` (enum: positive/neutral/distress) a tabela `posts`.
- Adicionar `risk_level` a tabela `messages` (ja tem `is_sensitive`, expandir).

**Riscos Tecnicos**: Latencia da API OpenAI no fluxo de criacao de posts. Resolver com queue job — classificar assincronamente e atualizar o `risk_level` depois do broadcast.

---

### 2.14 GAP-13: Conexao por Experiencia Partilhada

**Objetivo Funcional**: Sugerir utilizadores com experiencias semelhantes para conexao opt-in.

**Impacto no Sistema**: Nova feature de matching.

**Alteracoes de Arquitectura**:
- Novo `app/Services/ExperienceMatchingService.php`.
- Algoritmo baseado em:
  - Tags emocionais em comum (`emotional_tags`)
  - Salas visitadas em comum (tabela `room_visits`)
  - Tags de posts em comum
- Score de similaridade > threshold = sugestao.
- Nova notificacao: "Alguem na comunidade passou por algo parecido. Queres conhecer?"
- Ambos precisam de aceitar (duplo opt-in).

**Alteracoes de Base de Dados**:
- Nova tabela `experience_connections`: `user_id`, `suggested_user_id`, `score`, `status` (pending/accepted/declined), timestamps.

**Riscos Tecnicos**: Privacidade — nunca revelar a identidade antes de duplo opt-in. Usar pseudonimos no match. Limitar a 1 sugestao por semana.

---

### 2.15 GAP-14: Expressao Artistica ("The Wall")

**Objetivo Funcional**: Seccao para partilhar imagens/desenhos que representam o estado emocional.

**Impacto no Sistema**: Novo modulo completo.

**Alteracoes de Arquitectura**:
- Novo `app/Http/Controllers/WallController.php` (index, store, react, report).
- Novo `app/Models/WallPost.php` com campos: `user_id`, `image_path`, `caption` (opcional, max 100 chars), `is_sensitive`.
- Upload de imagens via Laravel Storage (S3/local).
- Galeria com masonry layout.
- Moderacao: todas as imagens passam por aprovacao manual (ou API de moderacao visual).

**Alteracoes de Base de Dados**:
- Nova tabela `wall_posts`: id, user_id, image_path, caption, is_sensitive, is_approved, timestamps.

**Riscos Tecnicos**: Moderacao de imagens e muito mais complexa que texto. Risco de conteudo inapropriado. Mitigar com aprovacao manual ou API tipo AWS Rekognition para detecao automatica de conteudo explicito.

---

## FASE 4 — ESCALA E POLISH (Semanas 11+)

### 2.16 GAP-15: Encriptacao E2E (Buddy Messages)

**Objetivo Funcional**: Mensagens privadas encriptadas end-to-end.

**Impacto no Sistema**: ALTO. Requer mudanca significativa no fluxo de mensagens buddy.

**Alteracoes de Arquitectura**:
- Usar `sodium_crypto_box_keypair()` do PHP (libsodium).
- Cada utilizador gera um par de chaves no registo. Public key na DB, private key encriptada com password do user (ou derivada via Argon2).
- Mensagens encriptadas com chave publica do destinatario antes de guardar.
- Desencriptadas no frontend com chave privada do remetente.
- O servidor NUNCA tem acesso ao plaintext.

**Alteracoes de Base de Dados**:
- Adicionar `public_key` e `encrypted_private_key` ao User.
- Adicionar `encrypted_content` ao Message (para mensagens em salas privadas).

**Riscos Tecnicos**: Perda de chave privada = perda permanente de mensagens. Key rotation. Performance da encriptacao em bulk.

---

### 2.17 GAP-21: Paginacao por Cursor

**Objetivo Funcional**: Performance constante independentemente do tamanho do dataset.

**Alteracoes**:
- `ForumController::index()`: mudar `->get()` para `->cursorPaginate(15)`.
- `ChatController::show()`: manter `take(50)` mas implementar "carregar mais" com cursor.
- Views: adicionar infinite scroll com Alpine.js + IntersectionObserver.

**Riscos Tecnicos**: Cursor pagination nao suporta "ir para pagina X". Adequado para feeds infinitos.

---

### 2.18 GAP-20: A/B Testing com Laravel Pennant

**Objetivo Funcional**: Testar diferentes versoes de intervencoes.

**Alteracoes**:
- `composer require laravel/pennant`
- Definir features em `app/Features/`.
- Exemplo: `WelcomeMessageVariant` — testar qual mensagem de boas-vindas reduz abandono.
- Dashboard admin para ver resultados.

---

### 2.19 Remaining Gaps (GAP-17 a GAP-28)

**Sons de Portugal (GAP-17)**: Novo modulo `SoundLibrary` com player integrado na Zona Calma. Requer assets de audio.

**Recomendacao Contextual (GAP-18)**: Novo `RecommendationService` com regras heuristicas baseadas em mood + hora + historico.

**Gestos Mobile (GAP-19)**: Hammer.js ou touch events nativos para swipe-to-save no forum.

**Lazy Loading (GAP-22)**: Adicionar `loading="lazy"` a todos os `<img>` de avatar. 10 minutos de trabalho.

**Multilinguismo (GAP-23)**: Mover todos os textos hardcoded para `lang/pt/`. Criar `lang/en/` como fallback.

**Cache Redis (GAP-24)**: `Cache::remember()` nos queries mais pesados do forum e dashboard. Invalidar no create/update.

**Funil Emocional (GAP-25)**: Novo `AnalyticsService` que regista eventos (crise_view, breathe_start, breathe_complete, leave).

**Temperatura Comunitaria (GAP-26)**: Novo Filament Widget com semaforo. Job horario que calcula o indice.

**Mapa de Jornada Admin (GAP-27)**: Novo Filament Widget com timeline de acoes do utilizador selecionado.

**Relatorio Publico (GAP-28)**: Nova pagina publica `/comunidade/impacto` com estatisticas anonimas.

---

# SECAO 3 — MELHORIAS ARQUITETURAIS FUTURAS

## 3.1 Padronizacao de Naming

**Problema Atual**: Inconsistencia entre PT e EN nos nomes:
- Controllers: mix de `ForumController` (EN) com metodos em PT (`comentar`, `reagir`)
- Routes: mix de `/mural` (PT) com `/chat` (EN)
- Models: todos em EN (correto)
- Views: mix de `diary/` (EN) com `calm/` (EN) — OK, mas nao alinhado com URLs PT

**Recomendacao**:
- **Models, Controllers, Services**: SEMPRE em ingles (padrao Laravel).
- **URLs (routes)**: SEMPRE em portugues (UX para o utilizador).
- **Nomes de metodos**: SEMPRE em ingles (padrao PHP/Laravel).
- **Views**: Em ingles (padrao Laravel), alinhado com controllers.
- **Blade texts/labels**: Em portugues (visivel ao utilizador).

## 3.2 Estrutura de Pastas

**Estado Atual**: Boa. Os Filament resources ja seguem um padrao organizado.

**Recomendacao para novas features**:
```
app/
  Http/Controllers/     # Controllers (1 por feature)
  Services/             # Business logic (nao em controllers)
    CBTAnalysisService.php
    GamificationService.php
    ExperienceMatchingService.php   # NOVO
    RecommendationService.php       # NOVO
    AnalyticsService.php            # NOVO
  Models/               # Eloquent models
  Events/               # Broadcasting events
  Notifications/        # Mail + Push + Database notifications
  Console/Commands/     # Scheduled tasks
  Filament/Resources/   # Admin (ja organizado por feature)
```

## 3.3 Separacao de Responsabilidades

**Problema Atual**: Alguns controllers tem logica de negocio que deveria estar em Services.
- `ForumController` tem logica de shadowban, moderacao, analise de sentimento.
- `ChatController` tem logica de moderacao, mute, reports.

**Recomendacao**:
- Extrair `ModerationService` que centraliza: shadowban, mute, delete, report, log.
- Extrair `ForumService` para logica de posts (criar, reagir, comentar).
- Controllers ficam finos: validacao -> service -> response.

## 3.4 Camadas de Abstracao

**Onde criar abstracoes**:

1. **NotificationService**: Centralizar logica de "enviar notificacao SE o utilizador esta em quiet hours, respeitar opt-in, escolher canal". Atualmente espalhada por varios controllers.

2. **ContentModerationPipeline**: Pipeline que todo o conteudo (post, comentario, mensagem) passa antes de ser guardado:
   - Etapa 1: Detecao de crise (CBTAnalysisService)
   - Etapa 2: Classificacao de risco
   - Etapa 3: Alerta a moderadores se necessario
   - Etapa 4: Broadcast se aplicavel

3. **UserStateService**: Centralizar verificacoes de estado do utilizador (banido? shadowbanido? hibernado? em quiet hours? onboarding completo?). Atualmente verificacoes espalhadas por middleware e controllers.

## 3.5 Preparacao para Escalar

1. **Queue Jobs**: Mover TODAS as operacoes de IA (CBTAnalysisService calls) para queue jobs. Atualmente bloqueiam o request.
2. **Database**: Preparar para migrar de SQLite para PostgreSQL. Evitar queries SQLite-especificas.
3. **Cache Strategy**: Definir TTLs por tipo de conteudo:
   - Rooms list: 5 min (ja feito)
   - Forum posts populares: 2 min
   - User profile: 10 min
   - Analytics/stats: 1 hora
4. **Rate Limiting**: Ja implementado com throttle middleware — adequado.

---

# SECAO 4 — PREPARACAO PARA IMPLEMENTACAO POR IA

## 4.1 Estrutura para Implementacao Rapida

Para que uma IA (como eu) consiga implementar features rapidamente no futuro, o projeto deve manter:

### Ficheiro ARCHITECTURE.md na raiz
Conteudo recomendado:
- Mapa de todas as rotas com breve descricao
- Lista de todos os Models com relacoes
- Lista de todos os Services com responsabilidade
- Convencoes de naming
- Stack tecnologica (Laravel 11, Reverb, Filament 4, Alpine.js, Tailwind)
- Como correr testes
- Como correr migrations

### Ficheiro CONVENTIONS.md
- Controllers: fino, delega para services
- Services: toda a logica de negocio
- Models: relacoes, casts, accessors. Sem logica de negocio.
- Views: Blade + Alpine.js. Sem jQuery.
- Admin: Filament Resources com estrutura Resource/Pages/Schemas/Tables
- Notificacoes: sempre via classe Notification (nunca inline)
- Broadcasting: sempre via classe Event

### Testes como Contrato
Cada feature deveria ter pelo menos:
- 1 Feature test para o happy path
- 1 Feature test para o caso de erro/edge case
- Tests servem como documentacao executavel que a IA pode correr para verificar regressoes

## 4.2 Reducao de Risco de Regressoes

1. **Testes Automatizados**: Atualmente NAO existem testes no projeto. Esta e a maior vulnerabilidade. Cada nova feature introduzida por IA pode partir funcionalidade existente sem que ninguem saiba.

   **Recomendacao**: Criar uma suite minima de smoke tests:
   - Todas as paginas autenticadas devolvem 200
   - Criar post no forum funciona
   - Criar daily log funciona
   - Enviar mensagem no chat funciona
   - Criar room no admin funciona
   - Login e registo funcionam

2. **Validacao Pre-Commit**: Hook de pre-commit que corre `php artisan route:list` (verifica que routes nao partiram) e `php -l` em ficheiros alterados.

3. **Migrations Idempotentas**: Usar `Schema::hasColumn()` e `Schema::hasTable()` para prevenir erros em re-runs.

## 4.3 Trabalho Modular e Previsivel

Para que a IA trabalhe de forma modular:

1. **1 Feature = 1 Branch = 1 PR**: Cada gap deste documento deve ser implementado isoladamente.

2. **Checklist por Feature**:
   - [ ] Migration (se aplicavel)
   - [ ] Model (se novo)
   - [ ] Service (se logica complexa)
   - [ ] Controller + Routes
   - [ ] View(s)
   - [ ] Filament Resource (se gerivel por admin)
   - [ ] Notification (se aplicavel)
   - [ ] Event/Broadcast (se tempo real)
   - [ ] Tests
   - [ ] PHP syntax check
   - [ ] Commit

3. **Contexto Minimo para IA**: Cada pedido de implementacao deveria referenciar:
   - O GAP-XX deste documento
   - Os ficheiros que vao ser alterados
   - O resultado esperado

## 4.4 Ordem de Implementacao Recomendada

```
FASE 1 (Critico):
  INC-03 -> GAP-04 -> GAP-01 -> GAP-06 -> GAP-05

FASE 2 (Experiencia):
  GAP-08 -> GAP-07 -> GAP-12 -> GAP-11 -> GAP-16 -> GAP-10

FASE 3 (Avancado):
  GAP-02 -> GAP-03 -> GAP-13 -> GAP-14

FASE 4 (Escala):
  GAP-21 -> GAP-24 -> GAP-22 -> GAP-15 -> GAP-20
```

Cada fase pode ser executada sequencialmente pela IA com instrucoes tipo:
"Implementa o GAP-04 (Check-in Pos-Leitura) conforme descrito no PLANO_ESTRATEGICO.md, Secao 2.2."

---

# APENDICE A — TABELA RESUMO

| ID | Funcionalidade | Prioridade | Esforco | Fase | Dependencias |
|----|---------------|-----------|---------|------|-------------|
| INC-03 | Corrigir GamificationService | CRITICA | Baixo | 1 | Nenhuma |
| GAP-04 | Check-in Pos-Leitura | ALTA | Baixo | 1 | post_checkins table |
| GAP-01 | Pesquisa Global | ALTA | Medio | 1 | Nenhuma |
| GAP-06 | Push Notifications | ALTA | Medio | 1 | Service Worker |
| GAP-05 | Moderacao IA (Admin) | ALTA | Medio | 1 | Filament, CBT Service |
| GAP-08 | Gradientes por Humor | MEDIA | Baixo | 2 | DailyLog |
| GAP-07 | Modo de Voz (Ditado) | MEDIA | Baixo | 2 | Web Speech API |
| GAP-12 | Breadcrumb Emocional | MEDIA | Baixo | 2 | Nenhuma |
| GAP-11 | Temas por Sala | MEDIA | Baixo | 2 | Room.color |
| GAP-16 | Calendario Emocional PT | MEDIA | Baixo | 2 | Config file |
| GAP-10 | Tour Contextual | MEDIA | Medio | 2 | localStorage ou DB |
| GAP-02 | PWA + Offline | ALTA | Alto | 3 | Service Worker, manifest |
| GAP-03 | Detecao Desengagement | ALTA | Medio | 3 | Dados existentes |
| GAP-13 | Conexao Partilhada | MEDIA | Alto | 3 | Nova tabela + service |
| GAP-14 | The Wall (Arte) | MEDIA | Alto | 3 | Upload, moderacao |
| GAP-21 | Cursor Pagination | BAIXA | Baixo | 4 | Nenhuma |
| GAP-24 | Cache Redis | BAIXA | Baixo | 4 | Redis |
| GAP-22 | Lazy Loading | BAIXA | Minimo | 4 | Nenhuma |
| GAP-15 | E2E Encryption | MEDIA | Alto | 4 | libsodium |
| GAP-20 | A/B Testing | BAIXA | Medio | 4 | Laravel Pennant |
| GAP-17 | Sons de Portugal | MEDIA | Medio | 4 | Audio assets |
| GAP-18 | Recomendacao Contextual | MEDIA | Medio | 4 | Service novo |
| GAP-19 | Gestos Mobile | BAIXA | Medio | 4 | Touch events |
| GAP-23 | Multilinguismo | BAIXA | Alto | 4 | lang/ files |
| GAP-25 | Funil Emocional | BAIXA | Medio | 4 | Analytics service |
| GAP-26 | Temperatura Comunidade | BAIXA | Medio | 4 | Job + widget |
| GAP-27 | Mapa Jornada Admin | BAIXA | Medio | 4 | Filament widget |
| GAP-28 | Relatorio Publico | BAIXA | Medio | 4 | Pagina publica |

---

**FIM DO DOCUMENTO**

Este plano deve ser usado como referencia para todas as implementacoes futuras.
Cada GAP-XX pode ser referenciado diretamente em pedidos de implementacao.
