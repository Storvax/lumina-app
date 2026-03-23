# 03 — Mapeamento Funcional: Sistema Atual → App Android Nativa

## Contexto

Este documento mapeia cada funcionalidade do sistema web Lumina para a sua forma equivalente
na app Android nativa. Para cada funcionalidade identifica:

- O tipo de migração (1:1, Nativo+, Reconceção, Web-first)
- A prioridade para desenvolvimento Android (P0 a P3)
- As dependências que precisam de existir antes de poder implementar

Serve de base para priorizar o backlog Android e identificar o que requer atenção especial
antes ou durante a implementação.

**Suposições explícitas** estão assinaladas ao longo do documento e consolidadas na secção
final. Nenhuma decisão final de stack Android é tomada aqui — este documento é apenas
leitura estrutural do produto atual.

---

## Legenda

| Classificação | Significado |
|--------------|------------|
| **1:1** | Migra diretamente — mesma funcionalidade, adaptada ao paradigma nativo |
| **Reconceção** | A funcionalidade existe mas precisa de ser repensada para mobile |
| **Nativo+** | Pode ser significativamente melhorada com capacidades nativas Android |
| **Web-first** | Deve permanecer primariamente na versão web |
| **Excluir** | Não faz sentido na app Android |

## Prioridades de desenvolvimento Android

| Nível | Significado | Módulos |
|-------|-------------|---------|
| **P0** | Bloqueante para MVP — sem isto a app não funciona | Auth, onboarding, dashboard, diário |
| **P1** | Essencial para lançamento — core da proposta de valor | Fórum, chat, zona calma, perfil básico |
| **P2** | Importante mas pode vir na versão seguinte | Buddy system, biblioteca, the wall, auto-avaliação |
| **P3** | Complementar — enriquece a experiência | Pesquisa, pacto diário, gamificação avançada, triagem terapia |

---

## 1. Autenticação e onboarding

**Prioridade:** P0 — bloqueante
**Dependências:** Sanctum (tokens), EncryptedSharedPreferences, deep links configurados

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Registo (email/password) | Registo nativo com validação inline | 1:1 | Adicionar biometria para login subsequente |
| Login session-based | Login por token (Sanctum) + biometria | Reconceção | Session → Bearer token. Guardar token seguro em EncryptedSharedPreferences |
| Forgot/reset password | Deep link para reset via email | 1:1 | Intent filter para links `lumina.pt/reset-password/*` |
| Verify email | Deep link para verificação | 1:1 | |
| Onboarding wizard (3 passos) | Onboarding nativo com animações, swipe | Nativo+ | Compose Pager com animações fluidas, progress indicator |
| Tour UI (overlay) | Compose overlay tutorial | 1:1 | Material 3 tooltip/coach marks |

## 2. Dashboard

**Prioridade:** P0 — bloqueante
**Dependências:** Endpoint `/api/v1/dashboard` agregado; cache server-side preservado; AI insight opcional (graceful fallback)

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Visão geral (mood, flames, streaks) | Dashboard nativo com cards animados | Nativo+ | Pull-to-refresh, animações de flame level |
| Missões do dia | Cards expansíveis com progress bars | Nativo+ | Widget Android para missões na home screen |
| Insight AI personalizado | Card com texto gerado | 1:1 | Mesmo endpoint, cache local |
| Notificações não lidas | Badge no ícone + lista in-app | Nativo+ | Integração com sistema de notificações Android |
| Posts recomendados | Lista horizontal scrollável | Nativo+ | LazyRow com snap |

## 3. Fórum — Mural da Esperança

