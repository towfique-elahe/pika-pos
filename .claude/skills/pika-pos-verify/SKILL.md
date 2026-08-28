---
name: pika-pos-verify
description: How to actually verify a Pika POS change on this local WordPress Studio site — lint, static analysis, build, and driving the site in a real browser. Use before claiming any Pika POS change works.
---

# Verifying a Pika POS change

Run these from the plugin directory unless stated. `wp` commands run from the
**site root** and are always prefixed with `studio`.

## 1. Static checks (fast, always)

```bash
composer run lint      # PHPCS, WordPress Coding Standards
composer run analyse   # PHPStan level 5, WordPress + WooCommerce + WP-CLI stubs
npm run lint:js        # ESLint
```

`composer run lint:fix` fixes most spacing and alignment complaints on its own.
What survives PHPCBF is usually real — missing escaping, a missing text domain,
an unprepared query. Fix the cause rather than adding a `phpcs:ignore`.

## 2. Build, if any JS or SCSS changed

```bash
npm run build          # writes build/ from src/index.js
npm run start          # watch mode for a tight edit loop
```

## 3. Confirm the plugin still loads

```bash
cd /Users/towfiqueelahe/Studio/icby-polki
studio wp plugin list --status=active --format=csv
studio wp eval 'echo defined( "PIKA_POS_VERSION" ) ? PIKA_POS_VERSION : "not loaded";'
```

A fatal error during load shows up here before it shows up in a browser.

## 4. Tests, once there are any

```bash
composer run test      # PHPUnit; boots the real site via tests/php/bootstrap.php
npm run test:e2e       # Playwright against http://localhost:8883
```

These are integration tests against the live local database. Anything a test
creates, it must delete in `tearDown()`.

## 5. Drive it in a browser

Use the **playwright** MCP server against http://localhost:8883. Log in at
`/wp-admin/` as `admin` / `admin`, or use the auto-login URL from
`studio status`. Check the browser console for errors before declaring success,
and screenshot the change.

## When things fail

| Symptom | Cause |
|---|---|
| A new front-end route 404s | Rewrite rules stale → `studio wp rewrite flush` |
| PHP fatal on a page | `cat ../../debug.log` from the plugin directory |
| REST route returns 401/403 | Logged-out session, or a capability the user lacks |
| Order created but stock unchanged | Something bypassed the single order-writing path |
| `studio: command not found` | Enable Studio CLI in the Studio desktop app |

Enable logging if `debug.log` does not exist:

```bash
cd /Users/towfiqueelahe/Studio/icby-polki && studio config set --debug-log
```

Never report a change as working on the strength of the code reading correctly.
