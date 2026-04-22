# Hub AI UI — workflow, v0 integration, and automated QA

This runbook is the **canonical** guidance for AI-assisted UI work on **Affiliate Hub** (Vue 3 + Vite under `vue-app/`). Consumer repos receive it via `cursor-config` propagation; repo-specific experiments belong in `.cursor/local/**` (see root `README.md`).

## Reference layout (Affiliate Hub)

Use these paths when aligning v0 or agent output with the real Hub tree:

| Area | Path |
| --- | --- |
| Pages | `vue-app/src/pages/` — look at the nearest sibling page for full-page composition, loading skeletons, and responsive layout patterns |
| Components | `vue-app/src/components/` |
| Frontend agent notes | `vue-app/AGENTS.md` |
| Vitest | `vue-app/tests/` |
| Hub build into Flask | `cd vue-app && npm run hub` |

Re-run the Vitest command below after large Hub refactors; paths are stable by convention (`vue-app/src/pages`, `vue-app/tests`).

## Proposed workflow (default)

1. **Plan**: Restate goal, constraints, acceptance criteria; pick the smallest UI surface (see `.cursor/commands/blueprint.md`).
2. **Generate** (v0 / agent): Produce Vue SFCs using **Composition API**, **2-space** indent, existing **Tailwind** + **shadcn-vue** patterns where the Hub already uses them.
3. **Integrate**: Place files under `vue-app/src/pages` or `vue-app/src/components`; wire routes/templates only through existing Hub patterns (`affiliatehub/vue/vue.py`, `vue-app/templates/*.html`, `vite.config.js` inputs — follow `AGENTS.md` / `docs/LOCAL_DEV.md` in Hub).
4. **Build check**: `cd vue-app && npm run hub` when the change must ship through Flask-served HTML.
5. **Verify auth-sensitive UI**: Prefer Flask at `http://localhost:5000` after `npm run hub`; use Vite dev server (`npm run dev`, port 5173) only for isolated iteration.
6. **Automated QA**: Add or extend **Vitest** tests under `vue-app/tests/`; run the smallest relevant file or suite before merge.
7. **Broader checks**: Use Hub `docs/RUNBOOKS/VERIFICATION_LADDER.md` and `docs/TESTING_MATRIX.md` when present; otherwise resolve from `package.json` and CI workflows.

## Working with v0 links and mockup images

When an issue includes a v0 preview URL, v0 code link, a zip attachment, or GitHub screenshot images, follow this order of evidence and mapping rules.

### Evidence preference (highest → lowest)

| Source | How to use |
| --- | --- |
| **Zip / exported code** | Extract; use as the implementation skeleton; apply Hub tokens and components on top. |
| **v0 preview URL** (`*.vercel.app`) | Use browser to capture layout structure and element hierarchy; note v0 styling does NOT match Hub — do not copy colors or spacing verbatim. |
| **GitHub issue screenshot** | Extract layout structure and component regions from the image; describe regions as a list before coding. |
| **v0 chat URL** (`v0.app/chat/...`) | Not reliably accessible by AI; always prefer zip or preview URL instead. |

### Prompt structure for mockup-based tasks

Before generating code, include this block in the agent prompt:

```
Context: [page/feature name and user goal in one sentence]
Design source: [zip | v0 preview | screenshot | written spec]
Hub target path: [vue-app/src/pages/<Page>.vue or vue-app/src/components/<Component>.vue]
Layout pattern: [dashboard → max-w-[1320px] | wide table → max-w-7xl] (from DESIGN.md §5.1)
Hub tokens to apply: [color variables from DESIGN.md §2, e.g. --primary, --secondary]
v0 → Hub substitutions: [list component swaps, e.g. v0 Button → Hub Button variant="secondary"]
Do NOT implement: [list items from issue "Notes" that are mockup-only, e.g. sidebar changes, color scheme changes]
```

### Mapping v0 output to Hub

