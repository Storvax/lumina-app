# 05 — Adaptações para Paradigma Mobile Nativo

## Contexto

Migrar de web (Blade + Tailwind + Alpine.js) para Android nativo não é apenas trocar tecnologias.
O paradigma de interação muda fundamentalmente: touch em vez de mouse, gestos em vez de clicks,
ciclo de vida da app em vez de refresh de página, conectividade intermitente em vez de always-online.

O estado actual do web (ref. [01-estado-atual.md](01-estado-atual.md)) apresenta gaps fundamentais
que justificam estas adaptações:
- **Sem offline** — todas as ações requerem rede, sem Service Worker implementado
- **Sem haptic feedback** — exercícios de respiração e grounding não têm componente tátil
- **Sem gravação de áudio nativa** — posts de áudio passam por Web API limitada
- **Sem push nativo** — VAPID parcial para web, FCM inexistente
- **Sem encriptação at-rest** — dados sensíveis (diário, cofre, safety plan) não encriptados no browser
- **Sem biometria** — autenticação biométrica impossível em web

Cada adaptação neste documento existe porque o paradigma web não oferece o equivalente.
A abordagem não é "fazer o mesmo mas em Kotlin" — é repensar cada interação para o contexto
nativo onde o utilizador pode estar em crise emocional, offline, ou com atenção fragmentada.

Este documento identifica todas as adaptações necessárias para que a Lumina funcione como uma
app verdadeiramente nativa.

---

## 1. Navegação

### Web atual
- Barra de navegação inferior (bottom nav) com 5 items
- Links de página com full page reload ou Alpine.js transitions
- URLs como estado (`/mural`, `/diario`, `/zona-calma`)

### Adaptação Android
- **Navigation Compose** com bottom navigation bar (Material 3)
- **Back stack gerido** pelo NavController — preserva estado entre telas
- **Deep links** para URLs da Lumina (permite notificações que abrem telas específicas)
- **Nested navigation graphs** por feature module
- **Animated transitions** entre telas (shared element transitions onde adequado)
- **Gesture navigation** — swipe back nativo do Android

**Decisão:** Manter a mesma estrutura de 5 tabs na bottom bar (Dashboard, Mural, Fogueira, Zona Calma, Perfil), mas com navegação aninhada dentro de cada tab.

---

## 2. Ciclo de vida e preservação de estado

### Web atual
- Estado vive no servidor (session) e no browser (Alpine.js reactive data)
- Refresh de página = perda de estado local
- Sem preocupação com background/foreground

### Adaptação Android
- **ViewModel** sobrevive a configuration changes (rotação, dark mode toggle)
- **SavedStateHandle** preserva estado durante process death
- **Room DB** como fonte de verdade para dados persistentes
- **WorkManager** para operações que devem completar mesmo com app em background (sync, upload)
- **Lifecycle-aware components** — pausar timers de respiração quando app vai para background

**Impacto na Zona Calma:**
- Exercícios de respiração devem pausar quando o utilizador sai da app
- Timer de grounding deve preservar estado se app vai para background brevemente
- Sons devem continuar em background (MediaSession + Foreground Service)

**Crise em background — cenários específicos:**

| Cenário | Comportamento |
|---------|-------------|
| Utilizador estava a ver safety plan e app é killed pelo sistema | Safety plan está em Room DB — restore imediato ao reabrir. SavedStateHandle preserva posição de scroll |
| Utilizador ativou Safe House (Casa Segura) | App fecha, clear recents, clear notificações. Ao reabrir: landing normal (login), NÃO retoma tela sensível |
| Exercício de respiração em andamento e app vai para background brevemente | Pausar animação e timer. Retomar ao voltar (ViewModel preserva estado via StateFlow) |
| Exercício de respiração e app é killed | Perda aceitável — exercício reinicia. Não justifica persistência complexa |
| Sons relaxantes em playback e app vai para background | Sons continuam (Foreground Service + MediaSession). Notification com controls de pausa/stop |

---

## 3. Touch, gestos e haptics

### Web atual
- Click handlers
- Hover states (não existem em mobile)
- Scroll com mouse/trackpad

### Adaptação Android

