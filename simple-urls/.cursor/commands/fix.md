---
description: End-to-end workflow. Optional GitHub Issue/PR URL ingestion + caching. Ingest → restate → plan → implement → test loop → verify → (security) → PR-ready summary.
---
# /fix — ingest → plan → implement → test → verify → PR-ready

## Default test commands (repo)
- **TEST_FAST**: `python -m unittest discover tests/`
- **TEST_ALL**: `python -m unittest discover tests/`
- **LINT**: (none standardized)

## Guardrail (non-negotiable)
- Do not guess silently. If context is missing/ambiguous, **stop and ask** 1–3 targeted questions before coding.

## Flow (numbered; follow closely)
0) **Ingest context (preferred: GitHub URL; cache it)**
   - Supported:
     - `https://github.com/<org>/<repo>/issues/<n>`
     - `https://github.com/<org>/<repo>/pull/<n>`
   - Prefer `gh`:
     - `gh --version`
     - `gh auth status`
     - Issue: `gh issue view <n> --repo <org>/<repo> --json title,body,comments,labels,assignees`
     - PR: `gh pr view <n> --repo <org>/<repo> --json title,body,comments,files,commits,checks`
   - If `gh` unavailable/not authed: try web context; otherwise ask user to paste body + 1–3 key comments.
   - In this chat, ingest once and create an “Issue Context Cache” block (Title, Goal, Acceptance Criteria, Constraints, Key links, 1–3 key comments). Reuse cache on subsequent invocations; only re-fetch if user asks or acceptance criteria are missing/ambiguous. Always say whether you used cache or re-fetched.

1) **Restate the task (before coding)**
   - Goal
   - Acceptance Criteria
   - Constraints
   - Likely files
   - If ambiguous: ask 1–3 questions **before** coding.

2) **Minimal plan**
   - Steps + files
   - Test plan (TEST_FAST during iteration; TEST_ALL before finalizing when feasible; include repo-specific verification from `docs/TESTING_MATRIX.md` **if present**; otherwise discover the right commands from the repo)
   - Risk / rollout notes (only if relevant)

3) **Implement smallest correct change**
   - No unrelated refactors/renames/reformatting.

4) **Test loop**
   - Use **Test Runner** proactively.
   - If tests fail → delegate to **Debugger** → minimal fix → re-run tests.
   - Repeat until green.

5) **Verify**
   - Delegate to **Verifier** to validate acceptance criteria + edge cases.
   - If issues found: fix minimally + re-run TEST_FAST.

6) **Security pass (when relevant)**
   - If changes touch user input/auth/SQL/secrets/config/web output: delegate to **Security Reviewer**.
   - Apply minimal fixes; re-run TEST_FAST.

7) **PR-ready summary block (human reference only)**
   - Do **not** create PRs.
   - Do **not** auto-write/modify PR descriptions.

### What / Why

### Tests run

### Risk

### Rollout / Rollback

### Notes / Follow-ups
