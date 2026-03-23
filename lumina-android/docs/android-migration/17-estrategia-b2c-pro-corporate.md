# 17 — Estratégia B2C, PRO e Corporate no Contexto Android

## Contexto

A Lumina opera em 3 frentes:
1. **B2C** — Utilizadores finais (saúde mental pessoal)
2. **PRO** — Terapeutas (gestão de pacientes, triagem, exercícios guiados)
3. **Corporate** — RH/Empresas (clima emocional, risco de burnout, wellness programs)

Cada frente tem necessidades, frequência de uso, e paradigmas de interação diferentes.
Este documento liga todas as decisões anteriores sobre o que cada perfil vê na app Android.

Refs:
- [04-fases-iniciais.md](04-fases-iniciais.md) — faseamento da migração Android
- [06-web-first-admin.md](06-web-first-admin.md) — funcionalidades web-only (admin, corporate, PRO)
- [09-modularizacao.md](09-modularizacao.md) — feature modules condicionais por role
- [10-backend-reutilizacao.md](10-backend-reutilizacao.md) — middlewares por role (TherapistMiddleware, CorporateMiddleware)
- [11-backend-gaps.md](11-backend-gaps.md) — gaps específicos por role (API endpoints PRO)
- [12-autenticacao-seguranca.md](12-autenticacao-seguranca.md) — auth flow per role, data classification
- [25-riscos-decisoes.md](25-riscos-decisoes.md) — decisões D-06 (Play Store), D-08 (single vs multi app)

---

## Observações do estado atual

1. **User.role** — 5 valores: `admin`, `moderator`, `therapist`, `hr_admin`, `regular`
   (`app/Models/User.php` fillable)
2. **TherapistMiddleware** — verifica `$user->role === 'therapist'` (`app/Http/Middleware/TherapistMiddleware.php`)
3. **CorporateMiddleware** — verifica `$user->role === 'hr_admin' && $user->company_id`
   (`app/Http/Middleware/CorporateMiddleware.php`)
4. **patient_therapist** pivot table — relação many-to-many entre `User` e `Therapist`
   (`User::therapists()` belongsToMany)
5. **Corporate privacy threshold** — dados agregados ocultos se < 5 utilizadores ativos
   (`CorporateController:35`)
6. **Filament admin** — `canAccessPanel()` retorna `isAdmin() || isModerator()`
   (moderadores têm acesso limitado ao painel)
7. **SelfAssessment model** — PHQ-9 e GAD-7 com severity levels
   (`minimal`, `mild`, `moderate`, `moderately_severe`, `severe`)
8. **BuddySession model** — `status`, `rating`, `started_at`, `completed_at` + escalation route
9. **Pseudonym system** — `getPseudonymAttribute()` usa SHA-256 determinístico (formato "Lumina-XXXXXX").
   Mesmo pseudonym em todas as interações comunitárias

---

## Experiência B2C por estado emocional

### Princípio

A app adapta-se ao estado emocional do utilizador. Não restringe acesso — **reprioriza**.
Um utilizador em crise precisa de ajuda rápida, não de gamificação. Um utilizador bem-disposto
beneficia de estímulos sociais e missões.

### Três modos adaptativos

| Modo | Trigger | Cor dominante | Features proeminentes | Features atenuadas |
|------|---------|-------------|---------------------|-------------------|
| **Normal** | mood > 2, streak ativo | Amber/emerald (cores Lumina standard) | Gamificação, missões, comunidade, badges | — |
| **Low mood** | mood ≤ 2 na última entry OU streak quebrado | Soft blue (`blue-50/100`) | Calm Zone, diário, sons, cofre | Gamificação (pressão reduzida), notificações sociais |
| **Crisis** | Safety plan aberto/editado OU CBTAnalysisService trigger | Gentle rose (`rose-50/100`) | SOS FAB (sempre visível), Calm Zone one-tap, safety plan one-tap | Comunidade, gamificação, desafios |