| Interação web | Equivalente nativo | Notas |
|--------------|-------------------|-------|
| Click | Tap (com ripple effect) | Material 3 ripple default |
| Hover | — | Eliminar estados hover, usar estados pressed/focused |
| Double-click | Double-tap (usar com parcimónia) | |
| Right-click | Long-press context menu | Para ações secundárias |
| — | Swipe-to-reply (chat) | Gesto novo, não existe na web |
| — | Swipe-to-dismiss | Para fechar bottom sheets |
| — | Pull-to-refresh | Em listas e dashboard |
| — | Haptic feedback | Em reações, mood slider, completar missão |

**Haptic feedback (Vibration API):**
- `HapticFeedbackType.LongPress` — ao reagir a um post/mensagem
- `HapticFeedbackType.TextHandleMove` — ao mover o slider de mood
- Custom vibration pattern — na respiração guiada (ritmo)
- Custom vibration pattern — no heartbeat/sintonia (batimento cardíaco)

**Touch targets:**
- Mínimo **48dp** para todos os elementos interativos (Material 3 guideline)
- Em contexto de crise (Zona Calma), aumentar para **56dp** — mãos a tremer
- Spacing entre botões: mínimo **8dp** para evitar toques acidentais

---

## 4. Offline e conectividade

### Web atual
- Sem suporte offline (sem Service Worker)
- Todas as ações requerem rede
- Erros de rede resultam em páginas de erro

### Adaptação Android

| Funcionalidade | Comportamento offline | Sync strategy |
|---------------|----------------------|---------------|
| Dashboard | Mostra última versão cacheada | Refresh on connectivity |
| Diário | Draft guardado localmente, sync quando há rede | Queue-based sync |
| Zona Calma (exercícios) | 100% funcional offline | N/A |
| Cofre pessoal | Leitura offline, escrita queued | Sync on connectivity |
| Plano de crise | 100% disponível offline | Pre-fetched |
| Fórum | Leitura da cache, escrita queued | Optimistic + sync |
| Chat | Sem funcionalidade offline (real-time) | Reconnect automático |
| Perfil | Leitura da cache | Sync on connectivity |

**Indicador de conectividade:**
- Banner discreto quando offline: "Estás offline. Algumas funcionalidades podem estar limitadas."
- Tom acolhedor, sem alarme
- Auto-dismiss quando rede volta

**Sync conflict resolution (Fase 1):** server-timestamp wins. Se o servidor rejeitar (409 Conflict),
a versão do servidor prevalece e a app atualiza o estado local.

**SyncQueue:** operações offline são armazenadas em `SyncQueueEntity` (Room DB) e processadas
por `SyncWorker` (WorkManager) quando a rede volta. Ref. [08-arquitetura-android.md](08-arquitetura-android.md)
secção "Sync e resolução de conflitos" e [13-offline-sync.md](13-offline-sync.md) secção 5.

---

## 5. Auto-save e prevenção de perda de dados

### Web atual
- Sem auto-save generalizado
- Risco de perda de dados em navegação acidental

### Adaptação Android
- **Diário:** Auto-save a cada 5 segundos de inatividade no TextField → Room DB
- **Posts (futuro):** Draft local com auto-save
- **Perfil:** Guardar localmente antes de enviar ao servidor
- **Unsaved changes dialog:** Se o utilizador tenta sair de um formulário com alterações: "Tens alterações não guardadas. Queres guardar antes de sair?"
- **Optimistic UI:** Mostrar a ação como concluída imediatamente, reverter se falhar

---

## 6. Notificações

### Web atual
- Notificações in-app (base de dados)
- Email (Brevo SMTP)
- Web Push (VAPID, parcial)

### Adaptação Android
- **Firebase Cloud Messaging (FCM)** para push notifications
- **Notification Channels** (obrigatório Android 8+):
  - `lumina_crisis` — Alta prioridade, para alertas de crise
  - `lumina_community` — Default, para interações do fórum/chat
  - `lumina_wellness` — Baixa prioridade, para lembretes gentis
  - `lumina_missions` — Default, para missões e gamificação
