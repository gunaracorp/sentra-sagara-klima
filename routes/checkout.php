<?php // /checkout — form data pengiriman
$cart = cart_items();
if (!$cart) redirect('keranjang');
$subtotal = 0;
foreach ($cart as $c) $subtotal += $c['price'] * $c['qty'];
$tax = setting('tax_enabled') === '1' ? (int)round($subtotal * ((float)setting('tax_percent', '0') / 100)) : 0;

page('checkout', ['cart' => $cart, 'subtotal' => $subtotal, 'tax' => $tax], [
    'title' => 'Checkout',
    'robots' => 'noindex, nofollow',
]);
