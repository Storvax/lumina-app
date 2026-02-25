# Lumina — Plano de Implementacao

## I. Analise do Estado Atual

### Modulos Funcionais (Implementados)

| Modulo | Estado | Ficheiros Principais |
|--------|--------|---------------------|
| Landing Page | Completo | `welcome.blade.php`, `landing/*.blade.php` |
| Autenticacao (Breeze) | Completo | `auth/*.blade.php`, `routes/auth.php` |
| Dashboard | Completo | `DashboardController.php`, `dashboard.blade.php` |
| Forum (Mural da Esperanca) | Completo | `ForumController.php`, `forum/*.blade.php` |
| Chat em Tempo Real (Fogueira) | Completo | `ChatController.php`, `chat/*.blade.php`, Events, Reverb |
| Diario Emocional | Completo | `DailyLogController.php`, `diary/index.blade.php` |
| Perfil (Santuario) | Completo | `ProfileController.php`, `profile/*.blade.php` |
| Sistema Buddy (Ouvinte) | Completo | `BuddyController.php`, `buddy/dashboard.blade.php` |
| Zona Calma | Completo | `CalmZoneController.php`, `calm/*.blade.php` |
| Biblioteca de Recursos | Completo | `LibraryController.php`, `library/index.blade.php` |
| Privacidade e RGPD | Completo | `PrivacyController.php`, `ProcessGdprDeletion.php` |
| Gamificacao Terapeutica | Completo | `GamificationService.php`, `GamificationController.php` |
| Painel Admin (Filament) | Completo | `app/Filament/Resources/*` |
| Moderacao | Completo | Shadowban, ModerationLog, Reports |
| Acessibilidade | Parcial | Fonte dislexico, movimento reduzido, tamanho de texto |
| NLP/Analise de Crise | Parcial | `CBTAnalysisService.php` (Keywords + OpenAI) |
| Notificacoes | Completo | Forum, Desafios, Resumo Semanal |
| Rate Limiting | Completo | `AppServiceProvider.php` |
| Casa Segura (Quick Exit) | Completo | Duplo-Escape no `lumina-layout.blade.php` |
| Filtro Noturno (cor) | Basico | `lumina-layout.blade.php` (apenas opacidade amber) |
| Barra de Navegacao Adaptativa | Parcial | Bottom nav adapta por tags emocionais |

### Servicos e Infraestrutura

| Componente | Estado |
|------------|--------|
| Laravel Reverb (WebSocket) | Configurado |
| Cache Redis (community_pulse, public_rooms) | Parcial |
| Laravel Queue (Jobs) | Configurado |
| Filament Admin Panel v5 | Configurado |
| Cron Jobs (purge-data, weekly-summary) | Comandos criados, agendamento nao confirmado |
| Testes Automatizados | Nao implementados |

---

## II. Funcionalidades em Falta — Por Prioridade

### FASE 1: CRITICO (Seguranca e Bem-Estar)

#### 1.1 Detecao de Crise Multicamada (Melhoria)
**Prioridade:** Critico
**Estado atual:** Layer 1 (keywords) funcional, Layer 3 (NLP via OpenAI) funcional com fallback
**Em falta:** Layer 2 — padroes de intencao sem keywords directas

**Dependencias:** `CBTAnalysisService.php`

**Instrucoes tecnicas:**

Ficheiro: `app/Services/CBTAnalysisService.php`

Adicionar ao `analyzeForumPost()`:
```php
// Layer 2: Padroes de intencao (frases que indicam crise sem keywords directas)
private const INTENT_PATTERNS = [
    'nao aguento mais',
    'quero desaparecer',
    'ninguem se importa',
    'seria melhor sem mim',
    'nao vale a pena',
    'cansado de viver',
    'nao consigo continuar',
    'quero acabar com isto',
    'nao vejo saida',
    'ja nao faz sentido',
    'estou a mais',
    'nao tenho forca',
    'quem me dera nao acordar',
    'desistir de tudo',
];

// No metodo analyzeForumPost, entre keywords e NLP:
$hasIntentPattern = Str::contains(Str::lower($text), self::INTENT_PATTERNS);
$hasKeywords = Str::contains(Str::lower($text), self::CRISIS_KEYWORDS);

$defaultResponse = [
    'is_sensitive' => $hasKeywords || $hasIntentPattern,
    'risk_level' => ($hasKeywords || $hasIntentPattern) ? 'high' : 'low',
    'sentiment' => 'neutral',
];
```

