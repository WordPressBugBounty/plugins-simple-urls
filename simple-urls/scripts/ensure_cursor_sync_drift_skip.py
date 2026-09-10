#!/usr/bin/env python3
"""Ensure consumer cursor-drift-check.yml skips sync / labeled PRs.

Propagate consumer sync PRs are fan-out of already-reviewed cursor-config master.
Drift-check must not block those merges (#442). Prefer label skips
(`cursor-sync`, `skip-drift-check`) so App tokens without `workflows`
permission never need to rewrite this YAML. Branch prefix remains a
belt-and-suspenders skip when the label is applied after the first run.
"""

from __future__ import annotations

import argparse
import re
import sys
from pathlib import Path

_JOB_HEADER_RE = re.compile(
    r"(^jobs:\r?\n  drift-check:\r?\n)",
    re.MULTILINE,
)
_SKIP_NEEDLE = "skip-drift-check"
_BRANCH_NEEDLE = "startsWith(github.head_ref, 'chore/cursor-sync-')"
_LABELED_NEEDLE = "labeled"
_SAME_REPO_GUARD = "github.event.pull_request.head.repo.full_name == github.repository"
_NON_PR_GUARD = "github.event_name != 'pull_request'"
_SKIP_BLOCK = (
    "    # Skip when labeled cursor-sync / skip-drift-check, or same-repo "
    "chore/cursor-sync-* (lassoanalytics/cursor-config#442).\n"
    "    if: ${{ github.event_name != 'pull_request' || ("
    "!contains(github.event.pull_request.labels.*.name, 'cursor-sync') && "
    "!contains(github.event.pull_request.labels.*.name, 'skip-drift-check') && "
    "!(startsWith(github.head_ref, 'chore/cursor-sync-') && "
    "github.event.pull_request.head.repo.full_name == github.repository)) }}\n"
)
# Re-run when label is added/removed after open (propagate labels after create).
_TYPES_LINE = (
    "    types: [opened, synchronize, reopened, labeled, unlabeled]\n"
)
_PR_BLOCK_RE = re.compile(
    r"(^on:\r?\n"
    r"  pull_request:\r?\n"
    r"(?:    .+\r?\n)*)",
    re.MULTILINE,
)


def ensure_pull_request_label_types(text: str) -> tuple[str, bool]:
    """Ensure pull_request fires on labeled/unlabeled. Idempotent."""
    if _LABELED_NEEDLE in text and "unlabeled" in text:
        # Already has labeled types somewhere under on: — good enough.
        if re.search(r"types:\s*\[[^\]]*labeled", text):
            return text, False
    match = _PR_BLOCK_RE.search(text)
    if not match:
        return text, False
    block = match.group(1)
    if re.search(r"^\s*types:\s*\[", block, re.MULTILINE):
        # Merge labeled/unlabeled into existing types list if missing.
        def _merge_types(m: re.Match[str]) -> str:
            inner = m.group(1)
            parts = [p.strip() for p in inner.split(",") if p.strip()]
            for t in ("labeled", "unlabeled"):
                if t not in parts:
                    parts.append(t)
            return f"types: [{', '.join(parts)}]"

        new_block = re.sub(
            r"types:\s*\[([^\]]*)\]",
            _merge_types,
            block,
            count=1,
        )
        if new_block == block:
            return text, False
        return text[: match.start()] + new_block + text[match.end() :], True
    # Insert types after pull_request: (or after branches block).
    # Prefer after last indented line under pull_request before blank/sibling.
    insert_at = match.end()
    updated = text[:insert_at] + _TYPES_LINE + text[insert_at:]
    return updated, True


