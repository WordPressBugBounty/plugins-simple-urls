---
name: ci-triage
description: Triage failing GitHub Actions checks with `gh` (read-only). Summarize failing jobs/steps and propose smallest next debugging step. Never rerun jobs or change code unless explicitly asked.
---

# CI triage (repo-agnostic)

Use this when the user says things like:
- “CI is failing—what broke?”
- “What checks are red on this PR?”
- “Can you summarize the failing job logs?”

## Hard safety rules
- **Read-only**: do not commit, push, or modify files.
- **No reruns**: do not rerun jobs (`gh run rerun`) unless the user explicitly asks.
- **No secrets**: if logs contain tokens/keys/credentials, **do not quote them**; redact and call it out.

## Workflow

### 0) Preconditions
Run:

```bash
gh --version
gh auth status
```

If not authenticated, stop and ask the user to run `gh auth login`.

### 1) Identify the target (PR first, otherwise branch)
Prefer PR context if available.

- If the user provided a PR URL/number:

```bash
gh pr view <PR_NUMBER> --json number,title,url,headRefName,baseRefName
gh pr checks <PR_NUMBER> --watch=false
```

- Otherwise (no PR given), use the current branch:

```bash
branch="$(git rev-parse --abbrev-ref HEAD)"
gh run list --branch "$branch" --limit 10
```

### 2) Pull the failing run(s) and logs
Pick the most recent failing run for the PR/branch/workflow and collect logs for **failed steps only**.

```bash
gh run view <RUN_ID> --json status,conclusion,htmlURL,workflowName,createdAt,updatedAt
gh run view <RUN_ID> --log-failed
```

If multiple workflows fail, repeat for each but keep output tight.

### 3) Summarize (high-signal)
Produce:
- **What failed**: workflow name + job + step.
- **First real error**: the earliest actionable error line (not cascading failures).
- **Likely cause**: one sentence, evidence-backed.
- **Smallest next step**: the minimum local command or file to inspect to confirm/fix.

### 4) If the user asks to fix
Only after the user explicitly asks to fix code/config:
- propose the smallest fix plan
- then execute it (tests → green)

