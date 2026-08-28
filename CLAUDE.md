# Pika POS — Claude Working Notes

A WooCommerce Point-of-Sale plugin. **Currently a skeleton** — [pika-pos.php](pika-pos.php)
boots, checks WooCommerce and declares HPOS compatibility; `includes/` is empty
and nothing is implemented yet.

## Environment (WordPress Studio)

This plugin lives inside a local WordPress Studio site. **Every `wp` command must
be prefixed with `studio` and run from the site root**, not from this plugin
directory:

```bash
cd /Users/towfiqueelahe/Studio/icby-polki
studio wp plugin list
```

| | |
|---|---|
| Site URL | http://localhost:8883/ |
| Admin | http://localhost:8883/wp-admin/ — `admin` / `admin` |
| WordPress | 7.1 |
| PHP runtime | 8.3 (native); CLI `php` is 8.5 |
| WooCommerce | 11.0.1 |
| Database | **SQLite** — `wp-content/database/.ht.sqlite` |
| Site root | `/Users/towfiqueelahe/Studio/icby-polki` |

The database is SQLite, not MySQL. Raw `$wpdb->query()` with MySQL-only syntax
(`ON DUPLICATE KEY UPDATE`, `SQL_CALC_FOUND_ROWS`) fails here even though it
works on production MySQL. Prefer `$wpdb->insert`, `$wpdb->update`,
`$wpdb->replace` and WooCommerce CRUD APIs.

If `studio` is not found, tell the user to enable **Settings → General → Studio
CLI for terminal** in the Studio desktop app.

The site has six demo products (SKUs `PIKA-*`) to test against.

## Conventions

Matches the author's existing plugins (see `../devbench`):

- WordPress Coding Standards. Tabs. `array()` not `[]`. Yoda conditions. Spaces
  inside parentheses. `defined( 'ABSPATH' ) || exit;` at the top of every PHP file.
- Class files are `includes/class-{slug}.php` holding `Pika_POS_{Slug}` —
  `class-orders.php` holds `Pika_POS_Orders`. No namespaces, no PSR-4.
- Prefixes: `pika_pos_` functions, `PIKA_POS_` constants, `pika_pos_` options and
  hooks, `{$wpdb->prefix}pika_pos_` tables.
- Text domain `pika-pos` on every user-facing string.
- Escape on output (`esc_html`, `esc_attr`, `wp_kses_post`), sanitise on input,
  a real `permission_callback` on every REST route.

The `pika-pos-conventions` skill has the details, including how a feature threads
through PHP → REST → JS.

## Tooling

| Command | What it does |
|---|---|
| `composer run lint` | PHPCS against WordPress Coding Standards |
| `composer run lint:fix` | PHPCBF auto-fix |
| `composer run analyse` | PHPStan level 5, with WordPress + WooCommerce + WP-CLI stubs |
| `composer run test` | PHPUnit — boots the real site (see `tests/php/bootstrap.php`) |
| `npm run build` | wp-scripts production build of `src/index.js` |
| `npm run start` | wp-scripts watch mode |
| `npm run lint` | JS + CSS + PHP in one go |
| `npm run test:e2e` | Playwright against the running site |

A `PostToolUse` hook ([bin/hook-lint.sh](bin/hook-lint.sh)) syntax-checks every
PHP and JSON file on write, so a parse error comes back immediately.

`src/index.js` is a placeholder that exists to keep the build and lint scripts
honest. Replace it — and add a `webpack.config.js` if you want named entries.

## MCP servers

Configured in [.mcp.json](.mcp.json):

- **playwright** — real browser against the local site. Use it to verify UI, not
  to guess.
- **site-db** — SQLite access to this site's WordPress database. Read-only in
  practice: to *write* data, go through WooCommerce APIs so hooks fire.
- **context7** — current WooCommerce/WordPress API docs. Check a signature here
  before relying on memory. If it rate-limits, set `CONTEXT7_API_KEY`.
- **filesystem** — scoped to this plugin and the WooCommerce source, so
  WooCommerce internals can be read directly.

## Verification

Never claim a change works on the strength of the code reading correctly. Run
`npm run lint`, `composer run analyse`, and — for anything user-facing — drive it
in a browser via the Playwright MCP server. The `pika-pos-verify` skill has the
full loop and the common failure modes.

## Don't

- Don't edit anything in `build/` — it is generated.
- Don't edit `wp-content/plugins/woocommerce/` — read it, never patch it.
- Don't run destructive `studio wp db` commands; the SQLite file is the only copy.
- Don't add a Composer or npm dependency without saying why it earns its weight.
- Don't build out features that weren't asked for. Scope creep here is a real
  risk — the plugin is deliberately a skeleton.
