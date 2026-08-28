---
description: Auto-fix Pika POS formatting and lint issues, then report what could not be fixed
allowed-tools: Bash(npm run:*), Bash(composer run:*), Bash(vendor/bin/phpcbf:*), Bash(npx wp-scripts:*), Read, Edit
---

Apply the automatic fixers, then deal with the remainder by hand.

1. `composer run lint:fix` — PHPCBF fixes spacing, alignment and array syntax.
2. `npx wp-scripts lint-js src --fix`
3. `npx wp-scripts format src`
4. Re-run `npm run lint` and fix what is left by editing the files.

Coding-standards violations that survive PHPCBF are usually real: missing
escaping, a missing text domain, an unprepared query. Fix the underlying issue
rather than adding a `phpcs:ignore`. If an ignore genuinely is correct, the
comment must say why in terms someone can check.