**Prioridade:** P1 — essencial para lançamento
**Dependências:** Paginação cursor-based; endpoint de upload de áudio; API Resources para Post e Comment; crisis detection ativo no endpoint de criação de post

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Feed de posts com filtros | Feed nativo com LazyColumn, pull-to-refresh | Nativo+ | Paginação cursor-based, cache offline |
| Ver post + comentários threaded | Tela de detalhe com replies colapsáveis | 1:1 | Nested comments com indentação visual |
| Criar post (texto) | Bottom sheet ou tela dedicada com auto-save | Nativo+ | Draft local, auto-save, optimistic posting |
| Criar post (áudio 60s) | Gravação nativa com waveform em tempo real | Nativo+ | MediaRecorder, visualização de amplitude |
| Reações (support/hug/candle/strength) | Emoji picker com haptic feedback | Nativo+ | Vibração suave ao reagir |
| Comentar (threaded) | Input contextual com reply preview | 1:1 | |
| Conteúdo sensível (blur) | Blur nativo com revelação por tap longo | Nativo+ | RenderEffect blur, animação de revelação |
| AI summary | Botão com loading + texto gerado | 1:1 | Cache local do summary |
| Report | Bottom sheet com opções | 1:1 | |
| Save/bookmark | Toggle com feedback háptico | 1:1 | Sincronizado com backend |
| Subscribe | Toggle | 1:1 | |
| Check-in emocional (pós-leitura) | Dialog após scroll de conteúdo sensível | 1:1 | |
| Pin/Lock (moderação) | Ações contextuais para mods | 1:1 | Visíveis apenas para roles adequados |
| Shadowban | Ação mod | 1:1 | |

## 4. Chat — A Fogueira

**Prioridade:** P1 — essencial para lançamento
**Dependências (críticas):** Sanctum tokens + autenticação WebSocket por token (não sessão); OkHttp WebSocket client; Reverb server atualizado para aceitar tokens; FCM para notificações de mensagens quando app em background

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Lista de salas | Lista nativa com room info cards | 1:1 | Cache offline da lista |
| Chat em tempo real | WebSocket nativo (OkHttp) + LazyColumn invertido | Reconceção | Precisa de implementação nativa de WebSocket, não Echo |
| Enviar mensagem | Input com suporte a anónimo, reply, sensível | 1:1 | Haptic no envio |
| Mensagens anónimas | Toggle no input area | 1:1 | |
| Reply (threading) | Swipe-to-reply com preview | Nativo+ | Gesto de swipe nativo |
| Editar mensagem | Long-press → opções → edição inline | Nativo+ | |
| Apagar mensagem | Long-press → confirmar | 1:1 | |
| Reações (hug/candle/ear) | Long-press → picker com haptic | Nativo+ | |
| Read receipts | Indicadores visuais (ticks) | 1:1 | Opt-in mantido |
| Presença online | Indicador de cor no avatar | 1:1 | Via WebSocket presence |
| Typing indicator | Animação de "..." | 1:1 | |
| Sala de silêncio | View-only mode com UI calma | 1:1 | Sem input, apenas observação |
| Crisis mode (slow 15s) | Timer visual + restrição no input | 1:1 | Countdown no botão de envio |
| Mute user (mod) | Ação contextual | 1:1 | |
| Pin message (mod) | Ação contextual | 1:1 | |
| Report | Long-press → report | 1:1 | |

## 5. Diário — O Teu Diário

**Prioridade:** P0 — bloqueante (core da proposta de valor)
**Dependências:** Endpoint `/api/v1/diary` (GET + POST); Room DB para drafts/auto-save; AI insight server-side (devolvido na resposta POST)

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Entrada diária (mood 1-5) | Slider ou seletor visual com animação | Nativo+ | Slider com haptic feedback por nível |
| Tags emocionais | Chips selecionáveis | 1:1 | Material 3 FilterChip |
| Nota livre | TextField com auto-save local | Nativo+ | Room DB para drafts, sync on save |
| CBT insight (AI) | Card expansível com texto | 1:1 | |
| Histórico (últimos 7 dias) | Timeline visual ou calendar view | Nativo+ | Gráfico de mood com cores |
| Auto-purge (retention) | Transparente ao utilizador | 1:1 | Lógica server-side |

## 6. Perfil — Santuário

