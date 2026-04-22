# Hub AI UI — End-to-End Workflow

Companion to `HUB_AI_UI.md`. This file is the **executable workflow** — structured for AI agents to follow without ambiguity. Read `HUB_AI_UI.md` for rationale and full checklists; for the pre-PR compliance checklist see `DESIGN.md` §9.2.

---

## Phase 0 — Context load (required before any UI task)

Run these reads in order, every time:

1. `DESIGN.md` (repo root) — color tokens, layout widths, shadcn-first rules.
2. `docs/RUNBOOKS/HUB_AI_UI.md` — v0 checklist, QA gates, build steps.
3. `vue-app/AGENTS.md` — build and test commands.

Then decide:

| Decision | Rule |
| --- | --- |
| **Target path** | Full page → `vue-app/src/pages/[Page].vue`; reusable block → `vue-app/src/components/[Component].vue` |
| **Layout width** | Dashboard / analytics / detail → `max-w-[1320px] mx-auto p-4 md:p-10 space-y-6`; data table / settings → `max-w-7xl mx-auto` |
| **Flask wiring needed?** | Yes if the page is served by Flask (most pages). Requires 3 files — see Phase 2. |

---

## Phase 1 — Prompt construction

Before generating any code, build the full prompt block. **Do not skip fields.**

> All prompts are self-contained in the sections below.

```
Context: [page/feature name] — [user goal in one sentence]

Design source: [zip | v0 preview URL (*.vercel.app) | GitHub issue screenshot | written spec]

Hub target path: vue-app/src/pages/[Page].vue
                 OR vue-app/src/components/[Component].vue

Layout pattern: max-w-[1320px] mx-auto p-4 md:p-10 space-y-6
                (use max-w-7xl for data tables / settings)

Hub tokens to apply:
  - Primary CTA:      Button (default variant)
  - Secondary action: Button variant="secondary"
  - Muted text:       class="text-muted-foreground text-sm"
  - Loading state:    <Skeleton /> inside <CardContent>
  - Notifications:    <Toaster />

v0 → Hub substitutions (fill in if using v0 output):
  - [v0 component] → [Hub equivalent, e.g. Button variant="outline"]
  - [v0 color/hex]  → [Hub token, e.g. text-primary]

Do NOT implement:
  - [List every item from the issue "Notes" section that is mockup-only]
  - Default: skip all sidebar/nav changes unless explicitly in acceptance criteria
  - Default: skip v0 color scheme — apply Hub tokens only
```

### v0 system context (paste at the start of every new v0 chat)

```
I'm building for a Vue 3 SaaS app (Affiliate Hub). Follow these rules:
- Vue 3 Composition API, <script setup>, 2-space indent
- Tailwind with CSS variables: bg-primary, text-primary-foreground, text-muted-foreground
  NOT hardcoded hex values
- Dashboard pages: max-w-[1320px] mx-auto p-4 md:p-10 space-y-6
- Wide table/settings pages: max-w-7xl mx-auto
- Button variants: default | secondary | outline | ghost | destructive | simple
- Always wrap page content in <ParentTemplate>
- Use shadcn-vue: Button, Card, CardHeader, CardTitle, CardContent, Skeleton,
  Dialog, Badge, Toaster — import from @/components/ui/
- Loading: <Skeleton class="h-4 w-full" /> inside <CardContent>
- NO Bootstrap, NO custom CSS, NO inline styles, NO hardcoded colors
```

---

## Phase 2 — Integration (after pasting v0 or agent output)

### v0 → Hub: 4 fixes always required

| v0 output | Replace with |
| --- | --- |
| Import paths outside `@/components/ui/` | `@/components/ui/[Component].vue` barrel import |
| Hardcoded hex / arbitrary Tailwind color | Hub `--variable` token (`bg-primary`, `text-secondary`, etc.) |
| Layout width (`max-w-6xl`, `max-w-screen-xl`, etc.) | `max-w-[1320px]` (dashboard) or `max-w-7xl` (tables) |
| Content without `<ParentTemplate>` wrapper | Wrap in `<ParentTemplate>` |

### Flask wiring (3 files — every new full page)

```bash
# 1. HTML entry point
vue-app/templates/[page].html

# 2. Vite rollup input
vue-app/vite.config.js  →  add to rollup.input: { [page]: 'vue-app/templates/[page].html' }

# 3. Flask route
affiliatehub/vue/vue.py  →  add @blueprint.route('/[route]')
```

### Evidence priority when working from a mockup

| Source | Action |
| --- | --- |
| **Zip / exported code** | Extract → use as skeleton → apply Hub tokens |
| **v0 preview URL** (`*.vercel.app`) | Screenshot layout structure; do not copy colors or spacing verbatim |
| **GitHub issue screenshot** | List regions as plain text first, then code |
| **v0 chat URL** (`v0.app/chat/...`) | Unreliable for AI access — request zip or preview URL instead |

---

## Phase 3 — Verification

Run in this order:

```bash
# 1. Build (required when templates or Flask integration changed)
cd vue-app && npm run hub

# 2. Verify on Flask — NOT the Vite dev server for auth-sensitive pages
open http://localhost:5000/[route]

# 3. Run the targeted test file
cd vue-app && npm test -- tests/[pages|components]/[Name].test.js
```

### Mandatory gates

Gates 1–4 are **always required** for any AI-generated UI change. Gates 5–6 apply by scope.

