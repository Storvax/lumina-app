# 25 — Riscos, Dependências e Decisões em Aberto

## 1. Riscos técnicos

### R-01: WebSocket compatibility com Reverb via token auth
**Severidade:** Alta
**Fase afetada:** 3 (Chat)
**Descrição:** O Laravel Reverb foi projetado para funcionar com Laravel Echo (JavaScript) e autenticação por session. A autenticação de canais via Bearer token (Sanctum) em vez de cookies de sessão pode requerer configuração adicional ou patches.
**Mitigação:** Testar early na Fase 0 com um endpoint `/broadcasting/auth` que aceite Bearer tokens. Se não funcionar out-of-the-box, pode ser necessário criar um middleware de bridge.
**Fallback:** Usar Pusher (serviço cloud) em vez de Reverb self-hosted.

### R-02: Performance do emulador em máquinas com 16 GB RAM
**Severidade:** Média
**Fase afetada:** Todas
**Descrição:** Android Studio + emulador + backend local pode consumir >12 GB RAM, deixando a máquina lenta.
**Mitigação:** Usar device físico para testing quando possível. Configurar emulador com apenas 2-3 GB RAM. Fechar backend quando não necessário.

### R-03: Complexidade de offline sync com conflitos
**Severidade:** Média
**Fase afetada:** 1B-2
**Descrição:** Se o utilizador edita o diário offline numa máquina e online noutra, pode haver conflitos de sync.
**Mitigação:** Para a Fase 1, usar "last-write-wins" com timestamps do servidor. Conflitos reais são raros (1 utilizador = tipicamente 1 dispositivo). Resolver com UX gentil se necessário.

### R-04: Tamanho do APK com exercícios e assets
**Severidade:** Baixa
**Fase afetada:** 4 (Sons)
**Descrição:** Se os sons relaxantes forem bundled no APK, o tamanho pode ultrapassar o alvo de 15 MB.
**Mitigação:** Download on-demand de sons com cache local. Manter core do APK leve.

### R-05: Breaking changes no backend ao criar API
**Severidade:** Média
**Fase afetada:** 0
**Descrição:** Ao refactorizar controllers e adicionar API Resources, há risco de introduzir bugs na web existente.
**Mitigação:** A API usa controllers separados (`Api/V1/`). Não tocar nos controllers web existentes. Testes end-to-end na web após cada mudança no backend.

### R-06: Dependência do OpenAI para funcionalidades core
**Severidade:** Média
**Fase afetada:** Todas
**Descrição:** Crisis detection (Layer 3), CBT insights, sumarização, e reflexão AI dependem da API OpenAI.
**Mitigação:** O backend já tem fallback local (Layer 1 + 2 de crisis detection). Para a app Android, garantir que funcionalidades degradam gracefully se a API falhar. Nunca bloquear uma ação do utilizador por falha da AI.

### R-07: Play Store review para conteúdo de saúde mental
**Severidade:** Média
**Fase afetada:** Release
**Descrição:** A Google Play tem políticas específicas sobre apps de saúde e wellness. Pode haver rejeição por conteúdo sensível (menções de suicídio, auto-lesão).
**Mitigação:** Incluir disclaimers claros, emergência contactos visíveis, e seguir guidelines da Play Store para apps de saúde. Considerar classificação etária adequada.

---

## 2. Dependências externas

| Dependência | Impacto se indisponível | Mitigação |
|------------|------------------------|-----------|
| Firebase (FCM) | Sem push notifications | Polling como fallback (não ideal) |
| OpenAI API | Sem CBT insights, AI summary, reflexão | Fallback local: keywords + intent patterns |
| Spotify API | Sem metadados de músicas | iTunes API como fallback |
| iTunes API | Sem metadados alternativos | Dados manuais |
| Google Play Store | Sem distribuição | APK direto ou alternative stores |
| Laravel Reverb | Sem real-time (chat) | Pusher cloud como fallback |
| Railway (hosting) | Backend offline | Backup deployment (Fly.io, DigitalOcean) |

---

## 3. Decisões em aberto

### D-01: Nome da pasta do projeto Android
**Opções:** `lumina-android/` vs `android/` vs `mobile/`
**Recomendação:** `lumina-android/` — Explícito, sem ambiguidade.
**Status:** Proposto, aguarda validação.

