---
description: Implement the smallest correct change. Optional GitHub Issue/PR URL ingestion + caching. Tests required (or explicit reason).
---
# /code — implement + verify

## Optional input: GitHub Issue/PR URL (URL-as-ID + caching)
Supported:
- `https://github.com/<org>/<repo>/issues/<n>`
- `https://github.com/<org>/<repo>/pull/<n>`

If provided, ingest once per chat (prefer `gh`) and create/reuse an “Issue Context Cache” block (Title, Goal, Acceptance Criteria, Constraints, Key links, 1–3 key comments). Always say whether you used cache or re-fetched.

## Implementation rules
- Make the **smallest** change that satisfies the request.
- Avoid drive-by refactors unless explicitly requested.
- Keep diffs reviewable; split into small commits only if asked (default: one).

## Testing rules
- Add/adjust tests with the change when feasible.
- Run the smallest relevant test set (use `docs/TESTING_MATRIX.md` **if present**; otherwise discover repo-specific test commands, e.g. search for `pytest`, `unittest`, `npm test`, `make test`).

## Subagent wiring (use proactively)
- After code changes, delegate to **Test Runner** to run `TEST_FAST` (and iterate).
- If tests fail, delegate to **Debugger** for root cause + minimal fix, then re-run tests.
- Once tests are green, delegate to **Verifier** to validate acceptance criteria and hunt edge cases.
- If changes touch user input, auth, SQL, secrets, or config: delegate to **Security Reviewer** and include findings/fixes.

## Final output (required)
- **What changed / Why**
- **Tests run** (exact commands + outcome)
- **Risks** (and any follow-ups)
