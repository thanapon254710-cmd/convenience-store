<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$id         = $_POST['product_id'] ?? null;
$name       = $_POST['product_name']  ?? null;
$price      = isset($_POST['product_price']) ? (float)$_POST['product_price'] : 0;
$image      = $_POST['product_image'] ?? 'asset/example-product-1.png';
$actionType = $_POST['action_type']   ?? 'add_to_cart';
$return     = $_POST['return']        ?? null;

if ($name) {
    // find existing item
    if ($actionType !== 'buy_now') {
        $_SESSION['checkout_mode'] = 'cart';
        $foundIndex = null;
        foreach ($_SESSION['cart'] as $index => $item) {
            if ($item['name'] === $name && (float)$item['price'] === $price) {
                $foundIndex = $index;
                break;
            }
        }

        if ($foundIndex === null) {
            $_SESSION['cart'][] = [
                'id'       => $id,
                'name'     => $name,
                'price'    => $price,
                'quantity' => 1,
                'image'    => $image
            ];
        } else {
            $_SESSION['cart'][$foundIndex]['quantity'] =
                ($_SESSION['cart'][$foundIndex]['quantity'] ?? 1) + 1;

            // make sure image is saved even if item already existed
            if (!isset($_SESSION['cart'][$foundIndex]['image'])) {
                $_SESSION['cart'][$foundIndex]['image'] = $image;
            }
        }
    } else {
        $_SESSION['buy_now_item'] = [
            'id'       => $id,
            'name'     => $name,
            'price'    => $price,
            'quantity' => 1,
            'image'    => $image
        ];
        $_SESSION['checkout_mode'] = 'buy_now'; 

        $return = 'checkout.php';
    }
}

// decide where to go after adding
if ($actionType === 'buy_now') {
    $dest = 'checkout.php';
} else {
    $dest = $return ? $return : 'HOME.php';
}

header("Location: " . $dest);
exit;