### D-02: Aplicação ID (package name)
**Opções:** `pt.lumina.app` vs `pt.lumina.android` vs `com.lumina.app`
**Recomendação:** `pt.lumina.app` — Reflete o domínio português, identidade da marca.
**Status:** Proposto, aguarda validação.
**Nota:** Uma vez publicado na Play Store, o application ID não pode mudar.

### D-03: Versionamento de API — header vs URL
**Opções:**
- URL: `/api/v1/...` (recomendado)
- Header: `Accept: application/vnd.lumina.v1+json`
**Recomendação:** URL-based. Mais simples, mais visível, standard para Laravel.
**Status:** Proposto.

### D-04: Monorepo vs multi-repo
**Opções:**
- Monorepo (recomendado): Backend + Android no mesmo repositório
- Multi-repo: Repositórios separados
**Recomendação:** Monorepo. Equipa pequena, coordenação frequente.
**Status:** Proposto.

### D-05: Conta Firebase — pessoal vs organizacional
**Opções:**
- Projeto Firebase pessoal (rápido para começar)
- Projeto Firebase em conta organizacional Lumina (correto para produção)
**Recomendação:** Criar conta organizacional desde o início. Evitar migração posterior.
**Status:** Decisão necessária antes da Fase 2 (FCM).

### D-06: Conta Google Play Developer
**Opções:**
- Conta pessoal (25 USD, setup rápido)
- Conta organizacional (25 USD, requer verificação DUNS)
**Recomendação:** Conta organizacional se Lumina já tiver NIF/empresa registada. Pessoal como fallback.
**Status:** Decisão necessária antes do primeiro release.

### D-07: Backend URL de produção para API
**Opções:**
- `api.lumina.pt` (subdomínio dedicado)
- `lumina.pt/api/v1/` (path-based)
**Recomendação:** `api.lumina.pt` — Separação clara, facilita CORS/CDN no futuro.
**Status:** Proposto, depende de configuração DNS.

### D-08: Estratégia de dark mode
**Opções:**
- Seguir sistema por defeito, override manual
- Light-only inicialmente, dark mode na Fase 4
**Recomendação:** Implementar dark mode desde a Fase 1A no design system, mas manter light como default.
**Status:** Proposto.

### D-09: Idioma do código Android
**Opções:**
- Código em inglês, UI em PT-PT (alinhado com PLANO_ESTRATEGICO.md)
- Tudo em PT-PT
**Recomendação:** Código e nomes de classes/funções em inglês. Strings da UI em PT-PT via `strings.xml`. Consistente com convenções internacionais e com o backend existente.
**Status:** Proposto, alinhado com documentação existente.

---

## 4. Suposições explícitas

| Suposição | Risco se falsa |
|-----------|---------------|
| A base de utilizadores é primariamente Android | Se iOS for necessário, considerar KMP |
| O backend continua em Railway | Se migrar, URLs e deployment mudam |
| 1 developer principal | Se equipa crescer, branch strategy pode precisar de ser mais formal |
| SQLite (dev) e PostgreSQL (prod) são mantidos | Se mudar DB, migrations podem divergir |
| Reverb é compatível com token auth | Se não for, WebSocket auth precisa de workaround |
| Play Store aprova app de saúde mental | Se rejeitada, precisar de ajustar conteúdo/disclaimers |

---

## 5. Decisões já tomadas (confirmadas neste plano)

| Decisão | Escolha | Justificação |
|---------|---------|-------------|
| Linguagem | Kotlin | Oficial, moderna, null-safe |
| UI | Jetpack Compose + Material 3 | Declarativo, estado da arte |
| Arquitetura | Clean Architecture + MVI | Testável, separação clara |
| DI | Hilt | Oficial, compile-time |
| Networking | Retrofit + OkHttp | Standard da indústria |
| DB local | Room | Oficial, type-safe |
| Auth | Sanctum tokens | Leve, integra com Laravel |
| WebSocket | OkHttp WebSocket | Maduro, fiável |
| Push | Firebase Cloud Messaging | Standard Android |
| App strategy | App única, módulos condicionais | Menor manutenção |
| Repository | Monorepo | Coordenação frequente |
| B2C mobile | Sim, Fase 1-3 | Core do produto |
| PRO mobile | Condicional, Fase 4+ | Depende de procura |
| Corporate mobile | Não | Desktop-first |

---

*Próximo: [26-recomendacao-final.md](26-recomendacao-final.md) — Recomendação final de abordagem.*