### Transições

```kotlin
sealed class EmotionalMode {
    object Normal : EmotionalMode()
    object LowMood : EmotionalMode()
    object Crisis : EmotionalMode()
}

// Derivado do último estado conhecido
val emotionalMode: StateFlow<EmotionalMode> = combine(
    userStateRepository.lastMoodLevel,
    userStateRepository.isStreakActive,
    userStateRepository.isSafetyPlanActive
) { mood, streakActive, crisisActive ->
    when {
        crisisActive -> EmotionalMode.Crisis
        mood != null && mood <= 2 -> EmotionalMode.LowMood
        !streakActive && mood != null && mood <= 3 -> EmotionalMode.LowMood
        else -> EmotionalMode.Normal
    }
}.stateIn(viewModelScope, SharingStarted.Eagerly, EmotionalMode.Normal)
```

### Manual override

- Botão "Preciso de ajuda" acessível em qualquer ecrã (SOS FAB ou menu)
- Toque ativa Crisis mode manualmente
- Saída: "Estou melhor" (bottom sheet gentil) ou timeout de 2h

### Comportamento por modo

**Normal mode:**
- Bottom bar standard: Dashboard, Mural, Fogueira, Calm Zone, Perfil
- Cards proeminentes: missão do dia, flames, streak
- Notificações: todas ativas (respeitando quiet hours)

**Low mood mode:**
- Bottom bar reordenada: Calm Zone promovida para segunda posição
- Card proeminente: "Respira fundo. Estamos aqui." com link para exercício
- Gamificação: mostrar progresso mas sem pressão ("Sem pressa, o teu ritmo é perfeito")
- Notificações sociais: suavizadas (delay de 30min para non-critical)

**Crisis mode:**
- SOS FAB permanente (canto inferior direito, 64dp, cor rose-500)
- Toque no FAB: bottom sheet com opções:
  - "Ligar para linha de crise" (tel: SNS 24)
  - "Ver o meu plano de crise"
  - "Ir para a Zona Calma"
  - "Falar com alguém" (chat/buddy)
- Comunidade rebaixada (não removida — nunca restringir acesso)
- Zero gamificação visível — flames e badges temporariamente ocultos

### Regra absoluta

**Nunca restringir acesso a funcionalidades baseado no estado emocional.**
Apenas repriorizar a disposição e visibilidade. O utilizador pode sempre aceder
a tudo — a app apenas facilita o caminho mais útil.

---

## Camadas de privacidade B2C

### Modelo de três camadas

| Camada | Dados | Visível para |
|--------|-------|-------------|
| **Público** | Pseudonym, flames count, bonfire level, posts publicados, reações dadas | Toda a comunidade |
| **Privado** | Diary entries, vault items, safety plan, nome real, self-assessments, sessões terapia, mood history | Apenas o próprio utilizador (+ terapeuta atribuído para assessments) |
| **Configurável** | Read receipts, quiet hours, presença online, chat participation | Controlado pelo utilizador em Definições → Privacidade |

### Dados nunca expostos via API

- `password`, `remember_token` — `$hidden` no User model
- `encrypted_private_key` — apenas no auth response (ref. [12-autenticacao-seguranca.md](12-autenticacao-seguranca.md))
- `shadowbanned_until` — interno, nunca no UserResource
- `banned_at` — apenas via error response (403)

### Pseudonym como identidade comunitária

- Todo utilizador é identificado na comunidade pelo pseudonym ("Lumina-XXXXXX")
- SHA-256 determinístico — mesmo pseudonym em posts, chat, reações, buddy sessions
- Nome real visível **apenas** para:
  - O próprio utilizador
  - Terapeuta atribuído (via `patient_therapist` pivot)
  - Admin (via Filament)
- Moderadores veem nome real **apenas** em ações de moderação (server-side, não no broadcast)

### GDPR alignment

