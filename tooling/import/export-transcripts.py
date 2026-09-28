#!/usr/bin/env python3
"""
Exporte les conversations Claude Code en Markdown lisible et expurgé.

Claude Code stocke chaque session dans
    ~/.claude/projects/<chemin-du-projet-encodé>/<uuid-session>.jsonl

Ces fichiers contiennent bien plus que la conversation : pièces jointes,
contenus de fichiers lus, sorties de commandes, métadonnées de facturation.
Sur une session type, moins de la moitié des lignes portent un message, et le
reste peut contenir des secrets lus au passage.

Ce script ne garde que les tours utilisateur et assistant, résume les appels
d'outils en une ligne, jette les résultats d'outils, et passe une couche de
masquage sur les motifs de secrets.

Usage :
    python3 export-transcripts.py --out ./export
    python3 export-transcripts.py --projects ~/.claude/projects --filter boulangerie
    python3 export-transcripts.py --out ./export --keep-tool-results   # déconseillé
"""

from __future__ import annotations

import argparse
import json
import re
import sys
from pathlib import Path

# --- Masquage ---------------------------------------------------------------
# Mieux vaut masquer trop que laisser passer une clé de production.
REDACTIONS: list[tuple[re.Pattern[str], str]] = [
    (re.compile(r"\b(sk|pk)_(live|test)_[A-Za-z0-9]{10,}"), "[CLÉ-STRIPE-MASQUÉE]"),
    (re.compile(r"\bsk-ant-[A-Za-z0-9_-]{10,}"), "[CLÉ-API-MASQUÉE]"),
    (re.compile(r"\bAKIA[0-9A-Z]{16}\b"), "[CLÉ-AWS-MASQUÉE]"),
    (re.compile(r"\bghp_[A-Za-z0-9]{20,}"), "[TOKEN-GITHUB-MASQUÉ]"),
    (re.compile(r"\beyJ[A-Za-z0-9_-]{10,}\.[A-Za-z0-9_-]{10,}\.[A-Za-z0-9_-]{10,}"), "[JWT-MASQUÉ]"),
    (re.compile(r"-----BEGIN [A-Z ]*PRIVATE KEY-----.*?-----END [A-Z ]*PRIVATE KEY-----", re.S), "[CLÉ-PRIVÉE-MASQUÉE]"),
    # Affectations de secrets : on garde le nom de la variable, pas la valeur.
    (re.compile(r"(?i)\b(DB_PASSWORD|DB_USER|DB_NAME|AUTH_KEY|AUTH_SALT|SECURE_AUTH_KEY|NONCE_KEY|LOGGED_IN_KEY)\b\s*[=:,]\s*['\"]?[^'\"\n,)]{4,}"), r"\1 = [MASQUÉ]"),
    (re.compile(r"(?i)\b(password|passwd|pwd|secret|api[_-]?key|token|bearer)\b\s*[=:]\s*['\"]?[^'\"\s]{6,}"), r"\1=[MASQUÉ]"),
    (re.compile(r"\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b"), "[EMAIL-MASQUÉ]"),
]

MAX_TEXT = 4000  # au-delà, un tour est tronqué : le contenu utile est au début


def redact(text: str) -> tuple[str, int]:
    """Applique les masquages. Renvoie (texte, nombre de substitutions)."""
    count = 0
    for pattern, replacement in REDACTIONS:
        text, n = pattern.subn(replacement, text)
        count += n
    return text, count


def blocks_to_markdown(content: object) -> tuple[list[str], list[str]]:
    """Extrait le texte et les appels d'outils d'un contenu de message."""
    texts: list[str] = []
    tools: list[str] = []

    if isinstance(content, str):
        return ([content], []) if content.strip() else ([], [])

    if not isinstance(content, list):
        return [], []

    for block in content:
        if not isinstance(block, dict):
            continue
        kind = block.get("type")
        if kind == "text":
            value = block.get("text") or ""
            if value.strip():
                texts.append(value)
        elif kind == "tool_use":
            name = block.get("name", "?")
            params = block.get("input") or {}
            # Une ligne par outil : ce qui compte est l'enchaînement, pas le détail.
            hint = ""
            if isinstance(params, dict):
                for key in ("description", "file_path", "path", "pattern", "command", "skill"):
                    if params.get(key):
                        hint = str(params[key]).splitlines()[0][:120]
                        break
            tools.append(f"{name}{f' — {hint}' if hint else ''}")
        # tool_result, thinking, image : volontairement ignorés.

    return texts, tools