- **Quiet hours** — Enforcement server-side primário (não enviar FCM). Client-side backup:
  se FCM chegar durante quiet hours (race condition), a app filtra localmente com base em
  `quiet_hours_start/end` do modelo User
- **Deep links nas notificações** — Abrir diretamente a tela relevante
- **Notification grouping** — Agrupar múltiplas interações do fórum
- **Complementaridade WebSocket + FCM:** WebSocket para foreground real-time (chat);
  FCM para background push. Deduplicação por UUID da mensagem.
  Ref. [14-realtime-chat-notificacoes.md](14-realtime-chat-notificacoes.md) para arquitetura completa

---

## 7. Performance e dispositivos modestos

### Considerações
- Portugal tem diversidade de dispositivos — muitos utilizadores com gama média/baixa
- A app deve funcionar bem em Android 8+ (API 26+)
- RAM mínima alvo: 3GB

### Estratégias
- **Lazy loading** — LazyColumn/LazyRow em todas as listas
- **Image caching** — Coil com disk cache (avatars, imagens do Wall)
- **Pagination** — Cursor-based para feeds infinitos
- **Memory management** — Evitar manter dados grandes em memória
- **Baseline Profiles** — Gerar via Macrobenchmark library. Profiles para: startup flow
  (login → dashboard), scroll do fórum feed, abertura da Zona Calma. Impacto esperado:
  30-40% melhoria em cold start
- **R8 full mode** — Minificação e tree-shaking em release. Keep rules para: modelos Retrofit
  (Kotlinx Serialization), Room entities, Hilt generated code.
  Ref. [07-stack-android.md](07-stack-android.md) secção "Build optimization"
- **APK size** — Alvo < 15MB (download), < 30MB (instalado)

**Alvos de performance:**

| Métrica | Alvo | Crítico para |
|---------|------|-------------|
| Cold start | < 2s | Primeira impressão |
| Tempo-até-Zona-Calma | < 500ms | Utilizador em crise |
| Scroll (fórum, listas) | 60fps estável | UX fluida |
| API response time (P95) | < 1s | Percepção de rapidez |
| Build time incremental | < 30s | Produtividade do developer |

---

## 8. Acessibilidade nativa

### Web atual
- Dyslexic font (OpenDyslexic)
- Text size (100%-200%)
- Reduced motion
- Focus-visible outlines
- ARIA labels

### Adaptação Android
- **TalkBack** — Todas as telas devem ser navegáveis com screen reader
  - `contentDescription` em todos os ícones e imagens
  - `semantics` block em componentes Compose complexos
  - Anúncios customizados para ações (ex: "Reação de abraço adicionada")
- **Text scaling** — Respeitar `fontScale` do sistema Android + opção extra Lumina
- **Reduced motion** — Respeitar `Settings.Global.ANIMATOR_DURATION_SCALE`
- **High contrast** — Suportar temas de alto contraste do sistema
- **Switch Access** — Todas as ações alcançáveis via teclado/switch
- **Touch targets** — Mínimo 48dp (já mencionado)
- **Dyslexic font** — Manter como opção nas settings Lumina (font family customizada)

---

## 9. Segurança emocional no contexto mobile

### Safe House (Casa Segura)
- **Web:** Double-escape → redirect para Google + clear session
- **Android:**
  - Botão de saída rápida sempre visível (floating ou no header)
  - Tap rápido 2x → fecha app instantaneamente
  - Limpa notificações pendentes
  - App não aparece em "Recent Apps" durante 30 segundos (FLAG_SECURE temporário)
  - Opção: "Abrir app de cobertura" (ex: browser com Google)

### Noite / Madrugada
- **Web:** Amber filter 21h-05h, banner de madrugada
- **Android:**
  - Detectar hora local e aplicar warm color scheme
  - Respeitar Night Light do sistema
  - Banner gentil: "Estás acordado/a tarde. Precisas de alguma coisa?"
  - Atalho direto para Zona Calma no banner

### Conteúdo sensível
- **Web:** Blur CSS + click para revelar
- **Android:**
  - `Modifier.blur()` ou `RenderEffect.createBlurEffect()`
  - Tap longo para revelar (mais intencional que tap simples)
  - Confirmação: "Este conteúdo pode ser difícil. Queres continuar?"
  - Haptic suave ao revelar

