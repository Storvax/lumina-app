# 06 — Funcionalidades Web-First, Admin-First e Backoffice-First

## Contexto

Este documento regista o que **NÃO** entra na app Android e porquê. Cada exclusão é uma decisão
deliberada que reduz escopo, complexidade e risco da migração Android.

O estado actual (ref. [01-estado-atual.md](01-estado-atual.md)) tem 4 perfis de utilizador:
B2C (utilizador final), PRO (terapeuta), Corporate (RH/B2B), e Admin/Moderador.
O inventário funcional (ref. [02-inventario-funcional.md](02-inventario-funcional.md)) identifica
~5 áreas funcionais como web-first. O mapeamento funcional
(ref. [03-mapeamento-funcional.md](03-mapeamento-funcional.md)) confirma a priorização.
Ref. [17-estrategia-b2c-pro-corporate.md](17-estrategia-b2c-pro-corporate.md) para estratégia
completa de posicionamento por perfil.

**Objetivo:** garantir que a equipa Android pode ignorar com segurança estas áreas, focando
100% no perfil B2C.

---

## Observações do estado atual

A separação entre perfis é limpa na arquitetura Laravel:
- **TherapistMiddleware** isola completamente o portal PRO
- **CorporateMiddleware** isola completamente o portal Corporate
- **Filament** é completamente independente (admin panel separado em `/admin`)
- **Rotas B2C** são as únicas que precisam de equivalente API

Isto significa: **nenhum destes middlewares precisa de equivalente na API Android.**
A separação server-side já existe e simplifica significativamente o escopo da API.

---

## Princípio

Nem tudo deve estar na app Android. Algumas funcionalidades são mais adequadas ao desktop/web
por natureza: interfaces de gestão complexas, dashboards analíticos, e ferramentas administrativas.

Manter estas funcionalidades exclusivamente web reduz a complexidade da app Android, acelera
o time-to-market, e permite focar os recursos nas experiências que realmente beneficiam do
mobile nativo.

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

## Matriz de decisão

| Área | Frequência de uso | Perfil desktop/mobile | Complexidade de interface | Volume de utilizadores | Benefício mobile | Decisão | Trigger de reconsideração |
|------|------------------|----------------------|--------------------------|----------------------|-----------------|---------|--------------------------|
| Filament (admin) | Raro | Desktop | Alta | < 5 | Nenhum | Web-only | Nunca |
| PRO (terapeuta) | Semanal | Desktop/tablet | Alta | Baixo | Médio | Web-first | ≥50 terapeutas ativos E ≥5 pedem mobile |
| Corporate (B2B/RH) | Mensal | Desktop | Média | Baixo | Baixo | Web-only | Nunca (salvo mudança fundamental de perfil) |
| Dev tools | Raro | Terminal | Baixa | Zero | Nenhum | Web-only | Nunca |
| Moderação pesada | Diário | Desktop | Alta | < 10 | Baixo | Web | Ações leves na app quando feature-chat existir (Fase 3) |

---

## Critérios de reconsideração

Para cada área web-first, o threshold de reconsideração é explícito:

| Área | Threshold | Ação se atingido | Impacto |
|------|----------|-----------------|---------|
| PRO (terapeuta) | ≥50 terapeutas ativos **E** ≥5 pedem mobile explicitamente | Adicionar `feature-therapist` como módulo condicional na app B2C (ref. [09-modularizacao.md](09-modularizacao.md)) | Médio — módulo novo, endpoints API novos |
| Corporate | Nunca reconsiderar | N/A | N/A |
| Filament | Nunca reconsiderar | N/A | N/A |
| Moderação | Quando `feature-chat` existir (Fase 3) | Ações leves (report, mute) na app. Ações pesadas (shadowban, lock, review) permanecem web | Baixo — report já previsto |
| Dev tools | Nunca reconsiderar | N/A | N/A |

Ref. [17-estrategia-b2c-pro-corporate.md](17-estrategia-b2c-pro-corporate.md) e
[23-escala-crescimento.md](23-escala-crescimento.md) para análise detalhada de crescimento.

---

## 5. Funcionalidades parcialmente web

Algumas funcionalidades existem na app Android com funcionalidade reduzida, mantendo a versão completa na web:

| Funcionalidade | Na app Android | Na web |
|---------------|---------------|--------|
| Moderação de posts | Ações básicas (report) | Ações completas (pin, lock, shadowban, review) |
| Moderação de chat | Ações básicas (report) | Ações completas (mute, crisis mode, delete) |
| Exportar dados (GDPR) | Trigger export + notificação quando pronto | Download direto do JSON |
| Passaporte emocional | Ver resumo na app | Ver + exportar PDF (gerado server-side) |
| Privacidade | Controlos básicos | Controlos completos + audit log |

**Detalhes de implementação parcial:**

- **GDPR export:** trigger na app (botão em Settings → Privacy) → job no backend →
  push notification quando pronto → download link abre no browser ou share intent.
  O ficheiro JSON/ZIP não é gerado nem armazenado na app.
- **Passaporte emocional:** ver resumo visual na app (mood trends, streak, achievements).
  PDF completo gerado server-side, download via browser ou share intent. Não gerar PDF na app
  — complexidade desnecessária.

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

## 8. Impacto quantificado para a migração

A exclusão destas áreas reduz significativamente o escopo da migração Android:

| Métrica | Quantidade eliminada | Fonte |
|---------|---------------------|-------|
| Rotas web sem equivalente API necessário | ~20 rotas | Portal PRO (~8), Corporate (~5), Filament (~zero API surface), Dev tools (~7) |
| Middlewares sem equivalente API | 2 (TherapistMiddleware, CorporateMiddleware) | Totalmente server-side |
| Filament API surface | Zero | Completamente independente, painel web em `/admin` |
| Módulos Android não criados | 3 (feature-therapist, feature-corporate, feature-admin) | Não necessários nas Fases 1-3 |
| Controllers sem equivalente API | ~6 (TherapistController, CorporateController, Filament resources) | Web-only |

**Resultado:** a app Android foca-se em ~65 rotas B2C candidatas a API
(ref. [02-inventario-funcional.md](02-inventario-funcional.md) secção 25),
ignorando com segurança ~20 rotas web-only.

---

## 9. Riscos

| Risco | Probabilidade | Impacto | Mitigação |
|-------|-------------|---------|-----------|
| Boundary errada: terapeutas pedem mobile antes do esperado | Baixa | Médio | Modularização (ref. [09-modularizacao.md](09-modularizacao.md)) permite adicionar `feature-therapist` como módulo futuro sem refactor da app |
| Moderadores precisam de agir rapidamente em mobile durante crise | Média | Alto | Ações leves (report, mute) já previstas na app (Fase 3). Ações pesadas permanecem web — moderadores têm acesso desktop |
| Utilizadores corporate pedem dashboard mobile | Baixa | Baixo | Dashboard corporate é visualização de dados agregados — não justifica app nativa. Alternativa: responsive web |
| API scope creep: endpoints PRO/corporate entram no escopo Android | Média | Médio | Este documento é a referência. Revisão em cada fase: qualquer endpoint não-B2C requer justificação explícita |

---

*Próximo: [07-stack-android.md](07-stack-android.md) — Stack Android moderna recomendada.*
