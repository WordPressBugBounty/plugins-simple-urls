# TDD enforcement (Claude Code)

> Mirrors `.cursor/commands/tdd.md` as an **always-on rule** for Claude
> Code (everything under `.claude/rules/` loads automatically). Edit the
> Cursor command first; keep this file in contract sync. Adapt only
> invocation / specialist-transport details.

## Hard rule: no bug fix without a failing test first

Whenever you are fixing a bug, closing a GitHub Issue, or implementing
anything from `/auto` — **write the test before the fix**. Same gate
weight as the Complexity gate in `.claude/commands/auto.md`.

## Optional input: GitHub Issue/PR URL (URL-as-ID + caching)

Supported:

- `https://github.com/<org>/<repo>/issues/<n>`
- `https://github.com/<org>/<repo>/pull/<n>`

If provided, ingest once per chat (prefer `gh`) and create/reuse an
Issue Context Cache block (Title, Goal, Acceptance Criteria, Constraints,
Key links, 1–3 key comments). Always say whether you used cache or re-fetched.

**GitHub-hosted images (mandatory when an Issue or PR URL is in scope):**
After `gh issue view` / `gh pr view` (or pasted Issue/PR markdown), complete
**`.cursor/docs/RUNBOOKS/GITHUB_ISSUE_PR_IMAGE_INTAKE.md`** end-to-end
**before** writing or changing tests when screenshots matter; merge
**Visual evidence** into the cache. Follow
**`.cursor/skills/github-issue-images/SKILL.md`**.

### The loop (RED → GREEN → REFACTOR)

1. **RED** — Write a test that reproduces the bug or exercises the missing
   behavior. Run it. **Confirm it fails for the right reason** (the actual
   bug being fixed, not an import error, missing fixture, or unrelated
   crash). If you can't get a clean failing-for-the-right-reason state,
   you don't understand the bug well enough to fix it yet — keep digging.
2. **GREEN** — Implement the smallest change that makes the test pass.
   Run it again. Confirm green.
3. **REFACTOR** (only if needed) — Clean up without changing behavior.
   Re-run the test suite after any refactor to confirm nothing broke.
4. **Loop** — follow `.cursor/commands/blueprint.md` for failure
   classification (`autofixable` / `needs-code-change` / `blocked`),
   approved autofixes, and bounce-back. If `blocked`, emit the
   **Failure Handoff** JSON from that file and stop.
5. **Affiliate Hub pre-push gate** (when cwd/repo is `affiliate-hub`):
   run `pytest tests/` before push or handoff; fail-closed — see
   `.cursor/rules/affiliate-hub-ci-verify.mdc`. Prefer
   `scripts/run_ci_pytest.sh` when Docker is available.

### When a pure unit test is hard

Write a **characterization test** first — one that locks in current
(possibly buggy) behavior — so you have a deterministic baseline before
you change anything. Then modify the test to express the *desired*
behavior, watch it fail, and fix.

### When you truly cannot write a test

This should be rare (e.g. the only reproduction path requires live
production infrastructure with no local equivalent). In that case:

- Explain concretely why in the PR description.
- Provide the next-best **deterministic** verification instead — e.g. a
  live-log sampling methodology with an explicit before/after comparison.
  "I read the code and it looks right" is never sufficient.
- Still add a unit test for anything in the diff that *can* be tested
  in isolation.

### Test discovery order

Resolve the right verification commands from repo truth, in this order:

1. `.cursor/docs/TESTING_MATRIX.md` (or `docs/TESTING_MATRIX.md`)
2. `AGENTS.md` (Tests section)
3. Repo runbooks (`docs/RUNBOOKS/*.md`)
4. `.github/workflows/*.yml`
5. `Makefile` / project config files
6. Search the repo for the test runner (`pytest`, `unittest`, `npm test`)

### Specialist panel (adapt Cursor `mcp_task`)

Use proactively, same roles as `.cursor/commands/tdd.md`:

- Drive the loop (run → fail → fix → run).
- After green, run the Verifier checklist (edge cases + coverage gaps).
- If changes touch user input/auth/SQL/secrets/config/web output: run
  the Security Reviewer checklist and address findings minimally, then
  re-run tests.
- Claude Code has no Cursor `mcp_task` — spawn Task/subagent runs when
  available, otherwise run those checklists in-process. Do not skip them.

### Success-path coverage

Don't stop at the regression test for the bug. If the affected area has
no test for the normal/happy path, add one.

### Create PR (when the user asks)

Follow **`.cursor/commands/git-create-pr.md`** end-to-end:

- **Before create issue** — run `resolve_driving_issue_ladder()` on the
  **full conversation**. If `phase_followup_should_reuse_parent()` is
  true, **reuse** that issue; do **not** call `create_lasso_issue.py`.
- **Issue resolution ladder** — scan the **full conversation** first;
  file a driving **OPEN** issue only when the thread has zero ticket
  references.
- **Multi-phase** — additional PRs in the same chat epic link to the
  **same** driving issue (`Fixes #N` on line 1).
- **PR body line 1** — `Fixes #<n>` or `Fixes org/repo#<n>`.
- **After create** — verify `closingIssuesReferences` GraphQL.

Do **not** open a PR without an issue unless the human said
`skip issue link`. Issue comments and `Refs #N` do not satisfy
the Development sidebar requirement.

### Final output (required whenever this rule applies)

State explicitly, every time:

- **Test added** — what it covers, and whether it's a true unit test or a
  characterization test
- **RED confirmed** — that you ran it and it failed for the right reason
  before implementing the fix
- **Fix** — minimal change summary
- **GREEN confirmed** — tests run (exact commands) + pass/fail
- **Affiliate Hub**: if this repo is `affiliate-hub`, include pre-push
  `pytest tests/` (or `scripts/run_ci_pytest.sh`) result; do not mark
  done if the CI suite was not green when runnable
- **Failure Handoff** JSON from `.cursor/commands/blueprint.md` when
  work is handed off or stopped as `blocked`
- If you skipped writing a test: the explicit reason + the deterministic
  alternative verification you used instead

Never report "fixed" without one of: a passing regression test you can
point to, or the explicit test-skip justification above.