- Todos os dados privados exportáveis (`GET /api/v1/profile/export` → JSON/ZIP)
- Todos os dados eliminháveis (`DELETE /api/v1/profile` → `ProcessGdprDeletion` job)
- Passaporte emocional: resumo exportável sem dados brutos
- Audit trail via `DataAccessLog` (ref. [12-autenticacao-seguranca.md](12-autenticacao-seguranca.md))

---

## Decisão: App única com módulos condicionais

**Uma só app Android**, com experiência adaptada ao role do utilizador.

### Justificação

| Opção | Prós | Contras |
|-------|------|---------|
| **App única (recomendada)** | Menor manutenção, base de código partilhada, instalação simples | UI ligeiramente mais complexa |
| Apps separadas (B2C + PRO + Corporate) | UX completamente dedicada | 3x manutenção, 3x testing, 3x deploy, confuso para users com múltiplos roles |
| App B2C + WebView para PRO/Corporate | Rápido para PRO/Corporate | Experiência inconsistente, manutenção dual |

**Decisão final:** App única. A experiência B2C é o core. PRO e Corporate são **módulos condicionais** que aparecem apenas para roles autorizados.

---

## Faseamento por frente

| Frente | Fase Android | Justificação |
|--------|-------------|-------------|
| B2C | Fase 1 | Core do produto, maior número de utilizadores, maior benefício mobile |
| PRO | Fase 4+ (se procura justificar) | Terapeutas usam primariamente desktop. Mobile é complementar |
| Corporate | Web-only (sem fase Android) | RH usa desktop exclusivamente. Dashboards analíticos não beneficiam de mobile |

---

## B2C — Experiência principal (Fase 1-3)

### Scope completo

Tudo o que está documentado nos ficheiros 01-16 refere-se à experiência B2C:
- Autenticação + onboarding
- Dashboard + gamificação
- Diário emocional
- Zona Calma (exercícios, cofre, sons)
- Fórum (Mural da Esperança)
- Chat (Fogueira)
- Buddy system
- Biblioteca, auto-avaliação, the wall
- Pesquisa, pacto comunitário
- Privacidade e GDPR

### Roles no B2C
- `user` — Experiência standard
- `moderator` — Experiência standard + ações de moderação (pin, lock, mute, shadowban)

**Moderadores veem a mesma app, com botões adicionais** nas telas de fórum e chat. Não há tela separada de moderação — as ações são contextuais.

---

## PRO — Portal do terapeuta (Fase 4+, condicional)

### Estado atual (web)
- Dashboard com lista de pacientes
- Atribuir missões terapêuticas
- Trigger de somatic sync (WebSocket)
- Middleware: TherapistMiddleware (role === 'therapist')

### Estratégia mobile (se implementado)

**Módulo `feature-therapist`** — Ativado apenas se `user.role === 'therapist'`.

**Funcionalidades mobile PRO (scope reduzido):**

| Funcionalidade | Mobile? | Razão |
|---------------|---------|-------|
| Ver lista de pacientes | ✅ | Consulta rápida |
| Ver mood history de paciente | ✅ | Contexto para sessão |
| Atribuir missão | ✅ | Ação rápida |
| Trigger somatic sync | ✅ | Ação durante sessão presencial |
| Notas clínicas extensas | ❌ | Desktop-first (teclado físico) |
| Dashboard analítico | ❌ | Desktop-first (ecrã grande) |
| Gestão de horários | ❌ | Desktop-first |

**UI PRO na app:**
- Tab adicional "PRO" na bottom bar (ou menu lateral)
- Lista de pacientes com último mood e streak
- Ações rápidas: missão + somatic sync

**API endpoints PRO (se necessários):**
- `GET /api/v1/therapist/patients` — Lista de pacientes
- `GET /api/v1/therapist/patients/{id}/mood-history` — Histórico de humor
- `POST /api/v1/therapist/missions` — Atribuir missão
- `POST /api/v1/therapist/somatic-sync` — Trigger sync (WebSocket)