def ensure_cursor_sync_drift_skip(text: str) -> tuple[str, bool]:
    """Return (updated_text, changed). Idempotent when skip already present."""
    text, types_changed = ensure_pull_request_label_types(text)
    if _SKIP_NEEDLE in text and _BRANCH_NEEDLE in text:
        return text, types_changed
    # Upgrade older branch-only skip to include labels.
    if _BRANCH_NEEDLE in text and _SKIP_NEEDLE not in text:
        # Replace existing if: line(s) under drift-check with the full block.
        old_if = re.compile(
            r"(^  drift-check:\r?\n)"
            r"(?:    #[^\n]*\r?\n)?"
            r"    if: \$\{\{[^}]+\}\}\r?\n",
            re.MULTILINE,
        )
        m = old_if.search(text)
        if m:
            updated = text[: m.start()] + m.group(1) + _SKIP_BLOCK + text[m.end() :]
            return updated, True
    match = _JOB_HEADER_RE.search(text)
    if not match:
        raise ValueError(
            "could not find 'jobs:\\n  drift-check:' header to insert sync-PR skip"
        )
    updated = text[: match.end()] + _SKIP_BLOCK + text[match.end() :]
    return updated, True


def patch_file(path: Path) -> bool:
    """Patch path in place. Return True if the file changed."""
    original = path.read_text(encoding="utf-8")
    updated, changed = ensure_cursor_sync_drift_skip(original)
    if changed:
        path.write_text(updated, encoding="utf-8")
    return changed


def run_self_test() -> int:
    sample = (
        "name: Cursor config - drift check\n"
        "\n"
        "on:\n"
        "  pull_request:\n"
        "    branches:\n"
        "      - master\n"
        "\n"
        "jobs:\n"
        "  drift-check:\n"
        "    runs-on: ubuntu-22.04\n"
        "    steps:\n"
        "      - uses: actions/checkout@v4\n"
    )
    updated, changed = ensure_cursor_sync_drift_skip(sample)
    assert changed, "expected first patch to change"
    assert _SKIP_NEEDLE in updated
    assert _BRANCH_NEEDLE in updated
    assert _SAME_REPO_GUARD in updated
    assert _NON_PR_GUARD in updated
    assert "cursor-sync" in updated
    assert "labeled" in updated
    assert "unlabeled" in updated
    assert "runs-on: ubuntu-22.04" in updated
    # if comes before runs-on
    job_idx = updated.index("drift-check:")
    if_idx = updated.index("startsWith(github.head_ref")
    runs_idx = updated.index("runs-on:")
    assert job_idx < if_idx < runs_idx

    again, changed2 = ensure_cursor_sync_drift_skip(updated)
    assert not changed2 and again == updated

    # Upgrade branch-only skip to labels.
    legacy = (
        "on:\n"
        "  pull_request:\n"
        "    branches:\n"
        "      - master\n"
        "\n"
        "jobs:\n"
        "  drift-check:\n"
        "    # old\n"
        "    if: ${{ github.event_name != 'pull_request' || "
        "!(startsWith(github.head_ref, 'chore/cursor-sync-') && "
        "github.event.pull_request.head.repo.full_name == github.repository) }}\n"
        "    runs-on: ubuntu-22.04\n"
    )
    upgraded, up_changed = ensure_cursor_sync_drift_skip(legacy)
    assert up_changed
    assert _SKIP_NEEDLE in upgraded

    try:
        ensure_cursor_sync_drift_skip("jobs:\n  other:\n    runs-on: ubuntu-22.04\n")
        raise AssertionError("expected ValueError for missing drift-check job")
    except ValueError:
        pass

    print("ensure_cursor_sync_drift_skip self-test ok")
    return 0


def main(argv: list[str] | None = None) -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--self-test", action="store_true")
    parser.add_argument(
        "path",
        nargs="?",
        type=Path,
        help="Path to consumer .github/workflows/cursor-drift-check.yml",
    )
    args = parser.parse_args(argv)
    if args.self_test:
        return run_self_test()
    if args.path is None:
        parser.error("path required (or use --self-test)")
        return 2
    if not args.path.is_file():
        print(f"skip: no file at {args.path}", file=sys.stderr)
        return 0
    try:
        changed = patch_file(args.path)
    except ValueError as exc:
        print(f"warn: {exc}", file=sys.stderr)
        return 1
    if changed:
        print(f"patched sync-PR drift skip into {args.path}")
    else:
        print(f"already present: sync-PR drift skip in {args.path}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
