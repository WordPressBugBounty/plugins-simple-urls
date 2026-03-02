---
description: Address PR review comments: apply valid feedback, explain disagreements, rerun tests, and update PR summary.
---
# /pr — respond to PR review comments

## Workflow
1) **List comments** and group by theme (correctness, style, naming, safety, tests).
2) **Apply what’s correct** with the smallest diff.
3) If you disagree: explain briefly and propose an **Alt**.
4) **Run relevant tests** after changes (see `docs/TESTING_MATRIX.md` **if present**; otherwise discover repo-specific commands, e.g. search for `pytest`, `unittest`, `npm test`, `make test`).
5) Produce a **PR-ready summary block** for the human to reference/copy if needed.
   - Do **not** create PRs.
   - Do **not** write/modify PR descriptions in GitHub (existing automations handle descriptions).
6) **Stage + commit + push** (automatic)
   - Stage the review-driven changes.
   - Commit with message: `PR review fixes`
   - Push the branch to `origin` (never force-push; never push protected branches).

## Subagent wiring (use proactively)
- Delegate to **Test Runner** after applying comment-driven changes.
- If tests fail, delegate to **Debugger**, then re-run tests.
- Delegate to **Verifier** before calling it done.
- If review comments touch security/safety, delegate to **Security Reviewer** and include findings/fixes.

## Output
- **What changed** (mapped to comment threads when possible)
- **Tests run** (exact commands + outcome)
- **Any open questions** (only if blocking)