### Decisão de implementação

**Não implementar PRO na fase 1-3.** Reavaliar na fase 4 com base em:
- Número de terapeutas ativos (threshold: ≥ 50)
- Feedback explícito de ≥ 5 terapeutas pedindo mobile
- ROI vs manter web-only

### PRO API contract preview

Se PRO mobile for implementado, os endpoints necessários:

| Endpoint | Método | Descrição | Response key |
|----------|--------|-----------|-------------|
| `/api/v1/therapist/patients` | GET | Lista de pacientes atribuídos | Lista com `name`, `pseudonym`, `last_mood`, `current_streak`, `risk_level` |
| `/api/v1/therapist/patients/{id}/mood-history` | GET | Histórico de mood (30 dias) | Array de `{date, mood_level, tags}` para gráfico |
| `/api/v1/therapist/patients/{id}/assessments` | GET | PHQ-9/GAD-7 history | Array de `{type, score, severity, created_at}` |
| `/api/v1/therapist/missions` | POST | Atribuir missão terapêutica | `{mission_id, patient_id, assigned_date}` |
| `/api/v1/therapist/somatic-sync` | POST | Trigger exercício somático (WebSocket) | `{session_id, exercise, bpm}` → `SomaticSyncTriggered` event |
| `/api/v1/therapist/sessions` | GET | Buddy sessions (upcoming/past) | Lista com `{session_id, patient_pseudonym, status, started_at}` |

**Scope:** read-heavy, action-light. Mobile PRO é para consulta rápida e ações pontuais,
não para gestão clínica completa (que permanece web).

**Response:** pacientes identificados por `pseudonym` (referência) + `name` (real). Terapeuta
tem acesso ao nome real pela relação `patient_therapist`.

---

## Corporate — Dashboard empresarial (web-only)

### Estado atual (web)
- Dashboard com métricas agregadas de clima emocional
- Middleware: CorporateMiddleware (user pertence a company)
- Visão de burnout risk e engagement

### Decisão: Não migrar para Android

| Critério | Análise |
|----------|---------|
| Perfil do utilizador | Profissional RH, usa desktop em horário laboral |
| Frequência de uso | Semanal/mensal (reviews de métricas) |
| Tipo de interface | Dashboards com gráficos, tabelas, exports |
| Volume de utilizadores | Muito baixo (1-2 por empresa) |
| Benefício mobile | Mínimo |
| Custo de implementação | Alto (gráficos complexos, exports) |

**Conclusão:** Corporate permanece 100% web. Se no futuro houver procura, considerar uma dashboard mobile muito simplificada (apenas indicadores-chave).

---

## Feature flags para ativar módulos

### Ativação baseada em role

```kotlin
@Composable
fun LuminaNavHost(user: User) {
    NavHost(navController, startDestination = "dashboard") {
        // B2C — Sempre disponível
        dashboardGraph()
        diaryGraph()
        calmZoneGraph()
        forumGraph()
        chatGraph()
        profileGraph()

        // PRO — Apenas para terapeutas
        if (user.role == "therapist") {
            therapistGraph()
        }
    }
}

@Composable
fun LuminaBottomBar(user: User) {
    val items = buildList {
        add(BottomBarItem.Dashboard)
        add(BottomBarItem.Forum)
        add(BottomBarItem.Chat)
        add(BottomBarItem.CalmZone)
        add(BottomBarItem.Profile)
        if (user.role == "therapist") {
            add(BottomBarItem.Pro)
        }
    }
    // ...
}
```

### Ativação baseada em feature flag (server-side)

Para rollouts graduais ou A/B testing:

```kotlin
// API response do profile inclui feature flags
data class UserProfile(
    // ...
    val features: Map<String, Boolean>
)

// Uso
if (userProfile.features["therapist_mobile"] == true) {
    // Mostrar módulo PRO
}
```

