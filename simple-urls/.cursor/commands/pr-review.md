---
description: Senior review pass. Scan for correctness/edge cases/security/perf/tests and provide SHIP/NO SHIP verdict with top risks.
---
# /pr-review — senior review + ship/no-ship

## Workflow
1) Read the diff and provided context (avoid editing PR description; existing automations handle descriptions).
2) Check correctness, edge cases, and failure modes.
3) Check tests: what exists, what was run, what’s missing.
4) Check safety/security posture for the touched surfaces.
5) Provide a clear verdict.

## Subagent wiring (use proactively)
- Delegate to **Security Reviewer** to scan for common vuln patterns and secrets.
- Delegate to **Verifier** to validate behavior and hunt edge cases.

## Output (required)
- **Verdict**: SHIP / NO SHIP
- **Top risks**: 1–5 bullets with concrete failure modes
- **Missing tests / validation**: what should be run (exact commands)
- **Required fixes** (if NO SHIP): smallest set of changes to get to SHIP