| # | Gate | Required when |
| --- | --- | --- |
| 1 | **DESIGN.md compliance** — layout width, Hub tokens, shadcn, `ParentTemplate` | Any UI change |
| 2 | **v0 cleanup complete** — 4 fixes applied (imports, colors, width, ParentTemplate) | v0 output was used |
| 3 | `cd vue-app && npm test -- <file>` passes | Any non-trivial Vue / component change |
| 4 | `cd vue-app && npm run hub` exits 0 | Templates, Vite inputs, or Flask integration changed |
| 5 | Page loads on Flask `http://localhost:5000` | Auth-sensitive pages |
| 6 | Playwright acceptance slice | Issue or verification ladder explicitly requires browser check |

---

## Phase 4 — Test authoring

**Rule:** every AI-generated component that is **non-trivial** must have at least one Vitest spec.

**Non-trivial** = has ANY of: conditional rendering (`v-if`/`v-show`), user interactions, async data loading, role/plan/permission branches, or more than one visual state. A purely static layout with no logic is the only exception.

### Minimum baseline by type

| Type | Minimum spec coverage |
| --- | --- |
| **Reusable component** | Happy path + 1 failure/empty state; add branch test per `v-if` on role/plan |
| **Full page** | Loaded state + loading/skeleton state; add empty/error/role states as needed |
| **Shared layout** (sidebar, nav) | 1 spec per user-visible variant; add Playwright if touches auth or billing |

### Assertion priority

1. `wrapper.text().toContain('...')` — user-visible text
2. `wrapper.find('[data-testid="..."]').exists()` — element presence
3. Set store/props per branch → assert different output — conditional branches
4. `wrapper.emitted('event-name')` — emitted events
5. Snapshots — sparingly, only for stable static output; never as the sole assertion

### Spec file location

```
vue-app/tests/pages/[Page].test.js
vue-app/tests/components/[Component].test.js
```

### Test generation prompt (paste into Cursor after the component is created)

```
Create a Vitest spec at vue-app/tests/[pages|components]/[ComponentName].test.js

Cover:
1. Happy path — component renders expected content when data is loaded
2. Loading/skeleton state — correct UI shown before data arrives
3. [Add one per v-if branch: empty state / error state / role gate]

Follow this pattern:
- vi.mock() calls BEFORE the component import
- Stub all shadcn Dialog / DropdownMenu / Popover with { template: '<div><slot /></div>' }
- Stub lucide-vue-next icons with { template: '<span />' }
- Assert user-visible text, not CSS classes
- Use data-testid selectors where available

Reference: vue-app/tests/components/NavUser.test.js
```

### Boilerplate (copy for each new spec)

```javascript
import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

// --- Mocks MUST come before the component import ---
vi.mock('../../src/stores/[store].js', () => ({ useStore: vi.fn(() => ({ ... })) }))
vi.mock('axios', () => ({ default: { get: vi.fn(), post: vi.fn() } }))
// Stub icon libraries (they use Teleport / Portal — fail in jsdom)
vi.mock('lucide-vue-next', () => {
    const S = { template: '<span />' }
    return { ChevronDown: S, ChevronsUpDown: S }
})

import MyComponent from '../../src/components/[Component].vue'

// Stub heavy shadcn primitives that use Portal
const Slot = { template: '<div><slot /></div>' }

describe('[Component]', () => {
    it('renders expected content in happy path', () => {
        const wrapper = mount(MyComponent, {
            global: { stubs: { Dialog: Slot, DropdownMenu: Slot } },
        })
        expect(wrapper.text()).toContain('[expected text]')
    })

    it('shows empty state when data is missing', () => {
        // set store/props to empty state
        const wrapper = mount(MyComponent, {
            global: { stubs: { Dialog: Slot, DropdownMenu: Slot } },
        })
        expect(wrapper.find('[data-testid="empty-state"]').exists()).toBe(true)
    })
})
```

### Selector rules

```javascript
// Prefer (stable)
wrapper.find('[data-testid="..."]')
wrapper.find('[role="button"]')
wrapper.text().toContain('...')

// Avoid (brittle — breaks on CSS refactor)
wrapper.find('.bg-primary.rounded-lg.px-4')
```

### Critical: mock ordering

`vi.mock()` calls are hoisted by Vitest. Place them **before** `import` statements or the component will load with real dependencies.

```javascript
// CORRECT
vi.mock('../../src/stores/profile-store.js', () => ({ ... }))
import NavUser from '../../src/components/sidebar/NavUser.vue'

// WRONG — mock arrives too late
import NavUser from '../../src/components/sidebar/NavUser.vue'
vi.mock('../../src/stores/profile-store.js', ...)
```

---

## Component size rule

If an AI-generated page exceeds ~400 lines, split into sub-components under `vue-app/src/components/[feature]/`. Each sub-component gets its own test file. A 1000-line single-file component is not testable at acceptable granularity.

---

## Quick reference

```
Phase 0  Read DESIGN.md → HUB_AI_UI.md → vue-app/AGENTS.md
Phase 1  Fill prompt template (below) + paste v0 system context (below) into v0
Phase 2  Fix 4 v0 issues → wire Flask (3 files)
Phase 3  Gates 1–4: DESIGN.md check → v0 cleanup → npm test -- <file> → npm run hub
Phase 4  Write spec: non-trivial? → happy path + empty/error state per type table
```

## Related

- Technical runbook + full gate definitions: `docs/RUNBOOKS/HUB_AI_UI.md`
- Design system + pre-PR compliance checklist (§9.2): `DESIGN.md` (affiliate-hub root)
- Skill entry point: `.cursor/skills/hub-ai-ui/SKILL.md`