---

## Moderação no contexto mobile

### Ações disponíveis na app (role: moderator)

| Ação | Onde | UI | Touch target |
|------|-----|------|-------------|
| Report review (simplificado) | Fórum, Chat | Swipe gesture no conteúdo flagged | 44x44dp |
| Pin post | Fórum | Long-press → menu contextual | 44x44dp |
| Delete message | Chat | Long-press → menu contextual | 44x44dp |
| Mute user | Chat | Long-press na mensagem → "Silenciar" | 44x44dp |
| Toggle crisis mode | Chat | Botão no header da sala | 48x48dp (ação crítica) |
| Pin message | Chat | Long-press → menu contextual | 44x44dp |

### Ações web-only (Filament)

| Ação | Razão |
|------|-------|
| Shadowban user | Complexo, raro, requer contexto do histórico completo |
| Lock/unlock posts | Operação bulk, desktop-first |
| Ban permanente | Decisão pesada, requer revisão em Filament |
| Moderation analytics/logs | Tabelas extensas, desktop-first |
| Feature flag management | Operação técnica/admin |
| User journey review | Timeline detalhada, requer ecrã grande |

### Quick action bar (moderadores em chat)

Quando um moderador está numa sala de chat, uma toolbar flutuante aparece no topo:

```
┌─────────────────────────────────────────────┐
│  🔇 Mute  │  📌 Pin  │  🛡️ Crisis  │  🗑️ Del  │
└─────────────────────────────────────────────┘
```

- Cada botão: **48x48dp** (acima do mínimo — ações frequentes e críticas)
- Cor suave (não agressiva — moderadores também estão em contexto emocional)
- Confirmação para ações destrutivas: dialog "Tens a certeza?" com undo de 5s (snackbar)
- Crisis toggle: confirmação explícita "Ativar modo de crise nesta sala?"

---

## Role transition handling

### Cenário

Um utilizador regular torna-se terapeuta (admin atribui role via Filament).

### Deteção

```kotlin
// No token refresh (periódico ou on-demand)
val refreshResponse = authApi.refreshToken()
val newRole = refreshResponse.user.role
val storedRole = userDataStore.getRole()

if (newRole != storedRole) {
    userDataStore.setRole(newRole)
    _roleTransitionEvent.emit(RoleTransition(from = storedRole, to = newRole))
}
```

### UI de transição

**Upgrade (regular → therapist):**
- Bottom sheet: "A tua conta foi atualizada. Agora tens acesso a funcionalidades PRO."
- Botão: "Explorar" → navega para tab PRO
- Nova tab "PRO" aparece na bottom bar sem requerer restart da app
- NavGraph atualizado dinamicamente (`therapistGraph()` adicionado)

**Downgrade (therapist → regular):**
- Bottom sheet: "O teu acesso PRO foi removido."
- Tab PRO removida da bottom bar
- Se user estava no ecrã PRO: redirect gentil para Dashboard
- Dados PRO cacheados: removidos da Room DB

**Moderator upgrade/downgrade:**
- Upgrade: botões de moderação aparecem contextualmente nos posts/mensagens
- Downgrade: botões desaparecem silenciosamente

### Edge case: role duplo

- Comunidade: sempre usa **pseudonym** (independentemente do role)
- Dashboard PRO: mostra **nome real** dos pacientes (autorizado pela relação terapêutica)
- Um terapeuta que também participa na comunidade como utilizador: pseudonym no fórum,
  nome real no painel PRO. Separação clara.

---

## Acessibilidade por perfil

### B2C — Compliance total