**Prioridade:** P1 — essencial para lançamento (perfil básico: P1; passaporte e milestones: P2)
**Dependências:** Endpoints de perfil; upload de avatar; safety plan disponível offline (Room DB)

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Ver perfil (pseudónimo, flames, level) | Profile screen com avatar, stats, timeline | 1:1 | |
| Editar dados pessoais | Formulário nativo | 1:1 | |
| Alterar password | Formulário nativo | 1:1 | |
| Acessibilidade (font, motion, text size) | Settings nativo + respeitar system a11y | Reconceção | Usar Android system preferences + opções extra Lumina |
| Safety plan | Formulário multi-campo com auto-save | Nativo+ | Disponível offline no cofre |
| Nível de energia | Quick-action (1-5) | 1:1 | |
| Tags emocionais | Chips editáveis | 1:1 | |
| Milestones (timeline) | Timeline vertical com ícones | 1:1 | |
| Passaporte emocional | Tela de resumo + exportar PDF | Reconceção | PDF gerado via Android PDF API ou server-side |
| Preferências de notificação | Settings screen com toggles | Nativo+ | Integrar com NotificationChannel Android |
| Log respiração | Quick action button | 1:1 | +5 flames |

## 7. Zona Calma

**Prioridade:** P1 — essencial para lançamento (exercícios core: P1; cofre offline e sons: P1; playlist: P2)
**Dependências:** ExoPlayer para sons; MediaRecorder para gravação de reflexão de áudio; Vibration API para exercícios hápticos; Room DB para cofre offline; deep links para Spotify app

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Hub (menu de exercícios) | Grid de cards com ícones e cores | 1:1 | |
| Grounding 5-4-3-2-1 | Exercício guiado com animações nativas | Nativo+ | Pode incluir vibração háptica por passo |
| Plano de crise | Lista de contactos + botão tel direto | Nativo+ | `Intent.ACTION_DIAL` para chamada direta |
| Sons relaxantes | Player de áudio com mixer | Nativo+ | ExoPlayer com suporte a múltiplos streams, notificação de media |
| Combustão (venting) | Texto que desaparece com animação de fogo | 1:1 | Canvas animation |
| Respiração guiada | Animação circular com guia de tempo | Nativo+ | Vibração rítmica sincronizada com respiração |
| Sintonia (heartbeat) | Body scan com vibração háptica | Nativo+ | Vibration API para ritmo cardíaco |
| Reflexão (AI) | Chat-like com "eu futuro" | 1:1 | |
| Cofre pessoal | Lista editável de técnicas | 1:1 | Disponível offline (Room DB) |
| Playlist comunitária | Lista com embed Spotify | 1:1 | Deep links para Spotify app |
| Sugerir música | Formulário com search API | 1:1 | |

## 8. Buddy System — Ouvinte

**Prioridade:** P2 — importante, pode vir após lançamento
**Dependências:** Módulo de Chat completamente implementado (Buddy usa salas privadas de chat); FCM para notificações de pedidos de sessão; endpoint de escalada para moderadores

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Dashboard buddy | Overview com status, pedidos pendentes | 1:1 | |
| Pedir buddy | Ação com confirmação | 1:1 | |
| Candidatar-se | Formulário | 1:1 | |
| Aceitar sessão | Ação com routing para chat privado | 1:1 | |
| Escalar para moderador | Ação com confirmação | 1:1 | |
| Avaliar sessão | Rating com estrelas/emojis | Nativo+ | Haptic por seleção |
| Chat privado (buddy room) | Mesma implementação de chat | 1:1 | Reutiliza módulo de chat |

## 9. Biblioteca

**Prioridade:** P2
**Dependências:** Endpoints básicos de listagem e votação; sem dependências críticas

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Listar recursos | Lista com categorias | 1:1 | |
| Sugerir recurso | Formulário | 1:1 | |
| Votar | Toggle com counter | 1:1 | |

## 10. Auto-avaliação

