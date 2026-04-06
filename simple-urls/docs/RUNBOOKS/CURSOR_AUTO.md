# Cursor Auto Auth Runbook

## Purpose
Use this runbook when `/auto` fails closed because it cannot fetch GitHub issue context.

## Supported auth paths

### 1. Environment token for Cursor Cloud or local runs
Prefer this path for `/auto`, especially in Cursor Cloud where local interactive auth will not help.

Provide one of these without echoing the token into shell history:

```bash
read -s GH_TOKEN
export GH_TOKEN
# or
read -s GITHUB_TOKEN
export GITHUB_TOKEN
```

`/auto` will export `GH_TOKEN="${GH_TOKEN:-$GITHUB_TOKEN}"` and use `gh api`.

### 2. Interactive `gh` auth for local/manual runs only
Use this only when you are running the workflow in a local environment where `gh` can persist auth state:

```bash
gh auth login -h github.com
gh auth status -h github.com
```

## Required capabilities
The token or authenticated account must be able to:
- read the target issue
- read issue comments
- create or update pull requests when `/auto` reaches the PR step

## Fail-closed recovery
If auth still fails:
1. Run `gh auth status -h github.com`
2. Confirm the repo/org is accessible with the current token/account
3. In Cursor Cloud, set `GH_TOKEN` or `GITHUB_TOKEN` in the cloud environment and retry `/auto`
4. Retry `/auto`
5. If access cannot be fixed immediately, paste the issue body and acceptance criteria directly so the workflow can proceed with explicit human-provided context
