# 04 — Funcionalidades a Migrar na Fase Inicial

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

### 2. Dashboard

**Justificação:** É a home. O utilizador precisa de contexto imediato sobre o seu estado e ações disponíveis.

| Aspeto | Detalhe |
|--------|---------|
| O que migra | Mood atual, flames, streak, missões, insight AI, notificações |
| Melhoria nativa | Pull-to-refresh, cards animados, widget Android |
| Endpoints necessários | `GET /api/v1/dashboard` (novo, agregado) |
| Offline | Cache da última versão em Room DB |
| Prioridade | Crítica |

### 3. Diário emocional

**Justificação:** É a funcionalidade mais pessoal e frequente. Auto-save e offline são essenciais.

| Aspeto | Detalhe |
|--------|---------|
| O que migra | Mood level (1-5), tags, nota, CBT insight, histórico |
| Melhoria nativa | Slider com haptic, auto-save local, sync on connectivity |
| Endpoints necessários | `GET /api/v1/diary`, `POST /api/v1/diary` (novos) |
| Offline | Draft guardado em Room DB, sync quando houver rede |
| Prioridade | Crítica |

### 4. Zona Calma (core)

**Justificação:** Em momento de crise, o utilizador precisa de acesso imediato a técnicas de regulação. Não pode depender de rede.

| Aspeto | Detalhe |
|--------|---------|
| O que migra | Hub, grounding 5-4-3-2-1, respiração guiada, sintonia/heartbeat, plano de crise |
| Melhoria nativa | Vibração háptica sincronizada, animações Canvas, chamada direta via Intent |
| Endpoints necessários | `GET /api/v1/calm-zone/vault` (novo), `GET /api/v1/user/safety-plan` (novo) |
| Offline | Exercícios funcionam 100% offline. Cofre e plano de crise cacheados localmente |
| Prioridade | Crítica |

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

| Endpoint | Método | Notas |
|----------|--------|-------|
| `POST /api/v1/auth/register` | POST | Registo |
| `POST /api/v1/auth/login` | POST | Login → token |
| `POST /api/v1/auth/logout` | POST | Revoke token |
| `POST /api/v1/auth/forgot-password` | POST | Reset link |
| `POST /api/v1/onboarding` | POST | Guardar respostas |
| `GET /api/v1/dashboard` | GET | Dados agregados |
| `GET /api/v1/diary` | GET | Histórico de entradas |
| `POST /api/v1/diary` | POST | Nova entrada |
| `GET /api/v1/profile` | GET | Dados do perfil |
| `PATCH /api/v1/profile` | PATCH | Atualizar perfil |
| `GET /api/v1/missions` | GET | Missões do dia |
| `GET /api/v1/calm-zone/vault` | GET | Items do cofre |
| `POST /api/v1/calm-zone/vault` | POST | Adicionar ao cofre |
| `DELETE /api/v1/calm-zone/vault/{id}` | DELETE | Remover do cofre |
| `GET /api/v1/user/safety-plan` | GET | Plano de segurança |
| `GET /api/v1/notifications` | GET | Notificações |
| `POST /api/v1/notifications/mark-read` | POST | Marcar como lidas |

**Total: ~17 endpoints para fase 1.**

---

*Próximo: [05-adaptacoes-nativas.md](05-adaptacoes-nativas.md) — Adaptações necessárias para paradigma mobile nativo.*
