<?php // /keranjang — halaman keranjang
$cart = cart_items();
$subtotal = 0;
foreach ($cart as $c) $subtotal += $c['price'] * $c['qty'];
$tax = setting('tax_enabled') === '1' ? (int)round($subtotal * ((float)setting('tax_percent', '0') / 100)) : 0;

page('keranjang', ['cart' => $cart, 'subtotal' => $subtotal, 'tax' => $tax], [
    'title' => 'Keranjang',
    'robots' => 'noindex, follow',
]);
