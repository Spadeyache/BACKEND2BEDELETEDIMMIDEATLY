---
name: agent-transparency
description: Keep a live, human-readable record of what you are doing during any coding task. Use this whenever you are about to modify a codebase — planning a change, editing files, running commands, or finishing a task. Produces a single self-contained HTML report (agent-activity.html) that explains, in plain language and in visual form, what you intend to do, what you changed, and why. Activate it for any non-trivial edit so the human can follow your work without reading every diff.
---

# Agent Transparency

Your job is not only to make the change — it is to make the change **legible**. A human should be able to open one HTML file and understand, at a glance and in plain words, what you are doing and why, without scrolling your terminal or reading every diff.

You do this by maintaining one file, `agent-activity.html`, and updating it at fixed checkpoints. The file has two halves that must always stay in sync:

- **Verbal** — your reasoning in plain English (and Japanese if the human writes in Japanese): what you understood the task to be, the judgment calls you made, what you assumed, and what you are unsure about.
- **Visual** — the structured record: a step tracker, the file-change table with diff stats, commands run, and a decision timeline.

Both matter. A diff with no reasoning is noise; reasoning with no diff is a promise. Always produce both.

## How the file works

`template.html` is a static renderer plus a single data block. **You never edit the CSS or the renderer script.** You edit only the JSON inside:

```html
<script id="activity-data" type="application/json"> { ... } </script>
```

This is deliberate. Keeping all your edits inside one well-formed JSON object means every update is surgical, diffable, and impossible to break the layout with. If your edit makes the JSON invalid, the page shows a parse error instead of the report — so keep it valid (no trailing commas, escape quotes).

**Setup (once per task):** copy the bundled template (`${CLAUDE_SKILL_DIR}/template.html`, or `template.html` sitting next to this file) to the repo root as `agent-activity.html`, reset the data block to the starting state below, and add `agent-activity.html` to `.gitignore` unless the human asks to commit it.

## The checkpoint protocol

Update the data block at three moments. Do not update on every micro-action — checkpoints, not a keystroke log.

### Checkpoint 1 — PLAN (before touching any file)
Set `status: "planning"`. Fill in `plan.intent`, `plan.approach`, `plan.steps`, and — this is the important part — `plan.assumptions` and `plan.openQuestions`. Surfacing what you assumed and what you are unsure about *before* you act is the single highest-value thing this report does: it lets the human correct course cheaply. Add one `log` entry of kind `plan`. Then pause if the open questions are load-bearing; otherwise proceed.

### Checkpoint 2..N — AFTER EDITS (after each coherent batch of changes)
Set `status: "executing"`. For every file you created, edited, or deleted, add or update an entry in `changes` with honest `added`/`removed` counts, a one-line `summary` of *what*, and a `why` explaining the reasoning — not a restatement of the diff. Flag anything non-obvious with `risk`. Advance `steps` statuses. Append `log` entries (`decision`, `edit`, `command`). Record anything you ran in `commands`. Bump `checkpoint`. Update `updated` to the current time.

Re-run this checkpoint after each batch — roughly, each time you'd otherwise tell the human "ok, I did X." If you are running as a long autonomous session, treat every few related edits as a batch rather than waiting until the end.

### Final — DONE (or BLOCKED)
Set `status: "done"` (or `"blocked"`). Fill `recap.summary` (what actually got done, in plain words), `recap.review` (specific things the human should personally eyeball — the riskiest or least-certain parts), and `recap.notDone` (anything you deliberately left, and why). If blocked, the `recap.summary` says exactly what you need from the human.

## Data schema

```jsonc
{
  "task": "one-line statement of the goal, in the human's words",
  "status": "planning | executing | done | blocked",
  "updated": "ISO 8601 timestamp",
  "checkpoint": 0,                       // integer, bump each edit checkpoint

  "plan": {
    "intent": "What I understood you to want, and why I read it that way.",
    "approach": "How I intend to do it, and the main trade-off I picked.",
    "assumptions": ["Things I am taking for granted — each one a chance for you to correct me."],
    "openQuestions": ["Judgment calls I could not fully resolve. State what I did in the meantime."],
    "steps": [
      { "id": "s1", "title": "short step name", "status": "pending|active|done|skipped", "note": "optional one-liner" }
    ]
  },

  "changes": [
    {
      "file": "relative/path.ext",
      "action": "create | edit | delete",
      "added": 0, "removed": 0,          // honest line counts
      "risk": "none | low | medium | high",
      "summary": "WHAT changed, one line.",
      "why": "WHY this change exists / why this approach. Not a restatement of the diff."
    }
  ],

  "commands": [
    { "cmd": "the command", "purpose": "why I ran it", "result": "ok | fail | pending" }
  ],

  "log": [
    { "t": "HH:MM", "kind": "plan|decision|edit|command|note|blocked", "text": "one sentence" }
  ],

  "recap": {
    "summary": "Plain-language wrap-up. Empty until done.",
    "review": ["Specific things to eyeball — riskiest / least certain first."],
    "notDone": ["Deliberately left undone, with the reason."]
  }
}
```

### Starting state (paste into a fresh `agent-activity.html`)
```json
{
  "task": "<the human's request, one line>",
  "status": "planning",
  "updated": "<now, ISO 8601>",
  "checkpoint": 0,
  "plan": { "intent": "", "approach": "", "assumptions": [], "openQuestions": [], "steps": [] },
  "changes": [], "commands": [], "log": [],
  "recap": { "summary": "", "review": [], "notDone": [] }
}
```

## Writing the verbal half well

This is where most agent reports fail — they narrate mechanics instead of reasoning. Rules:

- **Explain *why*, never just *what*.** "Added a token-bucket limiter" is the `summary`. "Put it at the router boundary so future endpoints are covered too" is the `why`. The `why` is the part the human can't get from the diff.
- **Surface judgment, don't bury it.** Every time you pick one option over a viable alternative, that's a decision — log it, and if it was close, say what you didn't pick. Unstated choices are where surprises hide.
- **Assumptions are gifts to the reader.** Each assumption you write down is a cheap correction point. Prefer listing an assumption over silently committing to it.
- **Plain language, no jargon dump.** Write as if to a smart colleague who hasn't seen this code. Short sentences. The serif text in the report is *you talking to them* — keep it human.
- **Match the human's language.** If they wrote to you in Japanese, write `intent`, `why`, `recap`, and log text in Japanese.
- **Be honest about risk and uncertainty.** A `high` risk flag with a clear reason builds more trust than a clean-looking report that hides a shaky change.

## The visual half is automatic

You do not draw anything. The renderer turns your data into the step tracker, the diff-stat bars, the change table, the command list, and the timeline. Your only job on the visual side is **accurate data**: real line counts, correct `action` and `status` values, correct ordering of `log` and `steps`. Garbage in, misleading dashboard out.

## Guardrails

- Edit only the JSON data block. Never the CSS or renderer.
- Keep the JSON valid — invalid JSON replaces the whole report with a parse-error line.
- Don't log secrets, tokens, full file contents, or full diffs into the report. Summaries and reasons only.
- Don't pad the log with trivia. Checkpoints and decisions, not keystrokes.
- The report reflects reality. If a command failed, mark it `fail`; if a step was skipped, mark it `skipped` and say why in `recap.notDone`. Never make the report look tidier than the work.