Always run the **v0 → Hub checklist** (below) after pasting v0 code. Key substitutions:

| v0 output | Hub equivalent |
| --- | --- |
| Generic `Button` / `btn` | Hub `Button` with nearest `variant` from `DESIGN.md §4.2` |
| Inline hex colors / arbitrary Tailwind | Hub `--variable` tokens from `vue-app/src/assets/css/tailwind.css` |
| Non-Hub import paths | Hub `@/components/ui/` barrel imports |
| v0 layout widths | `max-w-[1320px]` for analytics/dashboard; `max-w-7xl` for tables/settings |
| v0 sidebar / nav changes | **Skip unless the issue explicitly requires it** |
| v0 color scheme / brand | **Skip — apply Hub tokens only** |

### What to skip from v0 / mockup output

Issues often include notes like "All other changes in appearance should NOT be added." Treat any appearance change not listed in the acceptance criteria as **out of scope**.

---

## v0 → Hub integration checklist

- [ ] **API & data**: Match existing composables/stores/axios patterns; no ad-hoc fetch layers unless the feature requires it.
- [ ] **Imports**: Use paths and barrel files consistent with neighboring files in the same directory.
- [ ] **Styling**: Tailwind utility classes consistent with adjacent Hub UI; prefer existing shadcn-vue primitives over one-off CSS.
- [ ] **Templates**: If the page is Flask-mounted, ensure the HTML entry exists under `vue-app/templates/` and is listed in Vite rollup inputs where required.
- [ ] **Indentation & syntax**: Vue 3 Composition API; 2 spaces in JS/Vue.
- [ ] **Verification**: `npm run hub` + correct host (Flask vs Vite) per acceptance criteria.
- [ ] **Tests**: At least one focused Vitest spec for non-trivial UI behavior or regressions.

## Automated UI QA — recommendations

| Practice | Why |
| --- | --- |
| **Component tests (Vitest + Vue Test Utils)** | Fast feedback on render, props, emitted events, and conditional UI without full Playwright cost for every change. |
| **Stable selectors** | Prefer `data-testid` or role-based queries where the team standard allows; avoids brittle CSS class assertions. |
| **Stub network and heavy children** | Keeps unit tests deterministic and fast. |
| **Snapshot use** | Sparingly — prefer explicit assertions for Hub design tokens and critical copy. |
| **Playwright / acceptance** | Reserve for cross-cutting flows (login, checkout) per Hub CI; not the first loop for every AI-generated component. |

### Concrete example (validated pattern)

**Spec file** (in Affiliate Hub): `vue-app/tests/components/NavUser.test.js`

**Command** (from repo root):

```bash
cd vue-app && npm test -- tests/components/NavUser.test.js
```

**What it proves**: A real Hub component test using the project’s Vitest setup (`npm test` → `vitest run`) — mount, stubs, and assertions against UI that ships in the sidebar user menu. Use the same command shape for other files: `cd vue-app && npm test -- tests/<path>.test.js`.

Run this command in CI locally before claiming UI work is done; record **exact command + pass/fail** in PR **Tests run**.

## Mandatory gates before merge

These gates are **required**, not optional, for any AI-generated UI change.

| # | Gate | Applies when | How to verify |
| --- | --- | --- | --- |
| 1 | **DESIGN.md compliance** | Any UI change | Check: correct layout width, Hub tokens (no hardcoded hex), shadcn-vue components, `ParentTemplate` wrapper |
| 2 | **v0 cleanup complete** | v0 output was used | Confirm 4 fixes applied: imports → `@/components/ui/`, colors → CSS vars, layout width, `ParentTemplate` |
| 3 | **Vitest — targeted file** | Any non-trivial Vue/component change | `cd vue-app && npm test -- <affected-test-file(s)>` passes |
| 4 | **Hub build** | Templates, Vite inputs, or Flask integration changed | `cd vue-app && npm run hub` exits 0 |
| 5 | **Flask host verify** | Auth-sensitive pages | Page loads correctly at `http://localhost:5000` (not Vite :5173) |
| 6 | **Playwright / acceptance slice** | Issue or verification ladder explicitly requires browser check | Per `docs/RUNBOOKS/VERIFICATION_LADDER.md` |

