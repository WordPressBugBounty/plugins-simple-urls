---
name: run-local-ci-parity
description: Run the smallest local checks that match the repo’s CI “truth” (workflows + AGENTS docs). Never deploy; Terraform apply only when explicitly requested; prefer fast, targeted verification.
---

# Run local CI-parity checks (repo-agnostic)

Use this when the user says things like:
- “Run the same checks CI runs”
- “What’s the smallest local verification for this change?”
- “Make sure this will pass CI before I push”

## Hard safety rules
- **Never deploy** (no `kubectl rollout restart`, no release publish, no remote writes).
- **Terraform**: never run `terraform apply` unless the user explicitly asks; default to `fmt/validate/plan` only.
- **Keep it minimal**: prefer targeted/unit tests over full end-to-end suites unless the change surface demands it.
- **Don’t invent commands**: prefer commands documented in-repo (`AGENTS.md`, runbooks) or encoded in `.github/workflows/`.

## Workflow (decision-first, then commands)

### 1) Discover the repo’s “truth”
In priority order:
1) `AGENTS.md` (or `docs/TESTING*.md`, runbooks) if present.

2) `.github/workflows/*.yml` / `*.yaml` jobs with test/build steps.

3) Project files (`package.json`, `pyproject.toml`, `requirements.txt`, `composer.json`, Terraform files).

Goal: identify the **smallest** command set CI expects for the affected surface.

### 2) Pick the smallest verification set that matches the change surface
Examples of “smallest correct” defaults (override if repo docs/workflows say otherwise):

#### Python
- Prefer `pytest` if CI uses it; otherwise use unittest.

```bash
pytest -q
# or
python -m unittest discover tests/
```

#### Node
- Prefer repo scripts.

```bash
npm test
# or
npm run test
```

If the repo has acceptance/e2e (Playwright/Cypress), run it **only** when the change touches routes/UI/auth/shared client behavior, or when CI always runs it.

#### PHP
- Prefer Composer scripts or CI commands.

```bash
composer test || true
vendor/bin/phpunit
vendor/bin/phpcs
```

#### Terraform
Safe-by-default local checks:

```bash
terraform fmt -check
terraform init -backend=false
terraform validate
```

Run `terraform plan` only when helpful and safe (still read-only, but can be slow); never apply unless explicitly requested.

### 3) Handle required services (only if repo encodes them)
If CI requires Postgres/Redis/etc:

- Prefer repo-provided scripts (`runLocal.sh`, `docker-compose.yml`, `Makefile`, `scripts/*`).

- If the repo does not provide a deterministic local bootstrap, stop and report what CI expects (service + version) and what’s missing locally.

### 4) Report results
Always output:

- **Commands run** (exact)
- **Result** (pass/fail)
- **If fail**: first actionable error + smallest next step

