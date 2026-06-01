# agent-transparency

A drop-in skill that makes an AI coding agent keep a **single, self-contained HTML report** of what it's doing — updated at checkpoints, with a **verbal** half (plain-language reasoning, assumptions, open questions, decision log) and a **visual** half (step tracker, file-change table with diff stats, commands run).

No server, no build step, no dependencies. One HTML file you open in a browser.

## Files
- `SKILL.md` — the instruction set the agent follows (the rules).
- `template.html` — the renderer + a seed data block. Open it in a browser to see the format. The agent copies this to `agent-activity.html` and edits only the JSON data block inside it.

## How it behaves
The agent updates the report at three checkpoints:
1. **Plan** — before editing anything (intent, approach, assumptions, open questions).
2. **After edits** — after each batch of changes (what changed + *why*, diff stats, commands, decisions).
3. **Done / blocked** — a recap plus a "please eyeball this" review list.

All updates are surgical: the agent only ever edits one JSON block, never the layout.

## Wiring it into your agent
- **Claude Code** — drop the folder in `.claude/skills/agent-transparency/` (project) or `~/.claude/skills/` (global). It triggers on coding tasks via the `description`. Or reference it from `CLAUDE.md`: "For any code change, follow `.claude/skills/agent-transparency/SKILL.md`."
- **Cursor / Windsurf / generic** — paste the contents of `SKILL.md` into your rules file (`.cursorrules`, `AGENTS.md`, system prompt). Make `template.html` available in the repo.
- **Any agent** — the rules are tool-agnostic. The only requirements are that the agent can write files and edit a JSON block.

## Customizing
- **Look:** edit the `:root` CSS variables in `template.html`. The palette is a light, warm canvas with three named colors: `--papaya` (McLaren orange — the attention accent, reserved for what needs a human), `--green` (dark green — settled/done), and `--sakura` (the soft verbal/FYI register). Change those three to re-skin everything.
- **Schema:** add fields if you want (e.g. `tests`, `cost`) — but then add matching render logic in the script, and update the schema section of `SKILL.md` so the agent knows about them.
- **Sharing:** since it's a single static file, you can commit it, attach it to a PR, or host it anywhere.
