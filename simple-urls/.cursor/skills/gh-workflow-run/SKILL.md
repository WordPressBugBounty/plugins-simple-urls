---
name: gh-workflow-run
description: Trigger a GitHub Actions workflow with `gh`, watch it to completion, and summarize results. Defaults to non-prod workflows and the current branch; prod workflows require explicit user request.
---

# GitHub workflow runner (repo-agnostic)

Use this when the user says things like:
- “Trigger the staging deploy”
- “Run the release workflow”
- “Kick off workflow X and tell me if it passed”

## Hard safety rules
- **No production by default**: do not trigger prod workflows unless the user explicitly says “prod” (and you acknowledge rollout/rollback expectations in chat).
- **No main/master by default**: default ref is the current branch unless the user specifies otherwise.
- **No secrets**: never echo secret values or paste sensitive log lines.

## Workflow

### 0) Preconditions
Run:

```bash
gh --version
gh auth status
```

If not authenticated, stop and ask the user to run `gh auth login`.

### 1) Choose workflow + ref (safe defaults)
Find candidate workflows:

```bash
gh workflow list
```

Pick the workflow by one of:
- exact workflow file name (e.g. `staging.yml`), or
- unique workflow name (shown by `gh workflow list`)

Pick ref:

```bash
ref="$(git rev-parse --abbrev-ref HEAD)"
```

Override `ref` only if the user asked for a different ref.

### 2) Enforce environment gating
If workflow name/file suggests production (case-insensitive match like `prod`, `production`):
- **stop** unless the user explicitly requested prod
- if explicitly requested: state the minimal rollout/rollback note in chat before running

### 3) Trigger the workflow
Run:

```bash
gh workflow run "<WORKFLOW>" --ref "$ref"
```

If the workflow requires inputs, discover them:

```bash
gh workflow view "<WORKFLOW>" --yaml
```

Then rerun with `--field key=value` as needed.

### 4) Watch the run and summarize
Find the newest run for that workflow+ref and watch it:

```bash
gh run list --workflow "<WORKFLOW>" --branch "$ref" --limit 5
gh run watch <RUN_ID> --exit-status
```

If it fails, collect failed-step logs only:

```bash
gh run view <RUN_ID> --log-failed
```

Summarize:
- **Run URL**
- **Conclusion**
- **Failing job/step + first actionable error**
- **Smallest next step** (local repro command or file path to inspect)

