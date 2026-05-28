# PR review — screenshots and pasted feedback (Slack / Cloud)

Canonical **execution** runbook for the **pr-review-slack** skill (`.cursor/skills/pr-review-slack/SKILL.md`). Part of the same unified workflow as [`pr.md`](../../.cursor/commands/pr.md) (`/pr`). **Do not fork** thread-resolution behavior: use the bash block in `pr.md` under **Resolve addressed review threads** verbatim when resolving threads after push.

## When to use this runbook vs `/pr`

| Situation | Use |
| --- | --- |
| PR review **screenshot** (Copilot, GitHub, etc.) or **long pasted** review text | This runbook (then skill index) |
| No screenshot/paste; fetch from GitHub, Copilot threads, Path A/B | [`pr.md`](../../.cursor/commands/pr.md) only |

**Invocation cues:** `pr`, `/pr`, `pr-review`, `address this`, `fix review`, `apply feedback`, or similar — routing from always-on `.cursor/rules/pr-routing-stub.mdc` still lands on `pr.md`; use this runbook when applying **visual or pasted** feedback.

## Workflow

1. **Extract feedback** from the screenshot or pasted text
   - List comments and group by theme (correctness, style, naming, safety, tests).
   - If the image shows a diff + suggested change, apply the suggestion when it is valid.

2. **Apply what is correct** with the smallest diff
   - **UI guardrail** (same as `pr.md`): do not modify icons, labels, copy text, layout, or styling unless the issue causes a critical system failure or violates core functionality.
   - Prefer the reviewer’s suggested change when it improves clarity or robustness.
   - If you disagree: explain briefly and propose an **Alt**.

3. **Run relevant tests** after changes
   - Check `docs/TESTING_MATRIX.md` if present; otherwise discover repo commands (`pytest`, `unittest`, `make test`, etc.).
   - If tests cannot run locally (missing deps), note the CI command in the summary.

4. **Produce a PR-ready summary block**
   - Map changes to comment threads.
   - Do **not** create PRs or modify PR descriptions (existing automations handle that).

5. **Stage + commit + push**
   - Stage review-driven changes.
   - Commit message: `PR review fixes`
   - Push to `origin` (no force-push; no protected branches).

6. **Run review flow** (Security Reviewer + Verifier)
   - **Security Reviewer** (required): scan for vulns/secrets.
   - **Verifier** (required): validate behavior and edge cases.
   - Produce verdict (SHIP / NO SHIP) and top risks.

7. **Resolve addressed review threads** (after successful push)
   - When all visible feedback was addressed (fixed or explicitly declined), mark threads resolved using **exactly** the GraphQL flow and bash in `pr.md` → section **Resolve addressed review threads (Path B only, after push)**.
   - Skip if: no PR for current branch; user asked not to resolve; not all visible feedback was addressed.
   - **Cursor Cloud / `@cursor PR`:** mandatory when GitHub feedback was addressed; do not skip because the run did not start with IDE `/pr`.

## Subagent wiring

- **Test Runner:** after applying changes.
- **Debugger:** if tests fail.
- **Security Reviewer** (required).
- **Verifier** (required).

## Output (required)

- **PR link** (when it exists)
- **What changed** (mapped to comment threads)
- **Tests run** (exact commands + outcome)
- **Resolved threads** (count, or `skipped`)
- **Verdict:** SHIP / NO SHIP
- **Top risks** (from Security Reviewer + Verifier)
- **Open questions** (only if blocking)