**Prioridade:** P2
**Dependências:** Endpoints GET/POST para PHQ-9 e GAD-7; lógica de scoring é server-side

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Histórico de avaliações | Lista com scores e datas | 1:1 | |
| Formulário PHQ-9/GAD-7 | Questionário step-by-step com progress | Nativo+ | Uma pergunta por tela, animação de transição |
| Ver resultado | Card com score, severity, interpretação | 1:1 | |

## 11. The Wall

**Prioridade:** P2
**Dependências:** PhotoPicker API Android; endpoint de upload com compressão de imagem; storage seguro no servidor

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Galeria masonry | StaggeredGrid nativo | Nativo+ | LazyStaggeredGrid do Compose |
| Upload imagem | Camera intent + gallery picker | Nativo+ | PhotoPicker API, crop/compress nativo |

## 12. Privacidade e GDPR

**Prioridade:** P1 — necessário para conformidade legal antes do lançamento
**Dependências:** Endpoints de export (download manager), hibernação e eliminação; limpar storage local após delete

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Painel de privacidade | Settings screen com controlos | 1:1 | |
| Exportar dados | Trigger + download | 1:1 | Download Manager ou share intent |
| Hibernar conta | Ação com confirmação | 1:1 | |
| Apagar conta | Ação com confirmação multi-step | 1:1 | |

## 13. Pesquisa

**Prioridade:** P3
**Dependências:** Endpoint de pesquisa agregada com paginação; histórico de pesquisa local (Room DB)

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Pesquisa global | SearchBar nativa com sugestões | Nativo+ | Material 3 SearchBar, histórico local |

## 14. Comunidade

**Prioridade:** P3
**Dependências:** Endpoints de pacto e impacto; gráficos nativos para relatório de impacto

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Pacto diário | Card com prompt + resposta | 1:1 | |
| Relatório de impacto | Visualização com gráficos | Nativo+ | Charts nativos |

## 15. B2C / PRO / Corporate — presença na app Android

| Perfil | Presença na app Android | Módulos incluídos |
|--------|------------------------|-------------------|
| **B2C** | App principal (todas as fases) | Auth, onboarding, dashboard, diário, fórum, chat, zona calma, perfil, buddy (como utilizador), biblioteca, the wall, GDPR, gamificação, auto-avaliação, pesquisa, pacto, triagem de terapia |
| **PRO (terapeuta)** | Web-first na fase inicial | Portal /terapeuta não adequado a mobile. Pode ser reconsiderado em fase posterior com view ou app dedicada. O fluxo de triagem B2C (encontrar terapeuta) está na app; o portal de gestão do terapeuta não. |
| **Corporate** | Web-only | Dashboard analítico B2B fica sempre em web |
| **Admin/Mod (ações pesadas)** | Web-only | Filament backoffice, gestão de feature flags |
| **Admin/Mod (ações leves)** | App (baixa prioridade) | Shadowban, mute no chat, report actions — podem existir na app mas não são prioridade |

---

## 16. Funcionalidades que ficam web-first

| Funcionalidade | Razão |
|---------------|-------|
| Portal terapeuta (PRO) | Interface complexa de gestão, adequada a desktop |
| Portal corporate (B2B) | Dashboards analíticos, reports, adequados a desktop |
| Admin Filament | Backoffice completo, desktop-only |
| Gestão de feature flags | Admin-only |
| Download da base de dados | Dev-only |

## 17. Resumo da classificação

| Classificação | Quantidade | Percentagem |
|--------------|-----------|-------------|
| 1:1 | ~45 | ~55% |
| Nativo+ | ~30 | ~37% |
| Reconceção | ~5 | ~6% |
| Web-first | ~5 | ~6% |
| Excluir | 0 | 0% |

**Nota sobre Reconceção:** As funcionalidades de Reconceção (~5) são as mais arriscadas em
termos de tempo de desenvolvimento porque implicam redesenho de paradigma, não apenas
adaptação visual. Auth (session→token) e Chat real-time (WebSocket nativo) são os dois
casos mais críticos e bloqueantes.

### Prioridades por fase de desenvolvimento