| Requisito | Implementação |
|-----------|-------------|
| Touch targets | Mínimo 44x44dp em todos os elementos interativos (CLAUDE.md) |
| Screen reader | TalkBack suportado em todos os ecrãs. Labels em PT-PT |
| Haptic feedback | Configurável via `a11y_reduced_motion`. Off por default se ativo |
| High contrast | Respeitar setting do sistema. Cores Lumina testadas com WCAG AA (ratio ≥ 4.5:1) |
| Font scaling | `sp` units em todo o texto. Testar com 200% system font size |
| Reduced motion | `a11y_reduced_motion = true` → sem animações, transições instantâneas |
| Dyslexic font | `a11y_dyslexic_font = true` → OpenDyslexic como font family |

### PRO (se implementado)

| Aspeto | Consideração |
|--------|-------------|
| Lista de pacientes | TalkBack: row-by-row navigation. "Paciente {nome}, mood {level}, streak {dias}" |
| Mood chart | Alternativa textual: "Últimos 7 dias: mood médio {X}. Tendência: {subindo/descendo/estável}" |
| Ações rápidas | Swipe actions com labels: "Atribuir missão", "Iniciar somatic sync" |
| Somatic sync trigger | Confirmação voiced: "Iniciar exercício de respiração a {bpm} batimentos?" |

### Moderador

| Aspeto | Consideração |
|--------|-------------|
| Quick action bar | Cada botão com `contentDescription` em PT-PT |
| Confirmação destrutiva | Dialog voiced pelo TalkBack. Undo snackbar anunciado |
| Crisis toggle | Double confirmation (voiced): "Ativar modo de crise? Confirma." |
| Report review | Conteúdo flagged lido pelo TalkBack com aviso prévio: "Conteúdo reportado" |

### Crisis mode — acessibilidade reforçada

- Texto maior (upgrade de `bodyMedium` para `bodyLarge`)
- Contraste alto automático (não depender do setting do sistema)
- Layout simplificado: menos opções visíveis, caminho mais curto para ajuda
- SOS FAB: `contentDescription = "Pedir ajuda urgente"`, anunciado com prioridade alta
- Mínimo de interações entre o utilizador e a ajuda — máximo 2 toques

---

## Riscos

| ID | Risco | Probabilidade | Impacto | Mitigação |
|----|-------|--------------|---------|-----------|
| RISK-17-01 | APK size aumenta com módulos condicionais (PRO, moderação) | Média | Baixo | Módulo PRO é lightweight (poucas telas). Se necessário: Android App Bundle com dynamic feature modules |
| RISK-17-02 | Role change não detetado até token refresh (pode demorar horas) | Baixa | Médio | Adicionar WebSocket event para role changes no canal privado `App.Models.User.{id}`. Ou: refresh periódico a cada 4h |
| RISK-17-03 | Moderator actions em mobile sem undo causam dano irreversível | Média | Médio | Undo snackbar de 5s para mute e pin. Crisis toggle com double confirmation. Delete com confirm dialog |
| RISK-17-04 | Deteção de estado emocional baseada apenas na última diary entry é pouco fiável | Média | Alto | Combinar: mood da última entry + streak status + frequência de uso da Calm Zone + último assessment score. Fase 1: apenas mood + streak. Fase 2: modelo mais completo |
| RISK-17-05 | Utilizador em low mood mode sente-se "vigiado" pela app | Baixa | Alto | Tom não-intrusivo. Nunca dizer "Detetámos que estás mal". Apenas repriorizar layout silenciosamente. Manual override sempre disponível: "Estou bem, obrigado" |

---

## Resumo de decisões

| Frente | Mobile | Fase | Notas |
|--------|--------|------|-------|
| B2C (user) | ✅ App completa | 1-3 | Core do produto |
| B2C (moderator) | ✅ Com ações extras | 2-3 | Moderação contextual |
| PRO (therapist) | ⚠️ Possível módulo | 4+ | Scope reduzido, condicional |
| Corporate (HR) | ❌ Web-only | — | Desktop-first |
| Admin | ❌ Web-only (Filament) | — | Backoffice |

---

*Próximo: [18-setup-ambiente.md](18-setup-ambiente.md) — Setup de ambiente em múltiplos PCs.*
