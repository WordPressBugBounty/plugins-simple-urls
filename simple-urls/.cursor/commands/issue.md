---
description: Create a GitHub Issue from a prompt. Hybrid idea format (easy for `/plan <url>`) + concise Definition of Done. Uses `gh`.
---

# /issue — create a GitHub Issue (from prompt)

## Goal
Turn a freeform prompt into a GitHub Issue that’s easy to consume later (especially via `/plan <url>`).

## Input
- `/issue <text>` where `<text>` is the prompt you want captured as an Issue.
  - Include links, snippets, or “the thing we’ve been discussing” — summarize aggressively.

If `<text>` is missing: ask the user for a 1–3 sentence prompt.

## Guardrails (non-negotiable)
- No secrets (keys/tokens/passwords/private URLs/customer data).
- Don’t overfit: capture the *intent* + *verifiable outcomes*, not every chat detail.

## Repo + auth (preferred)
Use GitHub CLI when available:
- `gh --version`
- `gh auth status`
- Determine repo (prefer current): `gh repo view --json nameWithOwner --jq .nameWithOwner`

If `gh` is unavailable or not authed:
- Ask the user to authenticate (`gh auth login`) or choose **Draft-only** behavior for this invocation (print title/body).

## Draft the Issue

### Title rules
- ≤ 72 chars, specific, imperative.
- If needed, prefix type: `feat:`, `fix:`, `chore:`, `docs:`.

### Body template (hybrid)
Fill these sections; keep it short and testable.

```md
## Goal
<1 sentence>

## Context
<2–6 bullets: what/why/where; any relevant background>

## Acceptance Criteria
- [ ] <verifiable outcome>
- [ ] <verifiable outcome>

## Constraints / Non-goals
- <constraint or explicit non-goal>

## Key links
- <url or file path>

## Notes / Open questions
- <unknowns, decisions needed, risks>

## Definition of Done
- [ ] Acceptance criteria met
- [ ] Smallest relevant tests run (or explicit reason)
- [ ] No secrets committed
- [ ] No unintended production behavior change

<!-- LASSO_AGENT_ISSUE -->
```

## Create the Issue (side effect)
Create the Issue via `gh` and return the URL.

Safer command (avoid brittle shell quoting; supports arbitrary body text):

```bash
cat > /tmp/issue-title.txt <<'EOF'
<title>
EOF

cat > /tmp/issue-body.md <<'EOF'
<body>
EOF

gh issue create --repo <org>/<repo> --title "$(cat /tmp/issue-title.txt)" --body-file /tmp/issue-body.md
```

If labels/assignees are provided in the prompt, include them:
- Labels: `--label bug --label enhancement` (repeat `--label` for multiple)
- Assignees: `--assignee "<handle>"` (repeat `--assignee` for multiple; `@me` works too)

## Output (required)
- The created Issue URL
- The final title
- A 3–6 bullet summary of what you captured (no wall-of-text reprint)