Tambem aplicar ao `ChatController::send()` onde a deteccao de crise e rudimentar (linha 147). Extrair a logica de deteccao para um metodo reutilizavel no `CBTAnalysisService`:
```php
public function detectCrisis(string $text): array
{
    $lower = Str::lower($text);
    $hasKeywords = Str::contains($lower, self::CRISIS_KEYWORDS);
    $hasIntent = Str::contains($lower, self::INTENT_PATTERNS);

    return [
        'detected' => $hasKeywords || $hasIntent,
        'level' => $hasKeywords ? 'critical' : ($hasIntent ? 'high' : 'none'),
        'type' => $hasKeywords ? 'keyword' : ($hasIntent ? 'intent' : null),
    ];
}
```

**Acao adicional:** Quando crise detectada no Chat, enviar notificacao aos moderadores via `Notification::send()` para todos os User com role `admin` ou `moderator`.

---

#### 1.2 Plano de Crise Pessoal Interactivo
**Prioridade:** Critico
**Estado actual:** Campo `safety_plan` existe na tabela `users`, pagina de crise mostra-o como texto simples
**Em falta:** Pagina interactiva para preenchimento estruturado e acesso rapido em crise

**Dependencias:** Nova rota, nova view, alteracao no `ProfileController`

**Instrucoes tecnicas:**

1. Criar nova view `resources/views/profile/partials/safety-plan-form.blade.php`
2. Estrutura do plano (campos):
   - `warning_signs` — "O que sinto quando estou a entrar em crise" (textarea)
   - `coping_strategies` — "O que me ajuda a acalmar" (textarea)
   - `reasons_to_live` — "Razoes para continuar" (textarea)
   - `support_contacts` — "Pessoas de confianca e contactos" (textarea)
   - `professional_contacts` — "Profissionais de saude" (textarea)
   - `environment_safety` — "Tornar o meu ambiente seguro" (textarea)

3. Actualizar `ProfileController::updateSafetyPlan()` para aceitar JSON estruturado:
```php
$validated = $request->validate([
    'warning_signs' => 'nullable|string|max:1000',
    'coping_strategies' => 'nullable|string|max:1000',
    'reasons_to_live' => 'nullable|string|max:1000',
    'support_contacts' => 'nullable|string|max:1000',
    'professional_contacts' => 'nullable|string|max:1000',
    'environment_safety' => 'nullable|string|max:1000',
]);

$request->user()->forceFill([
    'safety_plan' => json_encode($validated),
])->save();
```

4. Na pagina de crise (`calm/crisis.blade.php`), renderizar cada seccao do plano de forma estruturada:
```blade
@php $plan = json_decode($user->safety_plan, true); @endphp
@if(is_array($plan))
    @foreach(['warning_signs' => 'Sinais de Alerta', ...] as $key => $label)
        @if(!empty($plan[$key]))
            <div class="mb-4">
                <h3 class="text-sm font-bold text-indigo-300">{{ $label }}</h3>
                <p class="text-slate-100 whitespace-pre-line">{{ $plan[$key] }}</p>
            </div>
        @endif
    @endforeach
@endif
```

5. Adicionar botao de acesso rapido ao plano no `lumina-layout.blade.php` (junto ao SOS), visivel apenas quando o utilizador tem plano definido.

---

#### 1.3 Modo Madrugada (00h-05h)
**Prioridade:** Critico
**Estado actual:** Filtro de cor amber existe (`night-mode-filter`), activado apos as 21h com opacidade 0.07
**Em falta:** Interface completa de crise nocturna

**Dependencias:** `lumina-layout.blade.php`, CSS

**Instrucoes tecnicas:**

