# 03 — Mapeamento Funcional: Sistema Atual → App Android Nativa

## Legenda

| Classificação | Significado |
|--------------|------------|
| **1:1** | Migra diretamente — mesma funcionalidade, adaptada ao paradigma nativo |
| **Reconceção** | A funcionalidade existe mas precisa de ser repensada para mobile |
| **Nativo+** | Pode ser significativamente melhorada com capacidades nativas Android |
| **Web-first** | Deve permanecer primariamente na versão web |
| **Excluir** | Não faz sentido na app Android |

---

## 1. Autenticação e onboarding

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Registo (email/password) | Registo nativo com validação inline | 1:1 | Adicionar biometria para login subsequente |
| Login session-based | Login por token (Sanctum) + biometria | Reconceção | Session → Bearer token. Guardar token seguro em EncryptedSharedPreferences |
| Forgot/reset password | Deep link para reset via email | 1:1 | Intent filter para links `lumina.pt/reset-password/*` |
| Verify email | Deep link para verificação | 1:1 | |
| Onboarding wizard (3 passos) | Onboarding nativo com animações, swipe | Nativo+ | Compose Pager com animações fluidas, progress indicator |
| Tour UI (overlay) | Compose overlay tutorial | 1:1 | Material 3 tooltip/coach marks |

## 2. Dashboard

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Visão geral (mood, flames, streaks) | Dashboard nativo com cards animados | Nativo+ | Pull-to-refresh, animações de flame level |
| Missões do dia | Cards expansíveis com progress bars | Nativo+ | Widget Android para missões na home screen |
| Insight AI personalizado | Card com texto gerado | 1:1 | Mesmo endpoint, cache local |
| Notificações não lidas | Badge no ícone + lista in-app | Nativo+ | Integração com sistema de notificações Android |
| Posts recomendados | Lista horizontal scrollável | Nativo+ | LazyRow com snap |

## 3. Fórum — Mural da Esperança

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

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Entrada diária (mood 1-5) | Slider ou seletor visual com animação | Nativo+ | Slider com haptic feedback por nível |
| Tags emocionais | Chips selecionáveis | 1:1 | Material 3 FilterChip |
| Nota livre | TextField com auto-save local | Nativo+ | Room DB para drafts, sync on save |
| CBT insight (AI) | Card expansível com texto | 1:1 | |
| Histórico (últimos 7 dias) | Timeline visual ou calendar view | Nativo+ | Gráfico de mood com cores |
| Auto-purge (retention) | Transparente ao utilizador | 1:1 | Lógica server-side |

## 6. Perfil — Santuário

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

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Listar recursos | Lista com categorias | 1:1 | |
| Sugerir recurso | Formulário | 1:1 | |
| Votar | Toggle com counter | 1:1 | |

## 10. Auto-avaliação

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Histórico de avaliações | Lista com scores e datas | 1:1 | |
| Formulário PHQ-9/GAD-7 | Questionário step-by-step com progress | Nativo+ | Uma pergunta por tela, animação de transição |
| Ver resultado | Card com score, severity, interpretação | 1:1 | |

## 11. The Wall

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Galeria masonry | StaggeredGrid nativo | Nativo+ | LazyStaggeredGrid do Compose |
| Upload imagem | Camera intent + gallery picker | Nativo+ | PhotoPicker API, crop/compress nativo |

## 12. Privacidade e GDPR

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Painel de privacidade | Settings screen com controlos | 1:1 | |
| Exportar dados | Trigger + download | 1:1 | Download Manager ou share intent |
| Hibernar conta | Ação com confirmação | 1:1 | |
| Apagar conta | Ação com confirmação multi-step | 1:1 | |

## 13. Pesquisa

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Pesquisa global | SearchBar nativa com sugestões | Nativo+ | Material 3 SearchBar, histórico local |

## 14. Comunidade

| Funcionalidade web | Android | Classificação | Notas |
|-------------------|---------|--------------|-------|
| Pacto diário | Card com prompt + resposta | 1:1 | |
| Relatório de impacto | Visualização com gráficos | Nativo+ | Charts nativos |

## 15. Funcionalidades que ficam web-first

| Funcionalidade | Razão |
|---------------|-------|
| Portal terapeuta (PRO) | Interface complexa de gestão, adequada a desktop |
| Portal corporate (B2B) | Dashboards analíticos, reports, adequados a desktop |
| Admin Filament | Backoffice completo, desktop-only |
| Gestão de feature flags | Admin-only |
| Download da base de dados | Dev-only |

## 16. Resumo da classificação

| Classificação | Quantidade | Percentagem |
|--------------|-----------|-------------|
| 1:1 | ~45 | ~55% |
| Nativo+ | ~30 | ~37% |
| Reconceção | ~5 | ~6% |
| Web-first | ~5 | ~6% |
| Excluir | 0 | 0% |

**Conclusão:** A grande maioria das funcionalidades migra diretamente ou beneficia de capacidades nativas. Apenas 5 funcionalidades requerem reconceção significativa (auth, chat real-time, acessibilidade, passaporte PDF, e-commerce/subscription se aplicável). Os portais administrativos e profissionais permanecem web-first.

---

*Próximo: [04-fase-inicial.md](04-fase-inicial.md) — Funcionalidades a migrar na primeira fase.*
