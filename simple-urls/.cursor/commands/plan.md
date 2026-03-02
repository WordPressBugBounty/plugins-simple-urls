---
description: Plan only (no edits). Optional GitHub Issue/PR URL ingestion + caching. Ends with phase gate + optional Issue comment.
---
# /plan — plan before code

## Goal
Create a minimal, safe plan that matches repo conventions and keeps diffs small.

## Optional input: GitHub Issue/PR URL (URL-as-ID + caching)
Supported:
- `https://github.com/<org>/<repo>/issues/<n>`
- `https://github.com/<org>/<repo>/pull/<n>`

Ingest context **without editing code** (ingest once per chat, then reuse cache):
1) Prefer GitHub CLI:
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

Create an in-chat “Issue Context Cache” block (title/goal/acceptance/constraints/key links/key comments). Always say whether you used the cache or re-fetched (only re-fetch if user asked to refresh or acceptance criteria are missing/ambiguous).

## Output format
- **Restate**:
  - **Goal**: 1 sentence
  - **Constraints**: bullets (include any explicit non-goals)
  - **Open questions**: only if blocking/ambiguous (ask 1–3 before proceeding)
- **Acceptance Criteria**: bullets; each item must be testable/verifiable
- **Decision record** (keep it short, but explicit):
  - **Pick**: chosen approach
  - **Why simplest long-term**: how it minimizes surface area (reuse existing patterns, avoid new deps, reduce future maintenance)
  - **Alts considered**: 1–3 options + why rejected
  - **Non-goals / cut line**: what you are explicitly not doing in this change
- **Build kickoff** (when relevant):
  - First test to write (or characterization test) + where it will live
- **Minimal plan (executable)**:
  - Steps with **explicit files to touch** (and what changes in each)
  - Include **checkpoint tests** inline (what to run after which step)
- **AC → Verification mapping (required)**:
  - Map each acceptance criterion to a verification method (test/command/manual) and where it lives (test file / command)
- **Test Plan**:
  - Prefer **TEST_FAST** during iteration
  - Run **TEST_ALL** before finalizing when feasible
  - Use `docs/TESTING_MATRIX.md` **if present** for repo-specific verification; otherwise, discover the right commands from the repo (e.g. search for `pytest`, `unittest`, `npm test`, `make test`)
- **Definition of Done**:
  - Acceptance criteria met
  - Smallest relevant tests run (or explicit reason they can’t be run)
  - No secrets committed
  - No unintended production behavior change
- **Risks / rollout notes**: only if relevant
- **Return to plan if**: Build uncovers new dependencies, migrations/schema changes, missing ownership/permissions, or scope beyond the cut line

## Constraints
- No production changes without explicit approval.
- No secrets in code/config.
- If the task is ambiguous, surface options and pick the smallest safe default.
- If there’s no Issue yet, consider creating one first via `/issue <summary>` so the plan has a durable URL.

## Plan self-check (required)
- Every acceptance criterion is verifiable and mapped to a check.
- Files-to-touch list is explicit.
- Pick + alts + cut line are stated (decision record).
- Test plan names commands (or states why tests can’t be run + next-best verification).
- “Return to plan if…” triggers are captured (no improvising past the cut line).

## Phase gate (required)
At the end, ask:
- “Ready to move to the next phase?”
  - If the user clicks **Build**, next phase is `/tdd`.
  - Override: if the user does **not** click Build and instead enters `/code`, honor that and proceed to `/code`.
  - `/fix` is for end-to-end execution.

If the user indicates **YES**, ask one more question:
- “Do you want me to post a short summary comment to the GitHub Issue so the team stays in the loop?”

If YES and the input was an **Issue** URL and `gh` is available/authed:
1) Check existing comments for marker:
   - `gh issue view <n> --repo <org>/<repo> --json comments`
   - Marker: `<!-- LASSO_AGENT_PLAN -->`
2) If marker exists: say “Already posted; skipping.”
3) Else post **one** concise comment:
   - `gh issue comment <n> --repo <org>/<repo> --body "<comment body>"`

Comment format (short, useful):
- Title line: “Agent Plan”
- 4–10 bullets max
- Include: “Next step: /tdd” (default when Build is clicked; `/code` only if explicitly requested; `/fix` for end-to-end)
- Include marker at bottom:
  - `<!-- LASSO_AGENT_PLAN -->`
