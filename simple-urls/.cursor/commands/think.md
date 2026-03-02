---
description: Investigation only (read-only). Optional GitHub Issue/PR URL ingestion + caching. Ends with phase gate + optional Issue comment.
---
# /think — investigate (no changes)

## Scope (non-negotiable)
- Read-only: do **not** modify files, run formatters, or make commits.
- Goal: reduce uncertainty; report what’s true in this repo with citations (paths/commands/output).

## Optional input: GitHub Issue/PR URL (URL-as-ID + caching)
Supported:
- `https://github.com/<org>/<repo>/issues/<n>`
- `https://github.com/<org>/<repo>/pull/<n>`

Do (ingest once per chat):
1) Try GitHub CLI first:
   - `gh --version`
   - `gh auth status`
2) Fetch:
   - Issue:
     - `gh issue view <n> --repo <org>/<repo> --json title,body,comments,labels,assignees`
   - PR:
     - `gh pr view <n> --repo <org>/<repo> --json title,body,comments,files,commits,checks`
3) If `gh` is unavailable/not authed:
   - Attempt to read the URL via available web context
   - Otherwise ask the user to paste the Issue/PR body + 1–3 key comments

Then create an in-chat “Issue Context Cache” block and reuse it on subsequent invocations. Do **not** repeatedly paste full threads. Always say whether you used the cache or re-fetched (only re-fetch if user asked to refresh or acceptance criteria are missing/ambiguous).

Example cache shape:

```md
### Issue Context Cache (from <url>)
- **Title**:
- **Goal**:
- **Acceptance criteria**:
- **Constraints**:
- **Key links**:
- **Key comments (1–3)**:
```

## Output (required)
- **Goal**: 1 sentence
- **Hypotheses**: 2–5 bullets (what might be happening + why)
- **Evidence to gather**: bullets with exact commands to run (smallest first)
- **Likely files**: bullets
- **Recommendation**: next phase (`/plan` or `/tdd` for small obvious bugs). If there’s no Issue yet and we should capture this work: suggest `/issue <summary>`.

## Phase gate (required)
At the end, ask:
- “Ready to move to the next phase?”
  - Next phase is `/plan` (or `/tdd` for small obvious bugs).

If the user indicates **YES**, ask one more question:
- “Do you want me to post a short summary comment to the GitHub Issue so the team stays in the loop?”

If YES and the input was an **Issue** URL and `gh` is available/authed:
1) Check existing comments for marker:
   - `gh issue view <n> --repo <org>/<repo> --json comments`
   - Marker: `<!-- LASSO_AGENT_THINK -->`
2) If marker exists: say “Already posted; skipping.”
3) Else post **one** concise comment:
   - `gh issue comment <n> --repo <org>/<repo> --body "<comment body>"`

Comment format (short, useful):
- Title line: “Agent Investigation Summary”
- 4–10 bullets max
- Include: “Next step: /plan” (or `/tdd`)
- Include marker at bottom:
  - `<!-- LASSO_AGENT_THINK -->`