| Fase | Módulos | Classificações predominantes |
|------|---------|------------------------------|
| P0 — MVP | Auth, onboarding, dashboard, diário | Reconceção (auth), Nativo+, 1:1 |
| P1 — Lançamento | Fórum, chat, zona calma, perfil, GDPR | Nativo+, Reconceção (chat), 1:1 |
| P2 — Versão seguinte | Buddy, biblioteca, the wall, auto-avaliação | 1:1, Nativo+ |
| P3 — Complementar | Pesquisa, pacto, gamificação avançada, triagem | 1:1, Nativo+ |

---

## 18. Suposições explícitas

| # | Suposição | Base da suposição | Impacto se errada |
|---|-----------|-------------------|-------------------|
| S1 | O utilizador PRO (terapeuta) não usa a app Android na fase 1 | Portal de gestão é complexo; não há pedido explícito | Poderia requerer segundo target de utilizador na app |
| S2 | O fluxo de triagem `/terapia` serve utilizadores B2C e pode existir na app como "encontrar apoio profissional" | TherapyController não usa TherapistMiddleware | Pode precisar de ser separado se requerer sessão com terapeuta |
| S3 | O Buddy System é B2C puro — qualquer utilizador pode ser ouvinte ou pedir ouvinte sem restrição de plano | BuddyController não usa middleware de plano | Pode haver restrição futura por plano PRO |
| S4 | Moderadores usam a app com ações de moderação limitadas (mute, shadowban, report) | Roles admin/moderator existem no modelo User | Pode ser que moderação só faça sentido em desktop |
| S5 | The Wall e a playlist da Zona Calma são B2C puras sem restrição de plano | Nenhum middleware de plano nos controllers respetivos | Pode haver restrição futura por plano |
| S6 | O safety plan deve estar disponível offline na app | É o recurso mais crítico em crise — disponibilidade offline é essencial para segurança | Se não implementado offline, utilizador em crise sem rede não consegue aceder |

---

## 19. Dependências críticas inter-módulos

Estas dependências definem a ordem em que os módulos podem ser desenvolvidos.
Um módulo não pode ser implementado antes de as suas dependências estarem prontas.

```
Auth (Sanctum tokens)
  ├── Dashboard
  ├── Diário
  ├── Fórum
  ├── Chat
  │     └── Buddy System (reutiliza Chat)
  ├── Zona Calma
  │     └── Safety Plan offline (Room DB)
  ├── Perfil
  ├── GDPR
  ├── Biblioteca
  ├── The Wall
  ├── Auto-avaliação
  ├── Pesquisa
  └── Gamificação (acionada por Diário, Fórum, Zona Calma, Chat)

WebSocket (Sanctum token auth no Reverb)
  └── Chat real-time
        └── Buddy System (chat privado)

FCM (Firebase Cloud Messaging)
  └── Push notifications (mensagens, notificações de forum, desafios gentis)

Room DB (Android local storage)
  ├── Drafts do Diário (auto-save)
  ├── Safety Plan offline
  ├── Cofre pessoal offline
  └── Histórico de pesquisa local

Endpoint de upload dedicado na API
  ├── Audio posts no Fórum
  ├── Upload de imagens no The Wall
  └── Avatar no Perfil
```

### Dependências críticas que bloqueiam múltiplos módulos

| Dependência | Módulos bloqueados | Prioridade de resolução |
|------------|-------------------|------------------------|
| Sanctum (token auth) | TODOS | P0 — resolver primeiro |
| API layer (`routes/api.php`) | TODOS | P0 — resolver antes de Sanctum |
| Paginação cursor-based | Fórum, Chat (histórico), Pesquisa | P1 |
| WebSocket token auth | Chat, Buddy | P1 |
| FCM | Push notifications (cross-module) | P1 |
| Endpoint de upload | Fórum (áudio), The Wall, Perfil (avatar) | P2 |
| Room DB setup | Diário drafts, Safety Plan, Cofre | P1 |

---

*Próximo: [04-fase-inicial.md](04-fase-inicial.md) — Funcionalidades a migrar na primeira fase.*
