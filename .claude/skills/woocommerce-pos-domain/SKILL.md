---
name: woocommerce-pos-domain
description: WooCommerce mechanics a point-of-sale plugin has to get right — creating and paying orders, HPOS, stock, taxes, refunds and till reconciliation. Use when writing anything that touches orders, products, stock or money in Pika POS.
---

# WooCommerce for a point of sale

A POS is not a checkout. The customer is standing at the counter, the goods are
already in their hand, and the money has already changed hands by the time your
code runs. That single difference drives most of what follows.

## The order path

One call does the whole job:

```php
$order = wc_create_order( array( 'created_via' => 'pika-pos', 'status' => 'pending' ) );
$order->add_product( $product, $quantity, $line_args );
$order->set_payment_method( 'pika_pos_cash' );
$order->calculate_totals( true );   // true = recalculate taxes
$order->save();
$order->payment_complete();          // paid date, stock, emails, reporting
```

`payment_complete()` is the important one. It stamps `date_paid`, reduces stock
via `wc_maybe_reduce_stock_levels()`, moves the order to `processing` or
`completed`, and fires the hooks reporting depends on. Setting the status by hand
instead produces an order that looks right in the list table and is wrong
everywhere else.

A POS sale then goes to `completed` — the goods have left the shop, so there is
nothing to fulfil.

**Order matters**: `calculate_totals()` must run *after* every line item is
added, and *before* you compare the total against cash tendered.

## Payment methods without gateways

Set POS tenders directly on the order with `set_payment_method()` /
`set_payment_method_title()`. Do not register them as `WC_Payment_Gateway`
classes: a registered gateway shows up at web checkout, and there is nothing for
it to process — the money is already in the drawer.

## HPOS

The plugin declares `custom_order_tables` compatibility, so orders may live in
`wp_wc_orders`, not `wp_posts`.

| Never | Always |
|---|---|
| `get_post_meta( $order_id, ... )` | `$order->get_meta( ... )` |
| `update_post_meta( $order_id, ... )` | `$order->update_meta_data( ... )` then `$order->save()` |
| `WP_Query` / `get_posts` on `shop_order` | `wc_get_orders( array( ... ) )` |
| `get_post( $order_id )` | `wc_get_order( $order_id )` |

`Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()`
tells you which store is live, but correct code does not need to branch on it.

## Stock

`payment_complete()` reduces stock once, guarded by the `_order_stock_reduced`
flag. Do not also call `wc_reduce_stock_levels()` — that double-decrements.

Check *before* selling, not after:

```php
if ( ! $product->has_enough_stock( $quantity ) ) { /* refuse the line */ }
```

Backorders are a judgement call at a counter: `has_enough_stock()` honours the
product's backorder setting, so a backorder-enabled product will pass.

## Money

- Never a float in storage or transport. `wc_format_decimal( $value, wc_get_price_decimals() )`.
- Never compute a total by hand — `calculate_totals()` owns tax, rounding and
  coupon interaction, and shops configure all three differently.
- Comparing tendered cash to a total needs an epsilon: floats do not compare
  cleanly — `$tendered + 0.0001 < $total`, not `$tendered < $total`.
- `wc_prices_include_tax()` changes what a "price" even means. Display comes from
  `wc_price()` server-side or `formatMoney()` client-side, never from string
  concatenation.

## Refunds

Never delete or reverse an order to undo a sale — that destroys the audit trail
a shop is legally required to keep. Create a refund against it:

```php
wc_create_refund( array(
	'order_id'       => $order->get_id(),
	'amount'         => $amount,
	'reason'         => $reason,
	'line_items'     => $line_items,   // restores stock when 'qty' is set
	'restock_items'  => true,
) );
```

## Till reconciliation

Expected drawer cash = opening float + **cash** sales + cash movements. Card
tenders never touch the drawer, so they must be excluded from that sum. Variance
is recorded, never silently corrected: a short drawer is information the shop
owner needs.

## Reference

- `references/orders.md` — order lifecycle, statuses, meta and hooks
- `references/products.md` — querying, variations, barcodes and stock
