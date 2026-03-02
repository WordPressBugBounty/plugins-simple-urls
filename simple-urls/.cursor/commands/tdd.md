---
description: TDD flow: optional GitHub Issue/PR URL ingestion + caching; write failing/characterization test, implement fix, iterate to green.
---
# /tdd — test-first (or characterization-first)

## Optional input: GitHub Issue/PR URL (URL-as-ID + caching)
Supported:
- `https://github.com/<org>/<repo>/issues/<n>`
- `https://github.com/<org>/<repo>/pull/<n>`

If provided, ingest once per chat (prefer `gh`) and create/reuse an “Issue Context Cache” block (Title, Goal, Acceptance Criteria, Constraints, Key links, 1–3 key comments). Always say whether you used cache or re-fetched.

## Workflow
1) **Reproduce**: express the bug/behavior as a test that fails for the right reason.
2) **Implement**: minimal fix to make the test pass.
3) **Refactor**: only if needed, keeping behavior stable and diff small.

## Subagent wiring (use proactively)
- Delegate to **Test Runner** to drive the loop (run → fail → fix → run).
- If failures are confusing, delegate to **Debugger** to isolate root cause and implement the minimal fix.
- After green, delegate to **Verifier** to validate edge cases and coverage gaps.
- If changes touch user input/auth/SQL/secrets/config/web output: delegate to **Security Reviewer** and address findings minimally (then re-run tests).

## Notes
- If pure unit test is hard, write a **characterization test** to lock current behavior first.
- Use `docs/TESTING_MATRIX.md` **if present** for the right verification commands; otherwise, discover the right commands from the repo (e.g. search for `pytest`, `unittest`, `npm test`, `make test`).
- If you truly can’t write a test: explain why and provide the next-best deterministic verification.

## Final output (required)
- **Test added** (what it covers)
- **Fix** (minimal change summary)
- **Tests run** (commands + outcome)