---

## 10. Internacionalização e localização

### Web atual
- PT-PT hardcoded em toda a interface
- Locale: `APP_LOCALE=pt`

### Adaptação Android
- **Strings em `res/values-pt/strings.xml`** — Todas as strings da UI
- **Default locale: PT-PT** — A app deve abrir em português por defeito
- **Preparar para i18n futuro** — Usar `stringResource()` em vez de strings hardcoded
- **Formatação local:**
  - Datas: `dd/MM/yyyy` ou "há 3 horas"
  - Números: `1.234` (separador de milhares com ponto)
  - Moeda: € (se aplicável)

---

## 11. Temas e aparência

### Web atual
- Tons suaves, cores pastel
- Night mode (amber filter 21h-05h)
- Dark mode parcial (classes existem, toggle em falta)

### Adaptação Android
- **Material 3 Dynamic Color** — Opção para usar cores do wallpaper do utilizador
- **Light theme** como default (tons calmos, suaves)
- **Dark theme** — Implementar desde o início, suave (não preto puro)
- **Seguir preferência do sistema** por defeito
- **Override manual** nas settings da Lumina
- **Color palette Lumina:**
  - Primary: tom azul-lilás calmo
  - Secondary: tom dourado/âmbar quente
  - Surface: brancos suaves (light) / cinzas profundos (dark)
  - Error: tom rosa suave (não vermelho agressivo)

---

## 12. Permissões Android

| Permissão | Uso | Quando pedir |
|-----------|-----|-------------|
| `INTERNET` | Networking | Automática (normal permission) |
| `RECORD_AUDIO` | Posts de áudio, reflexão por voz | No momento de uso (lazy) |
| `POST_NOTIFICATIONS` | Push notifications | Após onboarding |
| `VIBRATE` | Haptic feedback | Automática (normal permission) |
| `CAMERA` | Upload Wall (opcional) | No momento de uso |
| `READ_MEDIA_IMAGES` | Upload Wall, avatar | No momento de uso |
| `FOREGROUND_SERVICE` | Sons em background | Transparente |
| `CALL_PHONE` | Chamada de emergência (crisis plan) | No momento de uso |

**Princípio:** Pedir permissões apenas no momento de uso, com explicação contextual e tom acolhedor.

---

## 13. Segurança nativa

### O que muda vs web

| Aspeto | Web (atual) | Android (nativo) |
|--------|------------|-----------------|
| Autenticação | Session cookies (Breeze) | Sanctum tokens em EncryptedSharedPreferences |
| Token storage | Cookie jar do browser | Android Keystore (AES-256-GCM) |
| Biometria | Impossível | BiometricPrompt (fingerprint + face) |
| Screenshots de dados sensíveis | Não controlável | FLAG_SECURE em telas sensíveis |
| Encriptação at-rest | Não implementada | Campo-level encryption em Room DB |
| Safe House | Double Escape → redirect Google | Double-tap → close app + clear recents + clear notificações |

### FLAG_SECURE

Impede screenshots e gravação de ecrã em telas com dados sensíveis:
- Diário (leitura/escrita)
- Safety Plan
- Chat privado (buddy)
- Vault (cofre pessoal)
- Login (campo password)

### Encriptação at-rest

Dados sensíveis encriptados campo a campo em Room DB com AES-256-GCM via AndroidX Security:
diary notes, vault items, safety plan, chat messages (cache).
Dados não-sensíveis (perfil, gamificação, metadata) não encriptados — protegidos pelo sandboxing
do Android.

Ref. [08-arquitetura-android.md](08-arquitetura-android.md) secção "Arquitetura de segurança"
e [12-autenticacao-seguranca.md](12-autenticacao-seguranca.md) para policy completa.

---

## 14. Áudio e media

### O que muda vs web

| Aspeto | Web (atual) | Android (nativo) |
|--------|------------|-----------------|
| Sons relaxantes | Web Audio API (limitada) | ExoPlayer/Media3 (múltiplos streams, mixer de volume) |
| Gravação de áudio | Não implementada nativamente | MediaRecorder (posts de áudio ≤60s, reflexão por voz) |
| Background audio | Impossível | MediaSession + Foreground Service |
| Playlist externa | Links simples | Deep links Spotify/YouTube Music |
| Notification controls | Não disponível | MediaStyle notification (play/pause/stop) |

