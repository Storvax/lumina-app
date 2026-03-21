# 04 — Funcionalidades a Migrar na Fase Inicial

## Contexto

Este documento define o escopo da Fase 1 da app Android nativa. Baseia-se nas conclusões de:
- [01-estado-atual.md](01-estado-atual.md): gaps bloqueantes (no API, no Sanctum, no offline)
  e 7 services server-side reutilizáveis sem alteração
- [02-inventario-funcional.md](02-inventario-funcional.md): ~65 rotas B2C candidatas a API
- [03-mapeamento-funcional.md](03-mapeamento-funcional.md): priorização P0/P1 e dependências
  entre módulos

A app Android serve **exclusivamente o perfil B2C**. Portais PRO, Corporate, e Filament permanecem
web-only (ref. [06-web-first-admin.md](06-web-first-admin.md)).
Ref. [17-estrategia-b2c-pro-corporate.md](17-estrategia-b2c-pro-corporate.md) para posicionamento
completo por perfil.

---

## Observações do estado atual

- **7 services server-side reutilizáveis** sem alteração: CBTAnalysisService, GamificationService,
  RecommendationService, ExperienceMatchingService, AnalyticsService, EncryptionService,
  FeatureFlagService. A app consome-os via API.
- **API layer é o gap bloqueante** — nenhum endpoint existe (`routes/api.php` não tem rotas).
  Sanctum não está configurado. Todos os endpoints são novos.
- **Crisis detection é server-side** (3 camadas: keywords → intent patterns → GPT-4o-mini com 3s timeout).
  A Zona Calma pode ser offline-first para exercícios e depender de API apenas para safety plan sync.
- **Onboarding routing é server-side** — lógica de routing contextual (intenção → dashboard adaptado)
  deve ser replicada no app para navegação contextual pós-onboarding.
- **Dashboard aggregation é server-side** — `DashboardController` agrega mood, flames, streak,
  missões, AI insight, notificações. Necessário endpoint único `/api/v1/dashboard`.

---

## App única com módulos condicionais

**Decisão: UMA ÚNICA APP para B2C.**

- Não criar apps separadas por perfil.
- Futuro PRO: módulo condicional ativado por role (`feature-therapist`, Fase 4+).
  Ref. [09-modularizacao.md](09-modularizacao.md) para estratégia de modularização.
- Justificação: simplifica distribuição no Play Store, gestão de versões, e codebase.
  Modularização permite ativar/desativar módulos por role sem bloat no APK.

---

## O que NÃO migrar 1:1

Estas funcionalidades existem no web mas **não devem ser copiadas** — exigem redesign:

| Funcionalidade | Web (atual) | Android (redesign) | Tipo |
|---------------|------------|-------------------|------|
| Autenticação | Session cookies (Breeze) | Sanctum tokens + EncryptedSharedPreferences + BiometricPrompt | Reconceção |
| Onboarding | Redirect-based routing (`OnboardingController`) | In-app navigation com Pager + animações + haptic. Lógica de routing replicada localmente | Reconceção |
| Dashboard | Agregação em `DashboardController` com múltiplas queries | Endpoint único `/api/v1/dashboard` agregado. Room cache. Pull-to-refresh | Adaptação |
| Chat (Fase 3) | Laravel Echo + Reverb (session-based) | OkHttp WebSocket nativo (token-based) | Reconceção total |
| Acessibilidade | Toggles Lumina (dyslexic font, text size, reduced motion) | Respeitar system preferences Android + opções extra Lumina. TalkBack-first | Reconceção |
| Passaporte emocional (Fase 2+) | Gerado server-side como view | Ver resumo na app + PDF gerado server-side via download/share | Adaptação |
| Safe House | Double Escape → redirect Google | Double-tap → close app + clear recents + clear notificações | Reconceção |

Ref. [03-mapeamento-funcional.md](03-mapeamento-funcional.md) para classificação completa
(Direto, Adaptação, Reconceção, Novo, Web-first).

---

## Princípio orientador

A primeira fase deve entregar uma app que:
1. **Funciona de forma autónoma** — O utilizador consegue registar-se, fazer onboarding, e usar funcionalidades core sem precisar do site.
2. **Demonstra valor nativo** — A experiência deve ser visivelmente melhor do que usar o site no browser mobile.
3. **É emocionalmente segura** — As funcionalidades de crise e regulação devem estar presentes desde o início.
4. **É tecnicamente fundacional** — Auth, networking, offline, e navegação ficam resolvidos para as fases seguintes.

