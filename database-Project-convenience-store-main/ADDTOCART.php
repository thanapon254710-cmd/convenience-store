<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$name       = $_POST['product_name']  ?? null;
$price      = isset($_POST['product_price']) ? (float)$_POST['product_price'] : 0;
$actionType = $_POST['action_type']   ?? 'add_to_cart';
$return     = $_POST['return']        ?? null;

if ($name) {
    // find existing item
    $foundIndex = null;
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['name'] === $name && (float)$item['price'] === $price) {
            $foundIndex = $index;
            break;
        }
    }

    if ($foundIndex === null) {
        $_SESSION['cart'][] = [
            'name'     => $name,
            'price'    => $price,
            'quantity' => 1
        ];
    } else {
        $_SESSION['cart'][$foundIndex]['quantity'] =
            ($_SESSION['cart'][$foundIndex]['quantity'] ?? 1) + 1;
    }
}

// decide where to go after adding
if ($actionType === 'buy_now') {
    $dest = 'checkout.php';
} else {
    if ($return) {
        $dest = $return;        // e.g. WISHLIST.php
    } else {
        $dest = 'HOME.php';
    }
}

header("Location: " . $dest);
exit;
