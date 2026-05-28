# Lasso Lite release (GitHub → WordPress.org)

Quick checklist for shipping a new **Lite** build from `lassoanalytics/simple-urls`.

**Repo:** [simple-urls](https://github.com/lassoanalytics/simple-urls) · default branch **`master`**

---

## What a “release” does

| Step | Mechanism |
|------|-----------|
| Version in code | [`plugin.php`](../../plugin.php): `Version:` header and `LASSO_LITE_VERSION` (both **+1** each release) |
| GitHub | Tag **`{N}`** (no `v` prefix) + **published** Release on `master` |
| CI | [`.github/workflows/main.yml`](../../.github/workflows/main.yml) on `release: published` |
| Artifact | Deploy to **WordPress.org** SVN via `10up/action-wordpress-plugin-deploy` |
| Slack | After deploy succeeds → channel `C024MRLQJBF` (release notes from GitHub Release body) |
| Customer updates | WordPress.org plugin update API (no Lasso license gate) |

**RC (pre-release):** every push to `master` runs [`.github/workflows/release-rc.yml`](../../.github/workflows/release-rc.yml) → RC zip → S3 (`S3_BUCKET_LASSO_LITE_RC` / public bucket).

---

## Pre-release checklist

1. **Merge to `master`** — all feature/fix PRs for this release are merged; CI green.
2. **Increase both version fields** in [`plugin.php`](../../plugin.php) (see table below).
3. **Open a PR to `master`** from branch `release/{N}` with the version bump. Include **Proposed GitHub Release notes** in the PR body (see below). Or run **`/git-release-pr`** in Cursor (open this repo as the workspace root).
4. **Draft release notes** from the last shipped tag to `master` (see [Draft release notes](#draft-release-notes)).
5. **Merge the release PR** — when [`.github/workflows/release-draft-on-merge.yml`](../../.github/workflows/release-draft-on-merge.yml) is on `master`, Actions opens a **draft** GitHub Release + tag `{N}` from the PR notes.
6. **Review draft** on GitHub → **Publish release** when ready (this triggers WordPress.org deploy; drafts do not).
7. **(Optional)** QA an RC zip from the latest `master` push before the formal release.

**First-time setup:** GitHub does not run a workflow that is **added in the same PR** that merges. Merge the workflow file to `master` once, then release PRs get drafts automatically. If a merge did not create a draft, use **Actions → Draft GitHub Release (release PR merge) → Run workflow**.

### Version constants in `plugin.php` (increase every release)

Update **both** in the version block near the top of the file (same section as the “WE SHOULD UPDATE THE VERSION NUMBER” comment):

| Line (approx.) | Constant / field | What to do | Example (149 → 150) |
|----------------|------------------|------------|---------------------|
| ~8 | Plugin header `* Version:` | **+1**; must match tag `{N}` and `LASSO_LITE_VERSION` | `* Version: 149` → `* Version: 150` |
| ~27 | `LASSO_LITE_VERSION` | **+1**; same integer as header and GitHub tag | `'149'` → `'150'` |

```php
 * Version: 150          // line ~8 — increase

define( 'LASSO_LITE_VERSION', '150' );   // line ~27 — increase (match header)
```

Unlike Pro, Lite has **no** `LASSO_RC_SUFFIX` — DB migrations are gated by `lasso_version` / `LASSO_LITE_VERSION` via [`classes/class-update-db.php`](../../classes/class-update-db.php).

### Version pitfalls

- Header `Version:` must be the **integer release number** (e.g. `150`), not semver like `1.50.0`.
- Tag name **`150`** (not `v150`) and header `150` must refer to the **same** release number (historical Lite convention).
- Tag must point at the commit that contains the version bump on `master`.

---

## Draft release notes

After the version bump, summarize what shipped since the **previous tag** (e.g. `149` → `150`).

```bash
LAST=149
NEXT=150   # must match plugin.php Version / LASSO_LITE_VERSION

git fetch origin --tags
git log ${LAST}..origin/master --merges --oneline
git log ${LAST}..origin/master --no-merges --oneline --grep='^#'

echo "https://github.com/lassoanalytics/simple-urls/compare/${LAST}...master"
```

**Format** (match prior releases, e.g. 149):

```markdown
Improvements
- ...

Fixes
- ...
```

Group by user impact, not by PR number. Link issues in the GitHub Release body if helpful; keep bullets short.

**Tone (PR, GitHub Release, and Slack):**

Release notes are copied into **Slack** after deploy — treat them as **customer-facing**, not internal engineering logs.

- **User impact first** — what changed for site owners and editors, not how it was implemented.
- **Summarize** security, data-integrity, and reliability work in plain language (e.g. “stricter permission checks on settings actions”, “link scan no longer drops links from empty recipe content”).
- **Do not include sensitive or internal detail**, especially:
  - Database: table/column names, migration SQL, schema mechanics, `lasso_version` / RC gating details
  - Security: exploit paths, CVE-style detail, unpatched vulnerability descriptions, raw auth/license failure messages
  - Ops/debug: Sentry/stack traces, backend error strings, cron internals, data-loss root-cause postmortems
- **OK:** “Improved Amazon account connect flow in Lite settings.” **Avoid:** “`md5_link_slug` column backfill” or “fixed SQL injection in `lasso_ajax_settings`.”

Reuse the same summarized bullets in the **release PR** (**Proposed GitHub Release notes**), the **published GitHub Release** body, and what Slack will show.

### Release PR body template

```markdown
## Summary
Bump Lite to **{N}** for release.

> **Release notes:** customer-facing; copied to GitHub Release and Slack. No DB/schema/SQL, security exploit detail, or internal errors.

## Proposed GitHub Release notes

Improvements
- ...

Fixes
- ...
```

The draft workflow extracts everything under **`## Proposed GitHub Release notes`** until the next `##` heading.

---

## Publish on GitHub

### Automated draft (recommended)

When `release-draft-on-merge.yml` is on `master`, merging a `release/*` PR:

1. Creates tag **`{N}`** and a **draft** release (notes from **Proposed GitHub Release notes**).
2. Does **not** deploy to WordPress.org until you click **Publish release**.

Review: **GitHub → Releases → Drafts** → edit title/body if needed → **Publish release**.

### Release naming (historical pattern)

- **Tag:** `{N}` (e.g. `150`) — **no** `v` prefix
- **Target:** `master`
- **Title:** `{N} - Mon DD, YYYY` (e.g. `150 - May 28, 2026`)
- **Body:** `Improvements` / `Fixes` sections

**Important:** Only **Publish release** triggers WordPress.org deploy (`main.yml` listens for `types: [published]` only).

### CLI example (draft)

A **draft** release needs a matching **git tag** on the release commit first.

```bash
cd /path/to/simple-urls
git checkout master && git pull

grep -E 'Version:|LASSO_LITE_VERSION' plugin.php

N=150
TAG="${N}"
SHA=$(git rev-parse master)

git tag -a "$TAG" "$SHA" -m "${N} - $(date -u '+%b %d, %Y')"
git push origin "$TAG"

gh release create "$TAG" \
  --draft \
  --title "${N} - $(date -u '+%b %d, %Y')" \
  --notes "$(cat <<'EOF'
Improvements
- ...

Fixes
- ...
EOF
)"
```

Or: GitHub → **Releases** → **Draft a new release** → choose tag `{N}` → notes → save as draft → **Publish** when ready.

Or run **`/git-draft-release`** in Cursor after merge if the workflow did not create a draft.

### Slack (after WordPress.org deploy succeeds)

When you click **Publish release**, `main.yml` posts to Slack after the SVN deploy step. The message includes the **GitHub Release body verbatim** — follow [Tone (PR, GitHub Release, and Slack)](#tone-pr-github-release-and-slack) so Slack does not expose DB, security, or internal diagnostics.

| Config | Value |
|--------|--------|
| **Channel ID** | `C024MRLQJBF` (in workflow) |
| **Secret (preferred)** | `LASSO_LITE_RELEASE_SLACK_BOT_TOKEN` — bot with `chat:write` invited to that channel |
| **Secret (fallback)** | `LASSO_LITE_RELEASE_SLACK_WEBHOOK_URL` — Incoming Webhook if bot post fails |

Add these secrets under **simple-urls** repo → Settings → Secrets. You can use the same bot token / webhook as Pro (`LASSO_PRO_RELEASE_SLACK_*`) if they target the same channel.

Invite the bot to channel `C024MRLQJBF` before first publish.

**Example message:**

```text
✅ Lasso Lite released: `150` — 150 - May 28, 2026
• Deployed to WordPress.org (simple-urls)
• https://github.com/lassoanalytics/simple-urls/releases/tag/150
• https://wordpress.org/plugins/simple-urls/

*Release notes*
Improvements
- ...
```

Notes longer than ~3200 characters are truncated in Slack. If secrets are missing, the release still ships; the workflow logs a skip.

---

## Post-publish verification

```bash
gh run list --workflow "Deploy simple-urls to WordPress.org" --limit 3
gh run watch <RUN_ID> --exit-status
```

Expect:

- Workflow **success**
- New version visible on [wordpress.org/plugins/simple-urls](https://wordpress.org/plugins/simple-urls/) (may take a few minutes)

### Smoke test

- WP admin on a test site: **Dashboard → Updates** shows the new Lite version
- Upgrade; confirm any DB migrations if schema changed
- Spot-check a link redirect and one admin screen

---

## Reference

| File | Role |
|------|------|
| [`plugin.php`](../../plugin.php) | `Version:`, `LASSO_LITE_VERSION` |
| [`.github/workflows/main.yml`](../../.github/workflows/main.yml) | WordPress.org deploy (on **published** release) |
| [`.github/workflows/release-draft-on-merge.yml`](../../.github/workflows/release-draft-on-merge.yml) | Draft release + tag after `release/*` PR merge |
| [`.github/workflows/release-rc.yml`](../../.github/workflows/release-rc.yml) | RC zip on `master` push |
| [`classes/class-update-db.php`](../../classes/class-update-db.php) | Version-gated migrations |

Recent releases: `gh release list --limit 5`

**Pro equivalent:** [LASSO_PRO_RELEASE.md](https://github.com/lassoanalytics/affiliate-plugin/blob/master/docs/RUNBOOKS/LASSO_PRO_RELEASE.md) (S3 + `v{N}` tags + `LASSO_RC_SUFFIX`).
