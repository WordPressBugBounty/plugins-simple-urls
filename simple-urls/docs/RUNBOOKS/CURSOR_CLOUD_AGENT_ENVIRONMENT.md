# Cursor Cloud Agent environments — provisioning and recovery

## Purpose

Use this runbook when Cursor Cloud Agents stall on **“Waiting for environment”** (or similar) for a GitHub-backed repo—for example `lassoanalytics/affiliate-plugin`—whether the entry point is Slack (`/issue`), the [Agents](https://cursor.com/agents/) UI, or IDE Cloud runs.

**Scope:** Cursor Cloud **environment** configuration and operational recovery. It does **not** change production application behavior in the target repo.

## Quick links

- [Cloud Agents → Environments](https://cursor.com/dashboard/cloud-agents#environments)
- GitHub auth for agents in cloud: [CURSOR_AUTO.md](./CURSOR_AUTO.md) (`GH_TOKEN` / `GITHUB_TOKEN`)

## Symptom

- Agent or `/issue` flow never leaves provisioning; UI shows a long-lived **waiting for environment** state.
- Same repo works locally or worked previously; regression points to **cloud environment** drift or a failed image/bootstrap.

## Likely root causes (triage order)

Work top-down; more than one can apply.

| Area | What to check |
| --- | --- |
| **Environment lifecycle** | Environment deleted, renamed, or pointed at the wrong GitHub repo/branch; duplicate envs for the same repo. |
| **Bootstrap failure** | `.cursor/Dockerfile`, `.cursor/setup.sh`, or install steps exit non-zero, hang on prompts, or time out during image build or first boot. |
| **GitHub access** | Cloud agent cannot clone or fetch the repo (app/installation, SSO, token scopes). Symptom often appears as stuck provisioning rather than a clear PR error. |
| **Resource / quota** | Org or team limits on concurrent environments; retry after other sessions finish. |
| **Secrets** | Workflows that need `GH_TOKEN` / `GITHUB_TOKEN` in the cloud environment per [CURSOR_AUTO.md](./CURSOR_AUTO.md); missing token can break issue/PR steps after boot. |

## Remediation (standard)

1. Open [Environments](https://cursor.com/dashboard/cloud-agents#environments) and locate the environment tied to the affected repo (e.g. **affiliate-plugin** → `lassoanalytics/affiliate-plugin`).
2. Confirm **repository** and **default branch** match what the team uses for agents (typically the repo default branch unless you intentionally pin another).
3. If the UI offers **Rebuild** / **Recreate** / **Reset**, use it for a clean image and bootstrap (prefer this over repeatedly starting new agent sessions on a broken env).
4. After rebuild, open **logs** or build output for the environment (if exposed in the dashboard) and confirm Dockerfile/setup completed without errors.
5. Set or refresh **cloud environment variables** required by your workflow (at minimum GitHub API access for `/auto`-style flows: see [CURSOR_AUTO.md](./CURSOR_AUTO.md)).
6. Retry a **minimal** agent chat in the Agents UI, then `/issue` from Slack (or the other entry point that failed).

## Validation (acceptance checklist)

Use this to confirm recovery for a given repo (e.g. `affiliate-plugin`):

- [ ] `/issue` (or equivalent) for that repo **completes provisioning** and proceeds past “waiting for environment.”
- [ ] A normal **Agent chat** starts and runs for that repo from both **Slack** and **Agents UI** (or whichever channels your team uses).
- [ ] **No secrets** were pasted into tickets or committed; tokens stay in Cursor Cloud / approved secret stores only.

## After an incident

- Record **what fixed it** (e.g. “recreated environment”, “fixed `setup.sh` non-interactive apt”, “restored GH_TOKEN in cloud env”) in the issue or internal notes.
- If bootstrap scripts in `.cursor/` were the root cause, fix them in the **target repo** and let [cursor-propagate](https://github.com/lassoanalytics/cursor-config/blob/master/.github/workflows/cursor-propagate.yml) bring shared docs forward as usual.

## Recorded incidents

### affiliate-plugin — [cursor-config#47](https://github.com/lassoanalytics/cursor-config/issues/47)

- **Symptom:** `/issue` and Agent chat stalled on **waiting for environment** for `lassoanalytics/affiliate-plugin` (Slack and Agents UI).
- **In-repo remediation:** Follow **Remediation (standard)** and **Validation** above; prefer **Rebuild/Recreate** for the Cloud environment bound to that repo.
- **Root cause:** Confirm from environment/build logs in the dashboard after recovery, then add a one-line note under **Likely root causes** (which row applied) so the next incident is faster.

## Related

- Canonical consumer list: `repos.json` in `lassoanalytics/cursor-config` (includes `lassoanalytics/affiliate-plugin`).
- Shared Dev Agent verification: `docs/TESTING_MATRIX.md`.
