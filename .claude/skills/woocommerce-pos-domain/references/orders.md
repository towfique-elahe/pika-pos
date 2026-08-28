# Order reference

## Statuses

| Status | Meaning at a counter |
|---|---|
| `pending` | Order shell built, not yet paid. Never leave one behind. |
| `processing` | Paid, awaiting fulfilment. Rare for POS. |
| `completed` | Paid and handed over. The normal POS end state. |
| `refunded` | Fully refunded. Partial refunds keep the original status. |
| `failed` / `cancelled` | Not a POS outcome; a failed tender means no order. |

If order creation fails partway, `$order->delete( true )` the shell. A stray
`pending` order pollutes reporting and looks to the shop owner like a lost sale.

## Useful methods

```php
$order->get_id();
$order->get_order_number();          // respects sequential-number plugins
$order->get_total();                 // string
$order->get_total_tax();
$order->get_items();                 // WC_Order_Item_Product[]
$order->get_created_via();           // 'pika-pos' for register sales
$order->get_payment_method();
$order->get_date_created();          // WC_DateTime|null — always null-check
$order->add_order_note( $note );     // private note; second arg true = customer-facing
$order->set_address( $array, 'billing' );
```

## Querying POS orders

```php
wc_get_orders( array(
	'limit'      => -1,
	'status'     => array( 'wc-completed' ),
	'meta_query' => array(
		array( 'key' => '_pika_pos_session_id', 'value' => $session_id ),
	),
) );
```

Statuses in `wc_get_orders()` take the `wc-` prefix; `$order->get_status()`
returns them without it. This asymmetry causes real bugs — compare with
`$order->has_status( 'completed' )` instead of string equality.

## Meta worth writing on a POS order

| Key | Contents |
|---|---|
| `_pika_pos_register_id` | Register the sale was rung up on |
| `_pika_pos_session_id` | Till session, for cash-up |
| `_pika_pos_cashier_id` | The user who took the money |
| `_pika_pos_amount_tendered` | Cash handed over (cash sales only) |
| `_pika_pos_change_due` | Change given back |

Leading underscore keeps them out of the admin's custom-fields box.

## Hooks worth knowing

| Hook | When |
|---|---|
| `woocommerce_new_order` | Order first saved |
| `woocommerce_order_status_completed` | Reached completed |
| `woocommerce_payment_complete` | `payment_complete()` finished |
| `pika_pos_order_created` | Suggested plugin hook: order paid and recorded |

Give extensions a plugin-specific hook to use when they only care about register
sales, rather than making them filter the generic WooCommerce ones.
