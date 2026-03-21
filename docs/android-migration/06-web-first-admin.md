# 06 — Funcionalidades Web-First, Admin-First e Backoffice-First

## Princípio

Nem tudo deve estar na app Android. Algumas funcionalidades são mais adequadas ao desktop/web por natureza: interfaces de gestão complexas, dashboards analíticos, e ferramentas administrativas.

Manter estas funcionalidades exclusivamente web reduz a complexidade da app Android, acelera o time-to-market, e permite focar os recursos nas experiências que realmente beneficiam do mobile nativo.

---

## 1. Portal de administração (Filament)

**Decisão:** Permanece 100% web.

| Funcionalidade | Razão |
|---------------|-------|
| Gestão de utilizadores | Interface de tabela com filtros, bulk actions, export — desktop-first |
| Moderação de conteúdo | Revisão de reports, shadowban, lock/pin — requer contexto visual amplo |
| Gestão de salas de chat | Criar/editar/configurar rooms — operação rara |
| Gestão de achievements | CRUD de badges — operação rara |
| Gestão de missões | Templates de missões — operação rara |
| Feature flags | A/B testing toggles — operação técnica |
| Analytics dashboard | Gráficos, métricas, export — requer ecrã grande |
| Community temperature | Widget com dados agregados |
| User journey tracking | Timeline detalhada por utilizador |
| Moderation logs | Audit trail — tabelas extensas |
| Data access logs | GDPR audit — tabelas extensas |

**Impacto:** Zero impacto na app Android. Filament opera exclusivamente na web.

---

## 2. Portal terapeuta (Lumina PRO)

**Decisão:** Permanece web-first. Possível app simplificada futura (Fase 4+).

| Funcionalidade | Razão | Mobile futuro? |
|---------------|-------|---------------|
| Dashboard de pacientes | Visão tabular de múltiplos pacientes | Talvez (lista simplificada) |
| Atribuir missões | Seleção de paciente + tipo de missão | Talvez |
| Somatic sync trigger | Ação pontual via WebSocket | Sim (ação simples) |
| Histórico de sessões | Tabelas com dados extensos | Não |
| Notas clínicas | Texto extenso, formulários complexos | Não |

**Justificação detalhada:**
- Terapeutas trabalham primariamente em desktop/tablet durante sessões
- A interface requer visualização simultânea de múltiplos dados (histórico mood, assessments, notas)
- O volume de terapeutas é significativamente menor que o de utilizadores B2C
- ROI de implementar PRO em mobile nativo é baixo nesta fase

**Exceção futura:** Se houver procura real, considerar uma versão simplificada do PRO na mesma app Android (módulo condicional por role), limitada a:
- Ver lista de pacientes
- Trigger de somatic sync
- Atribuir missão rápida
- Chat terapêutico

---

## 3. Portal corporate (B2B)

**Decisão:** Permanece 100% web.

| Funcionalidade | Razão |
|---------------|-------|
| Dashboard de clima emocional | Gráficos complexos, dados agregados |
| Métricas de burnout | Visualizações analíticas |
| Relatórios de uso | Export PDF/CSV |
| Gestão de colaboradores | Tabelas com filtros |

**Justificação:**
- Perfil de utilizador RH = desktop-first
- Dashboards analíticos requerem ecrã grande
- Frequência de uso baixa (semanal/mensal)
- Dados sensíveis que beneficiam de ambiente controlado (VPN corporativa)

---

## 4. Funcionalidades técnicas/dev

| Funcionalidade | Decisão | Razão |
|---------------|---------|-------|
| Download da base de dados | Web-only | Dev/debug tool |
| Seeders e factories | Web-only | Desenvolvimento |
| Console commands | Web-only | Operações de servidor |
| Queue monitoring | Web-only | Operações (Horizon) |

---

## 5. Funcionalidades parcialmente web

Algumas funcionalidades existem na app Android com funcionalidade reduzida, mantendo a versão completa na web:

| Funcionalidade | Na app Android | Na web |
|---------------|---------------|--------|
| Moderação de posts | Ações básicas (report) | Ações completas (pin, lock, shadowban, review) |
| Moderação de chat | Ações básicas (report) | Ações completas (mute, crisis mode, delete) |
| Exportar dados (GDPR) | Trigger export + notificação quando pronto | Download direto do JSON |
| Passaporte emocional | Ver resumo | Ver + exportar PDF |
| Privacidade | Controlos básicos | Controlos completos + audit log |

---

## 6. Resumo de decisões

| Área | Mobile nativo | Web-only | Notas |
|------|--------------|----------|-------|
| B2C (utilizador final) | ✅ Sim | Complementar | Core da app Android |
| Administração (Filament) | ❌ Não | ✅ Sim | Desktop-first |
| Lumina PRO (terapeuta) | ❌ Não (fase 4?) | ✅ Sim | Desktop-first |
| Corporate (B2B/RH) | ❌ Não | ✅ Sim | Desktop-first |
| Moderação básica | ⚠️ Parcial | ✅ Completa | Report na app, review na web |

---

## 7. Implicações para a API

Os endpoints da API serão consumidos **apenas** pela app Android B2C (e potencialmente por futuras apps). Os portais web continuam a usar as rotas web existentes (`routes/web.php`).

Isto significa:
- A API não precisa de incluir endpoints para funcionalidades admin/PRO/corporate nesta fase
- Os middlewares `TherapistMiddleware` e `CorporateMiddleware` não precisam de equivalente API
- O Filament continua completamente independente da API

**Exceção:** Se no futuro se decidir criar uma versão PRO mobile, será necessário adicionar endpoints API para as funcionalidades do terapeuta. Essa decisão está documentada em [25-riscos-decisoes.md](25-riscos-decisoes.md).

---

*Próximo: [07-stack-android.md](07-stack-android.md) — Stack Android moderna recomendada.*
