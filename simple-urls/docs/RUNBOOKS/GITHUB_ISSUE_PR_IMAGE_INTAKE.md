# GitHub Issue / PR screenshot intake

Use this whenever a **`github.com` Issue or PR URL** is in scope for a slash command that ingests that URL (`/plan`, `/think`, `/fix`, `/tdd`, `/code`, `/auto`, `/hub-ai-ui` when the link is an Issue or PR, etc.). **Finish image intake before** planning, implementing, or stating conclusions that depend on screenshots.

Also follow **`.cursor/skills/github-issue-images/SKILL.md`**. Fail-closed recovery: **`docs/RUNBOOKS/CURSOR_AUTO.md`** (GitHub issue screenshots).

## 1) Collect markdown

Merge text from, in order:

- Issue or PR **body**
- Each **comment** `body` (review threads are not in `comments`; if review screenshots matter, fetch review bodies separately with `gh api` / `gh pr view` review options when needed)
- A short **user message** snippet that contains the GitHub URL or pasted excerpt

If you already ran `gh issue view` / `gh pr view` with `body,comments` in the JSON, **reuse that JSON**—do not re-fetch for images only.

## 2) Discover asset URLs

- Parse markdown/HTML locally for GitHub-hosted URLs, **or**
- Pipe the same JSON to:

  ```bash
  python3 scripts/extract_github_issue_image_urls.py
  ```

  (stdin = one JSON object with `body` and `comments` shaped like `gh issue view --json body,comments`.)

If **no** URLs pass the script allowlist, **stop** (nothing to download).

## 3) Download, validate, open

For **each** URL:

1. Download, e.g. `curl -fsSL -o /tmp/gh-asset.bin "<url>"`.
2. If `curl` returns **403** or **404**, retry once with GitHub auth, e.g.  
   `curl -fsSL -H "Authorization: Bearer $(gh auth token)" -o /tmp/gh-asset.bin "<url>"`  
   (some `https://github.com/user-attachments/assets/...` assets behave this way.)
3. Run `file --mime-type` on the download; require **`image/*`**.
4. **Open each validated image once** (viewer or agent image tool); write a compact **Visual evidence** summary (bullets) into the **Issue Context Cache**.

If a required screenshot is expired, blocked, or not an image: **stop fail-closed** and ask for a re-upload or pasted image (see **CURSOR_AUTO.md**).

## 4) PR-specific note

`gh pr view <n> --repo <org>/<r> --json body,comments` matches the extractor’s expected shape for **top-level** PR description and issue-style comments. Inline review images may need **`gh pr view --json comments` / review APIs** depending on where the team pasted screenshots.
