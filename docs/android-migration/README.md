# Lumina — Plano de Migração para Android Nativo

## Contexto

A Lumina é uma plataforma SaaS de saúde mental e bem-estar emocional focada em Portugal, construída atualmente em Laravel 12 + Blade + Tailwind CSS. Este plano documenta a estratégia completa para construir uma app Android nativa moderna a partir do projeto existente.

**O que este plano cobre:**
- Análise rigorosa do estado atual do produto e arquitetura
- Inventário funcional completo do sistema existente
- Mapeamento de cada funcionalidade para o paradigma Android nativo
- Proposta de stack, arquitetura e modularização Android
- Identificação de gaps no backend para suportar mobile
- Estratégias para autenticação, offline, real-time, media, gamificação
- Setup operacional para desenvolvimento em múltiplos PCs
- Roadmap de execução por fases

**O que este plano NÃO é:**
- Não é implementação de código
- Não é um exercício académico genérico
- Não é uma proposta de "wrapper" do site em WebView

## Princípios orientadores

1. **Segurança emocional** — A Lumina é usada por pessoas vulneráveis. A experiência deve ser calma, segura, intuitiva e acolhedora.
2. **Nativo de verdade** — Não queremos um wrapper. Queremos uma app que tire partido real do hardware e paradigmas Android.
3. **Preservação da identidade** — Funcionalidades, lógica de negócio, fluxos críticos, tom emocional e linguagem PT-PT são preservados.
4. **Migração progressiva** — O backend Laravel continua a servir a web. A app Android consome uma nova camada API.
5. **Pragmatismo** — Decisões baseadas em maturidade das ferramentas, comunidade, e adequação ao produto.

## Estrutura do plano

Os ficheiros estão organizados por tema. Recomenda-se leitura na ordem apresentada.

### Fundação e análise

| # | Ficheiro | Conteúdo |
|---|---------|----------|
| 01 | [Estado atual](01-estado-atual.md) | Stack, infraestrutura, modelos, controllers, serviços, maturidade por módulo |
| 02 | [Inventário funcional](02-inventario-funcional.md) | Todas as funcionalidades mapeadas por área, tipo de resposta, middleware, integrações |
| 03 | [Mapeamento funcional](03-mapeamento-funcional.md) | Cada funcionalidade → como deve existir na app Android |

### Estratégia de migração

| # | Ficheiro | Conteúdo |
|---|---------|----------|
| 04 | [Fase inicial](04-fase-inicial.md) | Funcionalidades a migrar primeiro e justificação |
| 05 | [Adaptações nativas](05-adaptacoes-nativas.md) | O que muda ao passar para paradigma mobile nativo |
| 06 | [Web-first e admin](06-web-first-admin.md) | Funcionalidades que devem permanecer web/backoffice |

### Arquitetura Android

| # | Ficheiro | Conteúdo |
|---|---------|----------|
| 07 | [Stack Android](07-stack-android.md) | Linguagem, frameworks, bibliotecas — com justificação |
| 08 | [Arquitetura Android](08-arquitetura-android.md) | Clean Architecture, camadas, padrões, diagramas |
| 09 | [Modularização](09-modularizacao.md) | Feature modules, core modules, estrutura de packages |

### Backend e integração

| # | Ficheiro | Conteúdo |
|---|---------|----------|
| 10 | [Backend — reutilização](10-backend-reutilizacao.md) | O que serve como está, contratos aproveitáveis |
| 11 | [Backend — gaps](11-backend-gaps.md) | Alterações necessárias: API routes, Sanctum, Resources, paginação, versionamento |

### Domínios técnicos

| # | Ficheiro | Conteúdo |
|---|---------|----------|
| 12 | [Autenticação e segurança](12-autenticacao-seguranca.md) | Tokens, biometria, secure storage, GDPR mobile |
| 13 | [Offline e sincronização](13-offline-sync.md) | Room DB, auto-save, conflict resolution, optimistic UI |
| 14 | [Real-time, chat e notificações](14-realtime-chat-notificacoes.md) | WebSocket client, FCM, presença, typing |
| 15 | [Áudio, media e uploads](15-audio-media-uploads.md) | Gravação, reprodução, upload com retry, conteúdo sensível |
| 16 | [Gamificação e servidor](16-gamificacao-servidor.md) | Flames, streaks, missions — validação server-side |
| 17 | [B2C, PRO e Corporate](17-estrategia-b2c-pro-corporate.md) | App única vs separadas, módulos condicionais |

### Ambiente de desenvolvimento

| # | Ficheiro | Conteúdo |
|---|---------|----------|
| 18 | [Setup de ambiente](18-setup-ambiente.md) | Configuração de máquinas para desenvolvimento Android |
| 19 | [Software e ferramentas](19-software-ferramentas.md) | Lista completa de software a instalar |
| 20 | [Git e sincronização](20-git-sincronizacao.md) | Branch strategy, convenções, workflow multi-máquina |
| 21 | [Segredos e variáveis](21-segredos-env.md) | Keystores, API keys, signing configs, gestão segura |
| 22 | [Bootstrap para novas máquinas](22-bootstrap-novas-maquinas.md) | Script de setup, checklist, troubleshooting |

### Execução

| # | Ficheiro | Conteúdo |
|---|---------|----------|
| 23 | [Roadmap por fases](23-roadmap-fases.md) | Fases 0-4 com dependências e critérios de conclusão |
| 24 | [Quick wins](24-quick-wins.md) | Vitórias rápidas para momentum inicial |
| 25 | [Riscos e decisões](25-riscos-decisoes.md) | Riscos técnicos, dependências, decisões em aberto |
| 26 | [Recomendação final](26-recomendacao-final.md) | Síntese executiva e próximos passos |

## Documentação existente relacionada

Este plano foi construído em complemento à documentação estratégica já existente no repositório:

- `PLANO_ESTRATEGICO.md` — Visão estratégica, gaps funcionais (GAP-01 a GAP-28), melhorias arquiteturais
- `PLANO_IMPLEMENTACAO.md` — Estado de implementação, funcionalidades em falta, fases de desenvolvimento
- `CHAT_ANALYSIS.md` — Análise detalhada do sistema de chat, bugs identificados, melhorias propostas

## Como usar este plano

1. **Leitura sequencial** — Para visão completa, ler na ordem 01→26
2. **Referência pontual** — Cada ficheiro é auto-contido no seu tema
3. **Base para implementação** — Num prompt futuro, referenciar este plano para arrancar a construção da app Android
4. **Documento vivo** — Deve ser atualizado à medida que decisões são tomadas e gaps resolvidos

## Decisões-chave antecipadas

| Decisão | Escolha | Justificação resumida |
|---------|---------|----------------------|
| Linguagem Android | Kotlin | Linguagem oficial, moderna, null-safe, coroutines |
| UI Framework | Jetpack Compose | Declarativo, Material 3, estado da arte Android |
| Arquitetura | Clean Architecture + MVI | Separação clara, testável, escalável |
| Autenticação mobile | Laravel Sanctum (tokens) | Leve, integra com Laravel, ideal para SPAs/mobile |
| Real-time mobile | OkHttp WebSocket | Maduro, fiável, integra com ecossistema Kotlin |
| Push notifications | Firebase Cloud Messaging | Standard Android, fiável, gratuito |
| Base de dados local | Room | Oficial, type-safe, integra com coroutines/Flow |
| Networking | Retrofit + OkHttp | Standard da indústria, maduro, extensível |
| App strategy | App única com perfis | B2C/PRO/Corporate na mesma app, módulos condicionais |

---

*Plano criado a 2026-03-21 com base na análise completa do repositório Lumina.*