---

## Fase 1A — Fundação técnica (pré-requisito)

Antes de qualquer funcionalidade visível, a fase 1A resolve a infraestrutura:

| Componente | Descrição | Prioridade |
|-----------|-----------|-----------|
| Projeto Android (Kotlin + Compose) | Scaffold inicial com Material 3 | Crítica |
| Autenticação (Sanctum tokens) | Login, registo, refresh, secure storage | Crítica |
| Camada de networking (Retrofit) | Client HTTP com interceptors, error handling | Crítica |
| Navegação (Navigation Compose) | Grafo de navegação principal | Crítica |
| Base de dados local (Room) | Schema inicial para cache e offline | Crítica |
| Design system Lumina | Cores, tipografia, componentes base | Crítica |
| CI básico | Build + lint no GitHub Actions | Alta |

**Critério de conclusão:** A app compila, autentica contra o backend Laravel, e navega entre telas skeleton.

---

## Fase 1B — Funcionalidades core

### 1. Onboarding (3 passos)

**Justificação:** É a primeira experiência do utilizador. Deve ser impecável.

| Aspeto | Detalhe |
|--------|---------|
| O que migra | Wizard de 3 perguntas: intenção, humor, preferência |
| Melhoria nativa | Pager com animações, haptic feedback, progress dots |
| Endpoint necessário | `POST /api/v1/onboarding` (novo) |
| Offline | Não aplicável (primeiro uso requer rede) |
| Prioridade | Crítica |
| Risco | **Médio** — depende de replicar routing logic do backend (`OnboardingController` decide para onde redirecionar com base na intenção). Lógica deve ser mapeada e replicada no app |

### 2. Dashboard

**Justificação:** É a home. O utilizador precisa de contexto imediato sobre o seu estado e ações disponíveis.

| Aspeto | Detalhe |
|--------|---------|
| O que migra | Mood atual, flames, streak, missões, insight AI, notificações |
| Melhoria nativa | Pull-to-refresh, cards animados, widget Android |
| Endpoints necessários | `GET /api/v1/dashboard` (novo, agregado) |
| Offline | Cache da última versão em Room DB |
| Prioridade | Crítica |
| Risco | **Alto** — depende de endpoint agregado que não existe. `DashboardController` agrega dados de múltiplas fontes (mood, flames, missões, AI insight). Se endpoint não pronto, dashboard mostra dados parciais (graceful degradation) |

### 3. Diário emocional

**Justificação:** É a funcionalidade mais pessoal e frequente. Auto-save e offline são essenciais.

| Aspeto | Detalhe |
|--------|---------|
| O que migra | Mood level (1-5), tags, nota, CBT insight, histórico |
| Melhoria nativa | Slider com haptic, auto-save local, sync on connectivity |
| Endpoints necessários | `GET /api/v1/diary`, `POST /api/v1/diary` (novos) |
| Offline | Draft guardado em Room DB, sync quando houver rede |
| Prioridade | Crítica |
| Risco | **Baixo** — funcionalidade simples, offline-first. CRUD standard com Room DB |

### 4. Zona Calma (core)

**Justificação:** Em momento de crise, o utilizador precisa de acesso imediato a técnicas de regulação. Não pode depender de rede.

| Aspeto | Detalhe |
|--------|---------|
| O que migra | Hub, grounding 5-4-3-2-1, respiração guiada, sintonia/heartbeat, plano de crise |
| Melhoria nativa | Vibração háptica sincronizada, animações Canvas, chamada direta via Intent |
| Endpoints necessários | `GET /api/v1/calm-zone/vault` (novo), `GET /api/v1/user/safety-plan` (novo) |
| Offline | Exercícios funcionam 100% offline. Cofre e plano de crise cacheados localmente |
| Prioridade | Crítica |
| Risco | **Baixo** — exercícios core são 100% offline (sem dependência de API). Cofre e safety plan dependem de API para sync mas funcionam com cache. Tuning de animações/haptics pode consumir mais tempo do que estimado |

