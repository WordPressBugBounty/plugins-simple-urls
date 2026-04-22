# Cursor Auto Auth Runbook

## Purpose
Use this runbook when `/auto` fails closed because it cannot fetch GitHub issue context, or when a GitHub issue includes screenshots that must be read before implementation.

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

`/auto` should set `GH_TOKEN="${GH_TOKEN:-$GITHUB_TOKEN}"` before calling `gh api`.

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

## Ingestion paths: text vs images

GitHub stores issue/comment bodies as **Markdown** (REST/GraphQL `body` fields). Uploaded screenshots become ordinary HTTPS URLs inside that Markdown (for example `![...](https://user-images.githubusercontent.com/...)` or `https://github.com/user-attachments/assets/...`). The APIs **do not** attach raster bytes separately; anything that only forwards a short summary, a title, or HTML rendered without surfacing the underlying Markdown will **drop** those URLs unless the client parses them back out.

| Path | Markdown / URLs in agent context? | Raster bytes without a fetch? | Typical gap |
| --- | --- | --- | --- |
| `gh issue view <n> --repo <o>/<r> --json body,comments` | Yes — full `body` strings | No | Agents skip the separate **curl + open image** step. |
| `gh api repos/<o>/<r>/issues/<n>` (+ `/issues/comments`) | Yes — `body` field | No | Same as above. |
| `gh issue view` (TTY, no `--json`) | Partial — human-oriented render | No | Not a stable machine contract; prefer `--json`. |
| IDE / Cloud prompts with `github_pr_context` (or similar) | **Varies** — often title-heavy | No | Treat as **possibly missing** embedded URLs; re-fetch with `gh` JSON when screenshots matter. |
| GitHub web UI HTML | N/A for agents | N/A | Use APIs/CLI for reproducible text, not scraped HTML. |

**Smallest reliable fix in-repo**: keep using GitHub’s supported Markdown fields, explicitly **enumerate** image URLs, then **fetch** each URL (see below). Helper:

```bash
gh issue view <n> --repo <org>/<repo> --json body,comments \
  | python3 scripts/extract_github_issue_image_urls.py
```

That prints one URL per line (deduped, body then comments). The helper **allowlists** parsed hosts (`user-images.githubusercontent.com`, `private-user-images.githubusercontent.com`, `camo.githubusercontent.com`, and `github.com` paths under `/user-attachments/assets/` or `/assets-cdn/`) so Markdown cannot smuggle unrelated hosts into a blind `curl` loop. Feed each line to `curl -fsSL` (and `file --mime-type`, then open once) per the policy section. Many `user-images` / `private-user-images` links already carry a short-lived `?token=` query parameter; if `curl` returns **403** or **404**, refresh the issue JSON (or ask for a re-uploaded image) instead of embedding long-lived secrets in the repo. Treat script stdout as sensitive when those query tokens are present.

## GitHub issue screenshots

**Policy**: Whenever a `github.com` issue or PR is in scope, check whether the issue body, comments, or user message contains GitHub-hosted image URLs. If it does, finish image intake before planning or implementing from that ticket.

**Canonical checklist (Issues + PRs, all slash commands):** [`GITHUB_ISSUE_PR_IMAGE_INTAKE.md`](./GITHUB_ISSUE_PR_IMAGE_INTAKE.md).

**Default flow**:
1. Use one `gh issue view <n> --repo <org>/<repo> --json body,comments`.
2. Extract image URLs locally from the issue body, comment bodies, and the user message snippet (regex parse is fine; optionally pipe the JSON through `python3 scripts/extract_github_issue_image_urls.py` to list GitHub-hosted asset URLs).
3. Download each GitHub-hosted image with `curl -fsSL`.
4. Validate each file with `file --mime-type` and require `image/*`.
5. Open each validated image once and write one short **Visual evidence** summary.

The main image shape to support is `private-user-images.githubusercontent.com/...`.

If an image URL is expired, unavailable, or does not validate as `image/*`, stop and ask for a fresh or re-uploaded image.

### End-to-end check (manual)

1. Create (or use) an issue in a sandbox repo with one embedded upload or `![alt](https://user-images.githubusercontent.com/...)` line in the body or a comment.
2. Run the `gh issue view ... --json body,comments | python3 scripts/extract_github_issue_image_urls.py` pipeline; confirm the URL appears.
3. `curl -fsSL` the URL to a temp file, run `file --mime-type` on it, and confirm `image/*`.
4. Open the file once and confirm the agent could describe the screenshot (layout, text, colors) from pixels — not from URL text alone.

## Fail-closed recovery
If auth still fails:
1. Run `gh auth status -h github.com`
2. Confirm the repo/org is accessible with the current token/account
3. In Cursor Cloud, set `GH_TOKEN` or `GITHUB_TOKEN` in the cloud environment and retry `/auto`
4. Retry `/auto`
5. If access cannot be fixed immediately, paste the issue body and acceptance criteria directly so the workflow can proceed with explicit human-provided context
