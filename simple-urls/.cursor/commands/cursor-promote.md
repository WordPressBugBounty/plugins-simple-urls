---
description: Trigger promotion of this repo’s .cursor/ to the canonical lassoanalytics/cursor-config repo.
---
# /cursor-promote — promote `.cursor/` to canonical

## Trigger promotion (manual)
From your branch in this repo:

```bash
./scripts/cursor-promote
```

This requires:
- `gh` installed
- `gh auth login` completed

This script opens (or reuses) a PR in `lassoanalytics/cursor-config` that updates canonical `.cursor/` using your `gh` credentials.

## Notes
- Promotion uses `rsync --delete`.
- Anything under `.cursor/` (including `.cursor/skills/**`) is part of the canonical contract and will be enforced by drift-check in repos that compare against `cursor-config`.
- Exclusions:
  - `.cursor/environment.json` (developer-local)
  - `.cursor/local/**` (repo-local overlay; never promoted)
  - `.cursor/rules/local__*.mdc` (repo-local Cursor rules; never promoted)
- If canonical already matches, the script exits without opening a PR.
