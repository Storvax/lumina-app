# 17 — Estratégia B2C, PRO e Corporate no Contexto Android

## Contexto

A Lumina opera em 3 frentes:
1. **B2C** — Utilizadores finais (saúde mental pessoal)
2. **PRO** — Terapeutas (gestão de pacientes, triagem, exercícios guiados)
3. **Corporate** — RH/Empresas (clima emocional, risco de burnout, wellness programs)

Cada frente tem necessidades, frequência de uso, e paradigmas de interação diferentes.

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
- Número de terapeutas ativos
- Feedback de terapeutas sobre necessidade mobile
- ROI vs manter web-only

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

### Ações de moderação disponíveis na app (role: moderator)

| Ação | Onde | UI |
|------|-----|------|
| Pin post | Fórum | Ícone contextual no post |
| Lock post | Fórum | Ícone contextual no post |
| Shadowban user | Fórum | Long-press no username |
| Mute user | Chat | Long-press na mensagem |
| Toggle crisis mode | Chat | Botão no header da sala |
| Delete message | Chat | Long-press na mensagem |
| Pin message | Chat | Long-press na mensagem |

**Ações de moderação NÃO disponíveis na app:**
- Review de reports (backoffice Filament)
- Ban permanente (backoffice)
- Gestão de feature flags (backoffice)
- Analytics de moderação (backoffice)

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
