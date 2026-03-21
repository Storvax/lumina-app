# 26 — Recomendação Final de Abordagem

## Síntese executiva

A Lumina tem um backend Laravel maduro com 32 modelos, 7 serviços, e lógica de negócio bem definida. O gap principal para mobile é a **ausência total de camada API** — não existe `routes/api.php`, não existe autenticação por tokens, e não existem API Resources.

A recomendação é uma abordagem em duas frentes paralelas:

1. **Backend:** Adicionar uma camada API (`/api/v1/`) com Sanctum, sem alterar os controllers web existentes. ~80% do backend é reutilizado diretamente (Models, Services, Events, Jobs, Commands).

2. **Android:** App nativa em Kotlin + Jetpack Compose com Clean Architecture + MVI, organizada em feature modules. App única para B2C (com módulos condicionais para moderadores e, futuramente, terapeutas).

---

## O que fazer primeiro

### Passo 1: Backend API layer (Fase 0)

Antes de escrever uma linha de Kotlin:
- Instalar Sanctum
- Criar `routes/api.php`
- Implementar 3 endpoints: login, profile, dashboard
- Padronizar error handling JSON
- Testar com Postman/Bruno

**Resultado:** A app Android tem algo contra o que autenticar e consumir.

### Passo 2: Scaffold Android + auth (Fase 1A)

- Projeto multi-module com version catalog
- Design system Lumina (cores, tipografia, componentes)
- Tela de login funcional contra a API
- Guardar token em EncryptedSharedPreferences
- CI com GitHub Actions

**Resultado:** App compila, autentica, e mostra tela com tema Lumina.

### Passo 3: Core funcional (Fase 1B)

- Onboarding, dashboard, diário, zona calma core, perfil, gamificação
- Offline: diário com auto-save, zona calma sem rede
- Safe House para saída de emergência
- Haptic feedback em exercícios e mood slider

**Resultado:** App utilizável com valor terapêutico real. Pronta para testes com utilizadores reais.

---

## Princípios a nunca esquecer

1. **A Lumina é usada por pessoas vulneráveis.** Cada decisão de UX deve considerar contexto de crise, mãos a tremer, pensamento nublado.

2. **Offline é obrigatório para a Zona Calma.** Em momento de crise, o utilizador pode não ter rede. Exercícios de regulação somática devem funcionar sempre.

3. **Toda a gamificação é server-side.** A app nunca calcula flames nem desbloqueia achievements. O servidor é a fonte de verdade.

4. **Sem mecânicas de culpa.** Streaks resetam para 1 (não 0). Missões não completadas desaparecem silenciosamente. Sem notificações agressivas.

5. **PT-PT humano e empático.** Todas as strings da UI, mensagens de erro, e estados vazios devem ser escritos com cuidado e sensibilidade.

6. **Nativo de verdade.** Touch targets ≥48dp (≥56dp em contexto de crise). Haptic feedback significativo. Gestos nativos (swipe, long-press). Não é um wrapper web.

---

## Para o próximo prompt de implementação

Quando for altura de começar a construir:

1. Referenciar este plano como contexto: `docs/android-migration/`
2. Começar pela **Fase 0** (backend API) OU **Fase 1A** (scaffold Android) — podem correr em paralelo
3. Seguir os **quick wins** do ficheiro 24 para momentum
4. Cada feature branch deve corresponder a um deliverable do roadmap (ficheiro 23)
5. Consultar o ficheiro 11 (backend gaps) para saber exatamente o que criar na API
6. Consultar o ficheiro 09 (modularização) para a estrutura exata de packages e módulos
7. Consultar o ficheiro 07 (stack) para versões exatas de bibliotecas

---

## Estrutura final deste plano

```
docs/android-migration/
├── README.md                           ← Índice principal
├── 01-estado-atual.md                  ← Análise do produto atual
├── 02-inventario-funcional.md          ← Todas as funcionalidades mapeadas
├── 03-mapeamento-funcional.md          ← Sistema atual → Android nativo
├── 04-fase-inicial.md                  ← O que migrar primeiro
├── 05-adaptacoes-nativas.md            ← Adaptações para mobile
├── 06-web-first-admin.md               ← O que fica web-only
├── 07-stack-android.md                 ← Stack recomendada
├── 08-arquitetura-android.md           ← Clean Architecture + MVI
├── 09-modularizacao.md                 ← Feature modules e packages
├── 10-backend-reutilizacao.md          ← O que reutilizar do Laravel
├── 11-backend-gaps.md                  ← O que criar no backend
├── 12-autenticacao-seguranca.md        ← Auth, biometria, GDPR, segurança
├── 13-offline-sync.md                  ← Offline, auto-save, sync
├── 14-realtime-chat-notificacoes.md    ← WebSocket, FCM, presença
├── 15-audio-media-uploads.md           ← Gravação, reprodução, uploads
├── 16-gamificacao-servidor.md          ← Flames, streaks, server-side
├── 17-estrategia-b2c-pro-corporate.md  ← App única, módulos condicionais
├── 18-setup-ambiente.md                ← Config de máquinas
├── 19-software-ferramentas.md          ← Tudo a instalar
├── 20-git-sincronizacao.md             ← Branch strategy, commits, sync
├── 21-segredos-env.md                  ← Secrets management
├── 22-bootstrap-novas-maquinas.md      ← Setup rápido
├── 23-roadmap-fases.md                 ← Fases 0-4 com deliverables
├── 24-quick-wins.md                    ← Vitórias rápidas
├── 25-riscos-decisoes.md               ← Riscos e decisões em aberto
└── 26-recomendacao-final.md            ← Este ficheiro
```

**27 ficheiros. ~6000+ linhas de planeamento. Zero linhas de código alterado.**

---

*Este plano foi criado com base na análise completa do repositório Lumina, incluindo toda a documentação estratégica existente (PLANO_ESTRATEGICO.md, PLANO_IMPLEMENTACAO.md, CHAT_ANALYSIS.md), os 32 modelos, 24+ controllers, 7 serviços, 85+ rotas, e toda a estrutura de views, events, notifications e jobs.*

*Pronto para implementação.*
