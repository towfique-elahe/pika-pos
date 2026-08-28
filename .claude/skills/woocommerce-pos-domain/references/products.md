# Product reference

## Querying

```php
$results = wc_get_products( array(
	'status'       => 'publish',
	'limit'        => 24,
	'page'         => 1,
	'paginate'     => true,       // returns an object: ->products, ->total, ->max_num_pages
	's'            => $search,
	'stock_status' => 'instock',
	'category'     => array( 'slug' ),
	'orderby'      => 'title',
) );
```

With `paginate => true` the return is an **object**, not an array — code that
treats it as an array silently sees nothing. Without it, an array comes back.

## Variations

A `variable` product is not sellable; its variations are. Expand parents before
showing them in a grid:

```php
if ( $product->is_type( 'variable' ) ) {
	foreach ( $product->get_children() as $child_id ) { ... }
}
```

Each variation is a `WC_Product_Variation` with its own price, SKU and stock, and
`get_name()` already includes the attribute summary.

## Sellability

```php
$product->is_purchasable();          // published, has a price, not a parent
$product->is_in_stock();
$product->has_enough_stock( $qty );  // honours backorder settings
$product->managing_stock();
$product->get_stock_quantity();      // null when stock is not managed
```

`is_purchasable()` alone is not enough for a POS — a product can be purchasable
and out of stock.

## Barcodes

WooCommerce has no barcode field. The SKU is the pragmatic default:

```php
$product_id = wc_get_product_id_by_sku( $code );
```

Shops using a dedicated barcode field (a custom meta key, or the GTIN field
WooCommerce added in 9.2) need another route in. A filter that returns a product
ID and short-circuits the SKU lookup is the usual shape:

```php
add_filter( 'pika_pos_resolve_barcode', function ( $product_id, $code ) {
	$found = wc_get_products( array( 'limit' => 1, 'meta_key' => '_barcode', 'meta_value' => $code, 'return' => 'ids' ) );
	return $found ? $found[0] : $product_id;
}, 10, 2 );
```

Hardware scanners behave as keyboards: they type the code and press Enter. Treat
Enter in the search box as a scan, and fall back to plain search when nothing
matches.
