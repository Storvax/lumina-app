# CLAUDE.md — Lumina Project Guidelines

Persistent instructions for all Claude Code interactions with the Lumina project.

---

## 1. PROJECT OVERVIEW

**Name:** Lumina

**Mission:** A safe, calming, and gamified digital space for mental health, emotional tracking, community support (Mural), and clinical/B2B management.

**Tone:** Empathetic, calm, emotionally safe, and professional.

---

## 2. TECH STACK & ARCHITECTURE

- **Backend:** Laravel 11 (PHP 8.2+), PostgreSQL, Redis/Database Queues
- **Frontend:** Blade Templates, Tailwind CSS, Alpine.js
- **Real-time:** Laravel Reverb (WebSockets)
- **Admin/Dashboards:** Filament PHP
- **Infrastructure:** Dockerized, deployed on Railway

---

## 3. STRICT CODING GUIDELINES

### Language Rules
- **ALL code** (variables, functions, classes, database columns) **MUST be written in English.**
- Example: ✅ `user_mood`, `calculateWellnessScore()` / ❌ `user_humor`, `calcularPontuaçãoBemEstar()`

### User-Facing Text
- **ALL user-facing strings** (UI, validation messages, emails, errors) **MUST be strictly in Portuguese (PT-PT)** with an empathetic tone.
- Example: ✅ "Como se sente hoje?" / ❌ "How do you feel today?"

### Code Comments
- **ALL comments MUST be in professional Portuguese (PT-PT).**
- Explain the **"WHY"** (business logic, edge cases), not the "WHAT."
- Delete useless/obvious comments.
- Example: ✅ "Delay envio de email de verificação para evitar SMTP rate limiting" / ❌ "Obtém o utilizador"

### Framework Best Practices
- Use strict typing in PHP (type hints on all method signatures and properties).
- Rely on Laravel's built-in features: **Eloquent ORM, FormRequests, Policies, Events, Queued Jobs**.
- Avoid reinventing the wheel. Leverage framework conventions.
- Follow PSR-12 code style with Laravel conventions.

---

## 4. UX & UI RULES (FRONTEND)

### Accessibility First
- All interactive elements on mobile **must have a minimum touch target of 44×44px**.
- Implementation: Use `min-h-[44px] min-w-[44px]` or adequate padding in Tailwind.
- Test with touch devices during development.

### Emotional Safety
- **Avoid aggressive colors for errors.** Use soft tones (e.g., `rose-500` styled softly).
- Include accessible escapes on sensitive flows (e.g., persistent SOS FAB for crisis support).
- Provide clear, non-judgmental error messaging.

### Optimistic UI
- For interactions like reacting to posts or reporting, **update the UI instantly** using Alpine.js/JS before the server responds.
- Reduces perceived latency and improves user confidence.
- Always provide rollback feedback on network failures.

### Data Prevention
- Implement **auto-save** (LocalStorage) for long text inputs to prevent data loss during crises.
- Display indicator: "Guardado automaticamente" / "Auto-saved"
- Clear LocalStorage after successful submission.

---

## 5. GIT & WORKFLOW

### Commits
- **ALWAYS use Conventional Commits.**
  - `feat:` New feature
  - `fix:` Bug fix
  - `chore:` Maintenance, documentation, dependencies
  - `refactor:` Code refactoring without feature changes
  - `test:` Test additions/updates
  - `docs:` Documentation only
  - `style:` Formatting, linting (no logic change)

Example: `feat: add mood tracking dashboard with gamification points`

### Execution
- Make changes **surgically.** Do not rewrite entire files if only a small logic change is needed.
- Preserve existing code structure and patterns.
- Minimize diffs for easier review.

### Behavior
- Execute **silently and efficiently.**
- Output only **plans and commit confirmations.**
- Do not conversationalize or over-explain.
- Focus on delivering the requested change cleanly.

---

## 6. COMMON COMMANDS (Quick Reference)

```bash
# Start local development server
php artisan serve

# Run queue worker for background jobs
php artisan queue:work --queue=auth-mail,default

# Start real-time WebSocket server (Reverb)
php artisan reverb:start

# Clear all caches (config, route, view, etc.)
php artisan optimize:clear

# Run migrations
php artisan migrate

# Run tests
php artisan test

# Fresh database (reset + seed)
php artisan migrate:fresh --seed
```

---

## Notes for Claude

- Consult this file at the start of each session to align with project conventions.
- Update this file if significant architectural decisions change.
- Prioritize emotional safety and accessibility in all UI/UX decisions.
- Keep code maintainable, typed, and aligned with Laravel best practices.
- Always default to Portuguese for user-facing content and comments.

---

*Last updated: 2026-03-12*
