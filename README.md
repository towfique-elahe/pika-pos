# Pika POS

Point of Sale for WooCommerce — a register that turns in-person sales into real
WooCommerce orders.

> **Status: skeleton.** The development environment is complete and green; the
> plugin itself is not implemented yet. `pika-pos.php` boots, gates on
> WooCommerce and declares HPOS compatibility. `includes/` is empty.

## Why another POS

Because a sale at the counter should be an ordinary WooCommerce order — so stock
moves, reports add up, refunds work, and every other WooCommerce extension keeps
behaving as it already does. No shadow order table, no reconciliation step.

## Requirements

| | |
|---|---|
| WordPress | 6.6+ |
| WooCommerce | 9.0+ |
| PHP | 8.0+ |
| Node | 20+ |

## Getting started

```bash
git clone https://github.com/towfique-elahe/pika-pos.git
cd pika-pos
composer install
npm install
npm run build
```

Then activate the plugin from **Plugins** in wp-admin.

## Layout

```
pika-pos.php              Bootstrap: constants, requirement gate, HPOS declaration
includes/                 PHP modules — class-{slug}.php holding Pika_POS_{Slug}
src/                      Front-end source, built by @wordpress/scripts
build/                    Generated. Not committed, never edited by hand
bin/                      Tooling scripts (lint hook, PHPStan bootstrap)
tests/php/                PHPUnit integration harness
tests/e2e/                Playwright configuration
.claude/                  Claude Code skills, agents, commands and settings
```

## Development

| Command | What it does |
|---|---|
| `composer run lint` | PHPCS against the WordPress Coding Standards |
| `composer run lint:fix` | PHPCBF auto-fix |
| `composer run analyse` | PHPStan level 5, with WordPress + WooCommerce + WP-CLI stubs |
| `composer run test` | PHPUnit |
| `npm run start` | wp-scripts watch build |
| `npm run build` | Production build |
| `npm run lint` | JS + CSS + PHP in one pass |
| `npm run test:e2e` | Playwright against the running site |

CI runs the lint, analysis and build steps on every push and pull request.

### Conventions

WordPress Coding Standards throughout — tabs, `array()` over `[]`, Yoda
conditions, `defined( 'ABSPATH' ) || exit;` at the top of every PHP file. Class
files are `includes/class-{slug}.php` holding `Pika_POS_{Slug}`; no namespaces.
Prefixes are `pika_pos_` for functions, options and hooks, `PIKA_POS_` for
constants, and `{$wpdb->prefix}pika_pos_` for tables.

Three rules carry most of the weight:

1. **One caller of `wc_create_order()`.** Anything that writes a sale goes
   through a single domain class, so stock, order notes, the paid date and
   reporting hooks can never be skipped.
2. **HPOS always.** `$order->get_meta()` and `wc_get_orders()`, never
   `get_post_meta()` on an order ID.
3. **Every REST route checks a capability.** `__return_true` is never correct in
   a plugin that reads customer records and takes payments.

### Testing

Tests are integration tests: they boot the real local WordPress install and run
against real WooCommerce and a real database. A POS is almost entirely
integration, and mocking WooCommerce would test the mocks instead of the plugin.
Anything a test creates, it removes in `tearDown()`.

## Working with Claude Code

The repository ships a full [Claude Code](https://claude.com/claude-code) setup
in `.claude/`, so the assistant starts with the project's conventions rather than
guessing at them.

**Skills** — loaded automatically when relevant:

- `pika-pos-conventions` — file layout, naming, and how a feature threads through
  PHP → REST → JS
- `woocommerce-pos-domain` — order creation, HPOS, stock, tax, refunds and till
  reconciliation, with references
- `pika-pos-verify` — how to actually prove a change works

**Agents** — `wp-security-reviewer`, `woo-order-auditor`, `pos-terminal-tester`.

**Commands** — `/pos-check`, `/pos-fix`, `/pos-endpoint`, `/pos-logs`.

**MCP servers** (`.mcp.json`) — Playwright for real-browser verification, SQLite
for reading the site database, Context7 for current WooCommerce API docs, and a
scoped filesystem server over the plugin and the WooCommerce source.

`.mcp.json` and `tests/e2e/playwright.config.js` carry absolute paths and a
`localhost` port from the original development machine. Adjust them for your own
setup.

## Roadmap

Decided, not yet built:

- Full-screen cashier terminal on a standalone front-end route, React on
  `@wordpress/scripts`
- Product search, tap-to-add grid, barcode and SKU scanning
- Cash and card tenders with change calculation
- Till sessions — opening float, cash movements, cash-up with variance
- Cashier and manager roles, so counter staff need no admin access

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