### Exercícios que precisam de adaptação de áudio

| Exercício | Adaptação |
|-----------|----------|
| Sons relaxantes (Zona Calma) | Múltiplos streams simultâneos com mixer de volume independente por som. Foreground Service para continuar em background |
| Respiração guiada | Áudio opcional com contagem. Sync com animação visual + haptic |
| Heartbeat/sintonia | Padrão de vibração sincronizado com áudio de batimento cardíaco |
| Reflexão por voz | MediaRecorder para gravação + upload. Compressão AAC (M4A), 128kbps |
| Posts de áudio (Fórum, Fase 2) | Gravação ≤60s. Playback inline com ExoPlayer. Waveform visual |

### Especificações técnicas

- **Reprodução:** Media3 (ExoPlayer successor). API unificada, suporte oficial Google,
  MediaSession para notification controls integrados
- **Gravação:** MediaRecorder (API simples, suficiente para voz, sem necessidade de raw PCM)
- **Compressão:** AAC (M4A), bitrate 128kbps — suficiente para voz, tamanho reduzido para upload
- **Background:** Foreground Service obrigatório para playback em background (Android 8+)

Ref. [15-audio-media-uploads.md](15-audio-media-uploads.md) para detalhes técnicos completos.

---

## 15. Riscos e dependências das adaptações

| Risco | Probabilidade | Impacto | Mitigação |
|-------|-------------|---------|-----------|
| WebSocket lifecycle management (reconexão, concorrência FCM, token expiry durante conexão longa) | Alta | Alto | Exponential backoff, FCM como fallback, auth refresh no reconnect. Ref. [08-arquitetura-android.md](08-arquitetura-android.md) |
| Offline sync para diary (conflitos web+app simultâneo) | Média | Médio | Server-timestamp wins (Fase 1). Merge strategy em Fase 4+ se multi-device se tornar comum |
| Haptic compatibility entre fabricantes | Baixa | Baixo | Usar apenas APIs standard (`HapticFeedbackType`, `VibrationEffect`). Testar em Samsung e Xiaomi como minimum |
| FLAG_SECURE + clear recents em fabricantes custom (Samsung One UI, MIUI) | Baixa | Médio | Testar Safe House em 3+ OEMs. Fallback: se clear recents falhar, pelo menos fechar app + clear notificações |
| MediaRecorder qualidade inconsistente entre dispositivos | Média | Baixo | Normalizar com bitrate fixo (128kbps AAC). Testar em gama baixa |
| Baseline Profiles dificuldade de gerar corretamente | Média | Baixo | Seguir codelabs oficiais. Impacto é optimização, não funcionalidade — pode ser adiado |
| Notification channels mal configurados | Baixa | Médio | Testar em Android 8 (min SDK) e Android 15 (target). Channels não podem ser alterados após criação — acertar na primeira versão |

---

## 16. Prioridades de adaptação

| Prioridade | Áreas | Fase |
|-----------|-------|------|
| **P0 (essencial Fase 1)** | Navegação (1), Ciclo de vida (2), Offline (4), Auto-save (5), Acessibilidade (8), Segurança emocional (9), Temas (11), Permissões (12), Segurança nativa (13) | 1A-1B |
| **P1 (importante Fase 2)** | Touch/haptics avançados (3), Notificações FCM completas (6), Performance optimization com Baseline Profiles (7), Áudio/media (14) | 2 |
| **P2 (desejável Fase 3+)** | Internacionalização preparação para multi-idioma (10), Dynamic color Material 3 (11) | 3+ |

**Nota:** P0 significa que a adaptação é bloqueante para a Fase 1 — sem ela, a app não funciona
ou a experiência é inaceitável. P1 é importante para a completude da experiência mas pode ser
implementado incrementalmente. P2 é desejável e melhora a experiência mas não bloqueia nenhuma feature.

---

*Próximo: [06-web-first-admin.md](06-web-first-admin.md) — Funcionalidades que permanecem web-first.*