Gates 1–4 are **always required** for AI-generated UI. Gates 5–6 apply when the feature scope warrants it.

## Minimum testing baseline for AI-generated components

### What counts as "non-trivial" (requires a test)

A component or page is **non-trivial** if it has ANY of:
- Conditional rendering (`v-if`, `v-show`) based on props, store state, or API data
- User interactions (clicks, form inputs, emits)
- Async data loading (skeleton → loaded → error states)
- Role / plan / permission-based display differences
- More than one distinct visual state

A component is **trivial** (test optional) only if it is a pure static layout with no logic — e.g. a header with hardcoded text.

### Baseline per type

| Type | Minimum | Expand when |
| --- | --- | --- |
| **Reusable component** | 1 spec: happy path + 1 failure/empty state | Has role/plan branches → add branch test |
| **Full page** | 1 spec: loaded state + loading/skeleton state | Has empty state, error state, or role gates → add each |
| **Shared layout** (sidebar, nav, header) | 1 spec per user-visible variant | Touches auth or billing → add Playwright slice |

### What to assert (in priority order)

1. **User-visible text** — `wrapper.text().toContain('...')`
2. **Element existence** — `wrapper.find('[data-testid="..."]').exists()`
3. **Conditional branches** — set store/props to each branch value, assert different output
4. **Emitted events** — `wrapper.emitted('event-name')` when the component controls navigation or parent state
5. **Snapshots** — sparingly, only for stable static output; never as the only assertion

## Where prompts and standards live

| Kind | Location |
| --- | --- |
| **v0 system context + task prompt template** | `docs/RUNBOOKS/HUB_AI_UI_WORKFLOW.md` Phase 1 |
| **Test generation prompt** | `docs/RUNBOOKS/HUB_AI_UI_WORKFLOW.md` Phase 4 |
| **DESIGN.md compliance checklist** | `DESIGN.md` §9.2 (affiliate-hub root) — paste into PR or Cursor prompt |
| **Team-wide workflow and checklists** | This file + `HUB_AI_UI_WORKFLOW.md` + `.cursor/skills/hub-ai-ui/SKILL.md` (all propagated) |
| **Prompt experiments / scratch** | Consumer `.cursor/local/**` (not promoted to canonical) |
| **Repo-only UI rules** | Consumer `.cursor/rules/local__*.mdc` after propagation naming rules |

## Risks, tradeoffs, and rationale

| Topic | Tradeoff / risk | Rationale |
| --- | --- | --- |
| **Doc + skill vs always-on rules** | Rules with broad globs can add noise for non-UI work. | Skill + runbook keeps Hub AI UI discoverable without alwaysApply churn. |
| **v0 speed vs Hub fit** | Strict checklists slow the first paste. | Checklist is smaller than rework from wrong host (5173 vs 5000) or missing `npm run hub`. |
| **Drift** | Paths and scripts can change. | Re-verify the Vitest command on main; prefer directory-level references in prose. |
| **Coverage gaps** | Vitest alone does not catch CSS regressions or real SSO. | Layer acceptance tests per Hub docs when the feature warrants it. |

## Return to planning when

- Propagation or drift-check blocks delivery.
- Hub `TEST_FAST` / matrix definitions disagree with this doc.
- Scope expands to migrations, new dependencies, or org-wide UI refactors — split follow-up issues.

## Related

- Dev Agent loop: `.cursor/commands/blueprint.md`
- Verification matrix (this repo): `docs/TESTING_MATRIX.md`
- Hub propagation list: `repos.json`
- **Lasso brand + Hub UI** (in `lassoanalytics/affiliate-hub`): repository root `DESIGN.md` — canonical colors, layout widths, shadcn patterns; read before UI work.
