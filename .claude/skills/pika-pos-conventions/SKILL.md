---
name: pika-pos-conventions
description: How to add or change a feature in the Pika POS plugin — file layout, naming, the PHP↔REST↔JS seam, and the rules that keep register sales behaving like normal WooCommerce orders. Use when writing or reviewing any Pika POS code.
---

# Pika POS conventions

The plugin is currently a skeleton: `pika-pos.php` boots and `includes/` is
empty. This is the shape to grow into, not a description of what exists.

## Where code goes

| Kind of change | File |
|---|---|
| New behaviour on load | `pika-pos.php` — wire it, don't implement it there |
| Schema change | `includes/class-install.php` — version-gated `dbDelta()` |
| New endpoint | `includes/rest/class-rest-{thing}-controller.php` |
| Anything that writes a sale | one domain class, e.g. `includes/class-orders.php` |
| Register / till / drawer data | `includes/class-registers.php` |
| New screen or widget | `src/` |

## Naming

```
includes/class-foo-bar.php   →  class Pika_POS_Foo_Bar
functions                    →  pika_pos_do_thing()
constants                    →  PIKA_POS_THING
options                      →  pika_pos_thing
hooks                        →  do_action( 'pika_pos_thing' )
tables                       →  {$wpdb->prefix}pika_pos_thing
text domain                  →  'pika-pos'  (on every user-facing string)
```

WordPress Coding Standards throughout: tabs, `array()` not `[]`, Yoda
conditions, spaces inside parentheses, `defined( 'ABSPATH' ) || exit;` as the
first statement after the file docblock.

## Adding a feature end to end

A feature like "apply a discount to a line" touches four layers, in this order:

1. **Domain** — the rule lives in a PHP class under `includes/`, not in a
   controller and not in JS.
2. **REST** — expose it with a `permission_callback` and a fully declared `args`
   schema. Arguments declared with a `type` and `sanitize_callback` are validated
   by WordPress before your callback runs; anything undeclared is not validated
   at all.
3. **Client API** — one module owns `apiFetch`; components never call it directly.
4. **UI** — a component. State that outlives one component gets its own store
   module.

Then verify — see the `pika-pos-verify` skill.

## Non-negotiables

**Every REST route needs a real `permission_callback`.** `__return_true` is never
correct in a POS; these routes read customer records and create paid orders.

**Sales go through one domain class.** Give `wc_create_order()` exactly one
caller. Bypassing it skips stock reduction, order notes, the paid date and your
own hooks, and produces orders that look wrong in WooCommerce reports.

**Never `get_post_meta()` on an order ID.** The plugin declares HPOS
compatibility, so orders may not be posts. Use `$order->get_meta()` /
`$order->update_meta_data()` + `$order->save()`, and `wc_get_orders()` to query.

**Money is a decimal string until it is displayed.** `wc_format_decimal()` on the
way in. Client-side totals are indicative only — WooCommerce's
`calculate_totals()` is authoritative, because it owns tax, coupons and rounding.

**The database here is SQLite.** MySQL-only SQL fails on this site even though it
works in production. Prefer `$wpdb->insert/update/replace` and `$wpdb->prepare()`.

**Escape late, sanitise early.** `esc_html`/`esc_attr`/`esc_url`/`wp_kses_post` at
the point of output, including inside `printf()`.

See the `woocommerce-pos-domain` skill for the WooCommerce mechanics behind these
rules.