No `lumina-layout.blade.php`, expandir o script de modo noturno:
```javascript
const hour = new Date().getHours();
const isMadrugada = hour >= 0 && hour < 5;

if (isMadrugada) {
    document.body.classList.add('madrugada-mode');

    // Inserir banner de madrugada (antes do main content)
    const banner = document.createElement('div');
    banner.id = 'madrugada-banner';
    banner.className = 'fixed bottom-20 md:bottom-4 left-4 right-4 md:left-auto md:right-6 md:max-w-sm z-40 bg-indigo-900/95 backdrop-blur-xl text-white rounded-2xl p-5 border border-indigo-700/50 shadow-2xl';
    banner.innerHTML = `
        <div class="flex items-start gap-3">
            <i class="ri-moon-foggy-line text-2xl text-indigo-300 shrink-0 mt-0.5"></i>
            <div>
                <p class="font-bold text-sm text-indigo-100">E tarde. O teu cerebro esta mais vulneravel a esta hora.</p>
                <p class="text-xs text-indigo-300 mt-1">Estamos aqui contigo. Respira fundo.</p>
                <div class="flex gap-2 mt-3">
                    <a href="${document.querySelector('meta[name=crisis-url]')?.content || '/zona-calma/crise'}" class="text-xs font-bold bg-indigo-700 hover:bg-indigo-600 px-3 py-1.5 rounded-full transition-colors">Zona de Crise</a>
                    <button onclick="this.closest('#madrugada-banner').remove()" class="text-xs text-indigo-400 hover:text-white px-2 py-1.5 transition-colors">Fechar</button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(banner);
}
```

CSS para o modo madrugada:
```css
.madrugada-mode {
    font-size: 105%;
}
.madrugada-mode * {
    scroll-behavior: smooth;
}
.madrugada-mode #night-mode-filter {
    opacity: 0.12 !important;
}
```

---

### FASE 2: ALTA PRIORIDADE

#### 2.1 Onboarding Adaptativo por Intencao
**Prioridade:** Alta
**Estado actual:** Registo standard do Breeze (nome, email, password)
**Em falta:** Fluxo pos-registo com 3 perguntas

**Dependencias:** Nova migration, nova rota, nova view, middleware

**Instrucoes tecnicas:**

1. Migration: Adicionar `onboarding_completed_at` a tabela `users`
```php
Schema::table('users', function (Blueprint $table) {
    $table->timestamp('onboarding_completed_at')->nullable();
    $table->string('onboarding_intent')->nullable(); // O que te trouxe aqui
    $table->string('onboarding_mood')->nullable();    // Como te sentes agora
    $table->string('onboarding_preference')->nullable(); // Ler, ouvir ou falar
});
```

2. Middleware `EnsureOnboardingCompleted`:
```php
public function handle($request, Closure $next)
{
    if (Auth::check() && !Auth::user()->onboarding_completed_at) {
        return redirect()->route('onboarding.index');
    }
    return $next($request);
}
```

3. Controller `OnboardingController`:
   - `index()` — Mostra o formulario de 3 passos (Alpine.js multi-step)
   - `store()` — Guarda respostas, marca `onboarding_completed_at`, redireciona para destino contextual

4. Logica de destino:
```php
$destination = match ($validated['intent']) {
    'crisis' => route('calm.crisis'),
    'talk' => route('rooms.index'),
    'write' => route('diary.index'),
    'learn' => route('library.index'),
    default => route('dashboard'),
};
```

5. View: `resources/views/onboarding/index.blade.php` — design minimal, fullscreen, sem navbar

---

#### 2.2 Resumo de Bem-Estar Semanal (Agendamento)
**Prioridade:** Alta
**Estado actual:** Comando `lumina:weekly-summary` e notificacao `WeeklyEmotionalSummary` existem
**Em falta:** Agendamento no scheduler

**Instrucoes tecnicas:**

Ficheiro: `routes/console.php` (ou `app/Console/Kernel.php` se existir)
```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('lumina:weekly-summary')->weeklyOn(0, '10:00'); // Domingos as 10h
Schedule::command('lumina:purge-data')->dailyAt('03:00');
```

---

#### 2.3 Check-in Pos-Leitura
**Prioridade:** Alta
**Estado actual:** Nao implementado
**Em falta:** Apos ler um post com tag "vent" ou risk_level "high", perguntar ao leitor como se sente

**Dependencias:** `forum/show.blade.php`, nova rota, tabela de resultados

**Instrucoes tecnicas:**

1. No `forum/show.blade.php`, adicionar componente Alpine.js no final:
```blade
@if(in_array($post->tag, ['vent', 'anxiety']) || $post->risk_level === 'high')
<div x-data="{ answered: false, feeling: null }" x-show="!answered"
     class="mt-8 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl p-6 border border-indigo-100 dark:border-indigo-800">
    <p class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">
        Como te sentes depois de ler isto?
    </p>
    <div class="flex gap-3">
        @foreach(['empathy' => ['icon' => 'ri-heart-pulse-line', 'label' => 'Empatia'], 'sadness' => ['icon' => 'ri-rainy-line', 'label' => 'Tristeza'], 'strength' => ['icon' => 'ri-shield-star-line', 'label' => 'Forca']] as $type => $meta)
        <button @click="feeling = '{{ $type }}'; answered = true; axios.post('/mural/{{ $post->id }}/checkin', { feeling: '{{ $type }}' })"
                class="flex-1 py-3 rounded-xl border text-center text-sm font-bold transition-all hover:shadow-sm"
                :class="feeling === '{{ $type }}' ? 'bg-indigo-500 text-white border-indigo-500' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600'">
            <i class="{{ $meta['icon'] }} text-lg block mb-1"></i>
            {{ $meta['label'] }}
        </button>
        @endforeach
    </div>
    <template x-if="answered">
        <p class="text-xs text-indigo-400 mt-3 text-center">Obrigado. A tua resposta ajuda a comunidade.</p>
    </template>
