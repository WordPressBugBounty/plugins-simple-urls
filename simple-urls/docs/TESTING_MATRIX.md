# Testing Matrix

## Purpose
This file is the repo-local source of truth for the Dev Agent verification loop.

Use it to resolve:
- `TEST_FAST`
- `TEST_ALL`
- targeted verification commands
- approved autofixers
- blocked conditions that should stop the loop instead of guessing

## Resolution rules
1. Pick the smallest verification command that matches the changed surface.
2. Run that command first.
3. If it fails, classify the result as `autofixable`, `needs-code-change`, or `blocked`.
4. Apply a fixer only when this file explicitly approves the failing command and fixer.
5. Re-run the original failing command immediately after any autofix.

## Repo baseline
This repo is process scaffolding, not application runtime code. It does not yet have a standardized automated unit/integration suite.

### Default commands
- `TEST_FAST`: `git diff --check && git diff --cached --check`
- `TEST_ALL`: `default_branch=$(git remote show origin | sed -n '/HEAD branch/s/.*: //p') && git fetch origin "$default_branch" && git diff --check && git diff --cached --check && git diff --check "origin/$default_branch"...HEAD`
- `LINT`: none standardized
- `FORMAT`: none standardized

### Change-surface guidance
| Changed surface | TEST_FAST | TEST_ALL | Approved autofixers |
| --- | --- | --- | --- |
| `.cursor/commands/**/*.md` | default `TEST_FAST` | default `TEST_ALL` | none |
| `.cursor/agents/**/*.md` | default `TEST_FAST` | default `TEST_ALL` | none |
| `.cursor/rules/**/*.mdc` | default `TEST_FAST` | default `TEST_ALL` | none |
| `README.md`, `docs/**/*.md` | default `TEST_FAST` | default `TEST_ALL` | none |
| `scripts/extract_github_issue_image_urls.py` | `python3 scripts/extract_github_issue_image_urls.py --self-test` (in addition to default `TEST_FAST` when this file changes) | default `TEST_ALL` | none |
| `scripts/set_github_project_points.py` | `python3 scripts/set_github_project_points.py --self-test && python3 -m unittest tests/test_set_github_project_points.py` (in addition to default `TEST_FAST` when this file changes) | default `TEST_ALL` | none |
| `.github/workflows/*.{yml,yaml}` | default `TEST_FAST` | default `TEST_ALL` | none |
| `repos.json` | default `TEST_FAST` | default `TEST_ALL` | none |

## Autofix policy
- No autofixers are approved by default in this repo.
- If a command fails here, the usual next action is `needs-code-change` or `blocked`, not a formatter pass.
- Consumer repos that want autofix must add explicit command -> fixer mappings here or in their own repo-local testing matrix.

## Blocked conditions
Mark the loop as `blocked` when:
- the required command is not documented here and cannot be derived safely from repo truth
- a workflow depends on services, auth, or tooling that is not available locally
- the requested verification would mutate remote or production state

## Consumer repo expectation
When this scaffold is propagated into a consumer repo, that repo should extend or replace this matrix with real smoke/full verification commands and any approved autofixers. The Dev Agent loop is only as strong as the repo-local commands encoded here.