**Exercícios incluídos na fase 1:**
- Grounding 5-4-3-2-1 (100% offline, sem backend)
- Respiração guiada (100% offline, animação + vibração)
- Sintonia/heartbeat (100% offline, vibração + animação)
- Plano de crise (cache local + contactos de emergência)
- Cofre pessoal (sync com backend, funciona offline)

**Exercícios adiados para fases seguintes:**
- Sons relaxantes (requer assets de áudio + streaming)
- Combustão/venting (complexidade de animação menor prioridade)
- Reflexão AI (requer rede)
- Playlist comunitária (requer rede + APIs externas)

### 5. Perfil básico

**Justificação:** O utilizador precisa de ver e editar os seus dados, e sentir ownership sobre o perfil.

| Aspeto | Detalhe |
|--------|---------|
| O que migra | Ver perfil (pseudónimo, flames, level), editar dados básicos, logout |
| Melhoria nativa | Biometria para ações sensíveis |
| Endpoints necessários | `GET /api/v1/profile`, `PATCH /api/v1/profile` (novos) |
| Offline | Cache do perfil em Room DB |
| Prioridade | Alta |
| Risco | **Baixo** — CRUD standard. User model tem 42+ campos mas o perfil expõe apenas um subconjunto |

**Adiado para fases seguintes:**
- Safety plan editing
- Milestones
- Passaporte emocional
- Preferências de notificação detalhadas

### 6. Gamificação básica

**Justificação:** Flames, streaks e missões são transversais a toda a experiência. Devem estar presentes desde o início.

| Aspeto | Detalhe |
|--------|---------|
| O que migra | Flames counter, streak display, missões do dia com progresso |
| Melhoria nativa | Animações de flame, haptic ao completar missão |
| Endpoints necessários | `GET /api/v1/missions` (novo), já incluído no dashboard aggregation |
| Offline | Cache das missões do dia |
| Prioridade | Alta |
| Risco | **Médio** — gamificação é transversal (flames incrementam ao guardar diário, completar missão, etc.). Requer triggers consistentes em todos os features modules. Se triggers incorretos, flames dessincronizam |

---

## Funcionalidades explicitamente excluídas da fase 1

| Funcionalidade | Razão do adiamento | Fase estimada |
|---------------|-------------------|--------------|
| Fórum (Mural) | Complexidade alta (posts, comentários, reações, moderação, áudio) | Fase 2 |
| Chat (Fogueira) | Requer WebSocket nativo + UI complexa | Fase 3 |
| Buddy system | Depende de chat funcional | Fase 3 |
| Auto-avaliação (PHQ-9, GAD-7) | Funcional mas não urgente para MVP | Fase 2 |
| Biblioteca | Baixa complexidade mas baixa prioridade no MVP | Fase 2 |
| The Wall | Requer upload de imagens + galeria | Fase 2 |
| Pesquisa global | Requer múltiplos módulos implementados | Fase 2 |
| Sons relaxantes | Requer streaming de áudio + assets | Fase 2 |
| Reflexão AI | Requer rede, complexidade de chat UI | Fase 2 |
| Pacto comunitário | Requer fórum | Fase 2 |
| Portal terapeuta | Web-first | — |
| Portal corporate | Web-first | — |
| Push notifications (FCM) | Importante mas não bloqueante para MVP | Fase 2 |

---

## Critérios de conclusão da Fase 1

### Funcionais
- [ ] Utilizador consegue registar-se e fazer login
- [ ] Onboarding completo com routing contextual
- [ ] Dashboard mostra mood, flames, streak, missões
- [ ] Diário permite criar entrada com mood, tags, nota
- [ ] Diário mostra histórico dos últimos 7 dias
- [ ] Zona Calma acessível com grounding, respiração, heartbeat
- [ ] Plano de crise acessível com contactos de emergência
- [ ] Cofre pessoal funcional (CRUD)
- [ ] Perfil visível e editável
- [ ] Flames e streaks atualizados ao completar ações
- [ ] Missões do dia visíveis com progresso

### Técnicos
- [ ] Auth por token (Sanctum) funcional
- [ ] Offline: diário com auto-save, zona calma funcional sem rede
- [ ] Cache: dashboard, perfil, missões cacheados em Room DB
- [ ] Error handling consistente e acolhedor
- [ ] Loading states em todas as operações de rede
- [ ] Pull-to-refresh nos ecrãs principais
- [ ] Navegação fluida entre todas as telas
- [ ] Design system Lumina aplicado (cores, tipografia, spacing)
- [ ] Acessibilidade básica (TalkBack, contrast, touch targets 48dp)