def export_session(path: Path, keep_tool_results: bool) -> tuple[str, dict] | None:
    """Convertit un .jsonl de session en Markdown. None si rien d'exploitable."""
    lines: list[str] = []
    stats = {"tours": 0, "outils": 0, "masquages": 0, "tronques": 0}
    meta: dict[str, str] = {}

    for raw in path.read_text(encoding="utf-8", errors="replace").splitlines():
        try:
            entry = json.loads(raw)
        except json.JSONDecodeError:
            continue

        if entry.get("type") not in ("user", "assistant"):
            continue
        # Les sous-agents produisent leurs propres tours : bruit pour une relecture.
        if entry.get("isSidechain"):
            continue

        message = entry.get("message")
        if not isinstance(message, dict):
            continue

        for key in ("cwd", "gitBranch", "version"):
            if entry.get(key) and key not in meta:
                meta[key] = str(entry[key])

        texts, tools = blocks_to_markdown(message.get("content"))
        if not texts and not tools:
            continue

        role = "Toi" if entry.get("type") == "user" else "Claude"
        when = (entry.get("timestamp") or "")[:19].replace("T", " ")

        body = "\n\n".join(texts).strip()
        if len(body) > MAX_TEXT:
            body = body[:MAX_TEXT] + f"\n\n_[…tronqué, {len(body) - MAX_TEXT} caractères de plus]_"
            stats["tronques"] += 1

        body, redacted = redact(body)
        stats["masquages"] += redacted

        lines.append(f"### {role} · {when}\n")
        if body:
            lines.append(body + "\n")
        if tools and not keep_tool_results:
            listing = "\n".join(f"- `{t}`" for t in tools)
            lines.append(f"<details><summary>{len(tools)} appel(s) d'outil</summary>\n\n{listing}\n\n</details>\n")
            stats["outils"] += len(tools)
        lines.append("")
        stats["tours"] += 1

    if stats["tours"] == 0:
        return None

    header = [
        f"# Session {path.stem}",
        "",
        f"- Projet : `{meta.get('cwd', 'inconnu')}`",
        f"- Branche : `{meta.get('gitBranch', '—')}`",
        f"- Tours : {stats['tours']} · outils : {stats['outils']} · masquages : {stats['masquages']}",
        "",
        "> Export expurgé. Les résultats d'outils et pièces jointes ne sont pas inclus.",
        "",
        "---",
        "",
    ]
    return "\n".join(header + lines), stats


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    parser.add_argument("--projects", default=str(Path.home() / ".claude" / "projects"),
                        help="dossier des projets Claude Code (défaut : ~/.claude/projects)")
    parser.add_argument("--out", default="./export-transcripts", help="dossier de sortie")
    parser.add_argument("--filter", default="", help="ne garder que les projets dont le nom contient ce texte")
    parser.add_argument("--keep-tool-results", action="store_true",
                        help="conserver le détail des outils (déconseillé : volumineux et risqué)")
    args = parser.parse_args()

    projects = Path(args.projects).expanduser()
    if not projects.is_dir():
        print(f"Dossier introuvable : {projects}", file=sys.stderr)
        print("Sur Windows, essaie : --projects %USERPROFILE%\\.claude\\projects", file=sys.stderr)
        return 1

    out = Path(args.out).expanduser()
    out.mkdir(parents=True, exist_ok=True)

    total = {"sessions": 0, "tours": 0, "masquages": 0}
    index: list[str] = ["# Sessions Claude Code exportées", ""]

    for project_dir in sorted(p for p in projects.iterdir() if p.is_dir()):
        if args.filter and args.filter.lower() not in project_dir.name.lower():
            continue

        sessions = sorted(project_dir.glob("*.jsonl"))
        if not sessions:
            continue

        project_out = out / project_dir.name.lstrip("-")
        project_out.mkdir(parents=True, exist_ok=True)
        index.append(f"## {project_dir.name}")
        index.append("")

        for session in sessions:
            result = export_session(session, args.keep_tool_results)
            if result is None:
                continue
            markdown, stats = result
            target = project_out / f"{session.stem}.md"
            target.write_text(markdown, encoding="utf-8")

            total["sessions"] += 1
            total["tours"] += stats["tours"]
            total["masquages"] += stats["masquages"]
            index.append(f"- [{session.stem}]({project_out.name}/{target.name}) — "
                         f"{stats['tours']} tours, {stats['masquages']} masquages")
            print(f"  ✓ {target.relative_to(out)}  ({stats['tours']} tours, "
                  f"{stats['masquages']} masquages, {target.stat().st_size // 1024} Ko)")
        index.append("")

    (out / "INDEX.md").write_text("\n".join(index), encoding="utf-8")

    print()
    print(f"{total['sessions']} session(s), {total['tours']} tours, "
          f"{total['masquages']} masquage(s) → {out}")
    print()
    print("Relis l'export avant de le partager. Le masquage attrape les motifs")
    print("connus, pas un secret écrit en toutes lettres dans une phrase :")
    print(f"  grep -rin 'mot de passe\\|identifiant\\|clé' {out} | head -30")
    return 0


if __name__ == "__main__":
    sys.exit(main())
