# 05 — Adaptações para Paradigma Mobile Nativo

## Contexto

Migrar de web (Blade + Tailwind + Alpine.js) para Android nativo não é apenas trocar tecnologias. O paradigma de interação muda fundamentalmente: touch em vez de mouse, gestos em vez de clicks, ciclo de vida da app em vez de refresh de página, conectividade intermitente em vez de always-online.

Este documento identifica todas as adaptações necessárias para que a Lumina funcione como uma app verdadeiramente nativa.

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
- **Quiet hours** — Respeitar as `quiet_hours_start/end` do utilizador, usar DND do sistema
- **Deep links nas notificações** — Abrir diretamente a tela relevante
- **Notification grouping** — Agrupar múltiplas interações do fórum

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
- **Baseline Profiles** — Para otimizar startup e scroll performance
- **R8 (ProGuard)** — Minificação e tree-shaking em release
- **APK size** — Alvo < 15MB (download), < 30MB (instalado)

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

*Próximo: [06-web-first-admin.md](06-web-first-admin.md) — Funcionalidades que permanecem web-first.*