</div>
@endif
```

2. Nova rota: `Route::post('/mural/{post}/checkin', [ForumController::class, 'postReadingCheckin'])->name('forum.checkin');`

3. Migration para tabela `post_checkins`:
```php
Schema::create('post_checkins', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('feeling'); // empathy, sadness, strength
    $table->timestamps();
    $table->unique(['post_id', 'user_id']);
});
```

4. Se o utilizador reportar `sadness`, sugerir recurso:
```javascript
if (feeling === 'sadness') {
    // Mostrar link discreto para Zona Calma
}
```

---

#### 2.4 Questionarios PHQ-9 e GAD-7
**Prioridade:** Alta
**Estado actual:** Nao implementado
**Em falta:** Auto-avaliacoes clinicas validadas (opcionais, privadas)

**Dependencias:** Nova migration, nova rota, novo controller, nova view

**Instrucoes tecnicas:**

1. Migration `create_self_assessments_table`:
```php
Schema::create('self_assessments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('type'); // 'phq9' ou 'gad7'
    $table->json('answers');
    $table->unsignedTinyInteger('total_score');
    $table->string('severity'); // minimal, mild, moderate, moderately_severe, severe
    $table->timestamps();
});
```

2. Controller `SelfAssessmentController`:
   - `index()` — Lista historico de avaliacoes
   - `create($type)` — Formulario do questionario
   - `store(Request $request)` — Calcula score e guarda
   - Cada pergunta tem opcoes 0-3 (PHQ-9: 9 perguntas, GAD-7: 7 perguntas)
   - Score total traduz-se em severity

3. Perguntas do PHQ-9 (em PT-PT):
   - "Pouco interesse ou prazer em fazer coisas"
   - "Sentir-se em baixo, deprimido(a) ou sem esperanca"
   - (etc. — questionarios clinicos validados e de dominio publico)

4. Rotas:
```php
Route::prefix('auto-avaliacao')->name('assessment.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{type}', 'create')->name('create');
    Route::post('/{type}', 'store')->name('store');
});
```

5. View com grafico de evolucao ao longo do tempo (scores anteriores).

---

#### 2.5 PWA com Service Worker e Modo Offline
**Prioridade:** Alta
**Estado actual:** Nao implementado
**Em falta:** Capacidade de acesso offline para funcoes criticas

**Dependencias:** `public/sw.js`, `public/manifest.json`, alteracao no layout

**Instrucoes tecnicas:**

1. Criar `public/manifest.json`:
```json
{
    "name": "Lumina",
    "short_name": "Lumina",
    "description": "O teu espaco seguro",
    "start_url": "/dashboard",
    "display": "standalone",
    "background_color": "#F8FAFC",
    "theme_color": "#6366F1",
    "icons": [
        { "src": "/icon-192.png", "sizes": "192x192", "type": "image/png" },
        { "src": "/icon-512.png", "sizes": "512x512", "type": "image/png" }
    ]
}
```

2. Criar `public/sw.js`:
```javascript
const CACHE_NAME = 'lumina-v1';
const OFFLINE_URLS = [
    '/zona-calma/crise',
    '/zona-calma/grounding',
    '/offline.html',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(OFFLINE_URLS))
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => caches.match('/offline.html'))
        );
    }
});
```

3. Registar no layout:
```html
<link rel="manifest" href="/manifest.json">
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js');
}
</script>
```

4. Criar `public/offline.html` com exercicio de respiracao integrado (nao depende de servidor).

---

#### 2.6 Cache Redis Expandido
**Prioridade:** Alta
**Estado actual:** Apenas `community_pulse` e `public_rooms` usam cache
**Em falta:** Cache em queries frequentes do forum e biblioteca

**Instrucoes tecnicas:**

Ficheiro: `ForumController::index()`
```php
// Cache dos posts mais recentes sem filtros (pagina inicial do forum)
if (!$request->has('search') && (!$request->has('tag') || $request->tag === 'all')) {
    $cacheKey = 'forum_posts_page_' . ($request->page ?? 1);
    $posts = Cache::remember($cacheKey, 300, fn() => $query->paginate(20));
} else {
    $posts = $query->paginate(20)->appends($request->query());
}
```

Ficheiro: `LibraryController::index()` — Cache similar para recursos aprovados.

Invalidar cache quando novos posts/recursos sao criados:
```php
Cache::forget('forum_posts_page_1');
```

---

### FASE 3: PRIORIDADE MEDIA

#### 3.1 Modo de Voz — Ditado de Entradas
**Estado:** Nao implementado
**Instrucoes:** Usar Web Speech API (`SpeechRecognition`) no formulario do Diario.
Botao de microfone junto ao textarea. Transcricao em tempo real.
```javascript
const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
recognition.lang = 'pt-PT';
recognition.continuous = true;
recognition.onresult = (e) => {
    textarea.value += e.results[e.results.length - 1][0].transcript;
};
```

#### 3.2 Animacoes de Carregamento Terapeuticas
**Estado:** Loader basico existe
**Instrucoes:** Expandir com animacoes variadas (ondas, folhas, respiracao).
Seleccionar aleatoriamente entre 3-4 animacoes CSS.

#### 3.3 Configuracao de Retencao de Dados (UI)
**Estado:** Campo `diary_retention_days` existe na BD, UI nao existe
**Instrucoes:** Adicionar slider/select na pagina de privacidade:
```blade
<select name="diary_retention_days">
    <option value="">Guardar sempre</option>
    <option value="30">30 dias</option>
    <option value="90">90 dias</option>
    <option value="365">1 ano</option>