### Emocionais e de produto
- [ ] Linguagem PT-PT em toda a interface
- [ ] Tom acolhedor nos estados de erro
- [ ] Transições suaves e calmas
- [ ] Touch targets adequados para contexto de crise (≥48dp)
- [ ] Sem mecânicas de culpa (streaks reset gentilmente)

---

## Estimativa de endpoints API necessários para Fase 1

| Endpoint | Método | Feature | Backend gap (ref. 11) | Depende de |
|----------|--------|---------|----------------------|-----------|
| `POST /api/v1/auth/register` | POST | Auth | Sanctum não configurado | Sanctum setup |
| `POST /api/v1/auth/login` | POST | Auth | Sanctum não configurado | Sanctum setup |
| `POST /api/v1/auth/logout` | POST | Auth | Sanctum não configurado | Sanctum setup |
| `POST /api/v1/auth/forgot-password` | POST | Auth | Endpoint não existe | Sanctum setup |
| `POST /api/v1/auth/refresh` | POST | Auth | Endpoint não existe | Sanctum setup |
| `POST /api/v1/onboarding` | POST | Onboarding | Endpoint não existe | Auth funcional |
| `GET /api/v1/dashboard` | GET | Dashboard | Endpoint agregado não existe | Auth funcional |
| `GET /api/v1/diary` | GET | Diário | Endpoint não existe | Auth funcional |
| `POST /api/v1/diary` | POST | Diário | Endpoint não existe | Auth funcional |
| `GET /api/v1/profile` | GET | Perfil | Endpoint não existe | Auth funcional |
| `PATCH /api/v1/profile` | PATCH | Perfil | Endpoint não existe | Auth funcional |
| `GET /api/v1/missions` | GET | Gamificação | Endpoint não existe | Auth funcional |
| `GET /api/v1/calm-zone/vault` | GET | Zona Calma | Endpoint não existe | Auth funcional |
| `POST /api/v1/calm-zone/vault` | POST | Zona Calma | Endpoint não existe | Auth funcional |
| `DELETE /api/v1/calm-zone/vault/{id}` | DELETE | Zona Calma | Endpoint não existe | Auth funcional |
| `GET /api/v1/user/safety-plan` | GET | Zona Calma | Endpoint não existe | Auth funcional |
| `GET /api/v1/notifications` | GET | Transversal | Endpoint não existe | Auth funcional |
| `POST /api/v1/notifications/mark-read` | POST | Transversal | Endpoint não existe | Auth funcional |

**Total: ~18 endpoints para Fase 1.** Todos dependem de Sanctum estar configurado no backend —
este é o blocker #1. Ref. [11-backend-gaps.md](11-backend-gaps.md) para gap analysis completo.

---

## Riscos de atraso da Fase 1

| Risco | Probabilidade | Impacto | Mitigação |
|-------|-------------|---------|-----------|
| Sanctum não configurado no backend | Alta (é gap conhecido) | **Crítico** — bloqueia TODOS os endpoints | Prioridade #1 do backend. Sem Sanctum, nenhum desenvolvimento Android avança para além de UI mockada |
| Endpoint agregado de dashboard não pronto | Média | Alto | Dashboard pode mostrar dados parciais (graceful degradation): flames e streak de cache, missões de endpoint separado. Degradar com elegância |
| Room DB schema não alinhado com API response | Média | Médio | Definir contratos API (DTOs) antes de implementar Room entities. Mappers absorvem diferenças. Mas diferenças grandes = mappers complexos |
| Tuning de animações/haptics na Zona Calma | Alta | Baixo | Iterar após funcionalidade base estar pronta. Animações são polish, não blocker. Valores iniciais: baseados em apps de referência (Calm, Headspace) |
| Lógica de onboarding routing difícil de replicar | Média | Médio | Mapear decision tree do `OnboardingController` antes de implementar. Se necessário, API pode devolver routing hints |
| Gamificação triggers inconsistentes entre features | Média | Médio | Centralizar tracking em `GamificationRepository` único. Todos os features usam o mesmo entry point. Testar com cenários cross-feature |

---

*Próximo: [05-adaptacoes-nativas.md](05-adaptacoes-nativas.md) — Adaptações necessárias para paradigma mobile nativo.*