</select>
```
Rota: `PATCH /privacidade/retencao`

#### 3.4 Timeline Pessoal "A Minha Jornada" (Melhoria)
**Estado:** Milestones existem no perfil, visualizacao basica
**Instrucoes:** Melhorar a visualizacao com timeline vertical estilizada.
Adicionar campo `icon` ao milestone. Permitir seleccionar icone na criacao.

#### 3.5 Expressao Artistica (The Wall)
**Estado:** Nao implementado
**Instrucoes:**
- Nova seccao no Forum com tag `art`
- Permitir upload de imagem (max 2MB, validacao MIME)
- Galeria de imagens em grid masonry
- Sem comentarios — apenas reacoes

#### 3.6 Breadcrumb Emocional
**Estado:** Nao implementado
**Instrucoes:** No `lumina-layout.blade.php`, adicionar linha subtil:
```blade
<div class="text-xs text-slate-400 px-6 pt-20 pb-2">
    {{ match(true) {
        request()->routeIs('calm.*') => 'Zona Calma',
        request()->routeIs('forum.*') => 'Mural da Esperanca',
        request()->routeIs('diary.*') => 'O Teu Diario',
        request()->routeIs('chat.*') => 'Fogueira',
        default => '',
    } }}
</div>
```

#### 3.7 Pesquisa com Filtros Emocionais
**Estado:** Forum tem filtro por tag, sem filtros emocionais
**Instrucoes:** Adicionar checkbox no painel de filtros:
- "Mostrar apenas historias de recuperacao" → `WHERE tag = 'hope'`
- "Excluir conteudo pesado" → `WHERE is_sensitive = false`

#### 3.8 Dark Mode Global Consistente
**Estado:** Parcialmente implementado (classes dark: existem em muitas views)
**Instrucoes:** Adicionar toggle no navbar e guardar preferencia no localStorage + BD.
Classes `dark` ja existem — falta apenas o toggle mecanismo e persistencia.

---

### FASE 4: FUTURO (Baixa prioridade ou alta complexidade)

| Funcionalidade | Complexidade | Notas |
|---------------|-------------|-------|
| Encriptacao E2E no Buddy | Alta | Requer libsodium, keypair management, UI de chaves |
| Conexao por Experiencia Partilhada | Alta | Algoritmo de matching por tags + historico |
| Radio da Comunidade | Media | Web Audio API para stream sincronizado |
| A/B Testing (Laravel Pennant) | Media | Feature flags para testar intervencoes |
| Push Notifications (Web Push) | Media | WebPush PHP library + VAPID keys + opt-in UI |
| Previsao de Engagement Decrescente | Alta | ML pipeline, dados historicos necessarios |
| Dashboard de Impacto Admin | Media | Widgets Filament com metricas de bem-estar |
| Temperatura da Comunidade | Media | Indice calculado por hora, semaforo para mods |

---

## III. Calendario de Implementacao Sugerido

### Sprint 1 — Seguranca Critica
1. Detecao de Crise Multicamada (Layer 2)
2. Plano de Crise Pessoal Interactivo
3. Modo Madrugada

### Sprint 2 — Experiencia do Utilizador
4. Onboarding Adaptativo
5. Check-in Pos-Leitura
6. Agendamento do Resumo Semanal + Purge

### Sprint 3 — Saude Clinica e Performance
7. Questionarios PHQ-9 / GAD-7
8. Cache Redis Expandido
9. PWA / Service Worker

### Sprint 4 — Refinamento
10. Modo de Voz (Web Speech API)
11. Animacoes Terapeuticas
12. UI de Retencao de Dados
13. Dark Mode Toggle Global
14. Breadcrumb Emocional
15. Filtros Emocionais na Pesquisa

### Sprint 5 — Diferenciacao
16. Expressao Artistica
17. Timeline "A Minha Jornada" melhorada
18. Push Notifications

---

## IV. Principios Tecnicos Transversais

1. **Seguranca primeiro:** Cada funcionalidade deve passar por validacao de input, proteccao CSRF, rate limiting, e verificacao de autorizacao antes de qualquer operacao.

2. **Privacidade by-design:** Dados pessoais nunca expostos desnecessariamente. Pseudonimos no forum. Logs de auditoria para acessos a dados sensiveis.

3. **Performance:** Usar `Cache::remember()` para queries repetitivas. Processar operacoes pesadas via Queue Jobs. Evitar N+1 queries com eager loading.

4. **Acessibilidade:** Todos os elementos interactivos devem ter `focus-visible`, labels ARIA, e suporte a navegacao por teclado. Respeitar `prefers-reduced-motion`.

5. **Linguagem:** Todos os textos voltados ao utilizador devem ser em portugues europeu, com tom empático, sem pressao nem culpabilizacao. Evitar jargao tecnico.

6. **Sem retencao toxica:** Nenhuma mecanica que crie culpa por inactividade. Streaks reiniciam sem drama. Notificacoes sao opt-in. Sem leaderboards publicos.

7. **Fallbacks:** Todas as integracoes externas (OpenAI, Spotify, iTunes) devem ter fallback graceful. A plataforma funciona sem APIs externas.

---

## V. Notas Arquitecturais

- **Stack:** Laravel 12, Blade, Tailwind CSS, Alpine.js, Laravel Reverb, Filament 5
- **BD:** SQLite (desenvolvimento) / PostgreSQL/MySQL (producao)
- **Cache:** Redis recomendado (file cache como fallback)
- **Queue:** Database driver (Horizon recomendado para producao)
- **Assets:** Vite com Tailwind CSS + PostCSS
- **Iconografia:** Remix Icons (CDN)
- **Tipografia:** Plus Jakarta Sans (Google Fonts)

---

*Este plano serve como guia tecnico para iteracoes futuras. Cada item contem instrucoes suficientes para implementacao directa, priorizando seguranca, privacidade e bem-estar do utilizador acima de metricas de engagement.*
