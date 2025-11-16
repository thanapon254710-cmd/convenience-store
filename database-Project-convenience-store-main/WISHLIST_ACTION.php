<?php
session_start();

if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

$action = $_POST['action'] ?? ($_GET['action'] ?? null);
$id     = $_POST['product_id'] ?? null;
$name   = $_POST['product_name'] ?? null;

if ($action && $name) {
    // fallback id if not provided
    if (!$id) {
        $id = $name;
    }

    if ($action === 'toggle' || $action === 'add') {

        // If toggling and already in wishlist -> remove
        if ($action === 'toggle' && isset($_SESSION['wishlist'][$id])) {
            unset($_SESSION['wishlist'][$id]);
        } else {
            $price  = isset($_POST['product_price']) ? (float)$_POST['product_price'] : 0;
            $image  = $_POST['product_image'] ?? 'asset/placeholder.png';
            $rating = $_POST['product_rating'] ?? '5.0';

            $_SESSION['wishlist'][$id] = [
                'id'        => $id,
                'name'      => $name,
                'price'     => $price,
                'image_url' => $image,
                'in_stock'  => true,
                'rating'    => $rating,
            ];
        }

    } elseif ($action === 'remove') {
        if (isset($_SESSION['wishlist'][$id])) {
            unset($_SESSION['wishlist'][$id]);
        }
    }
}

// Redirect back to the previous page (or HOME.php if not available)
$redirect = $_SERVER['HTTP_REFERER'] ?? 'HOME.php';
header("Location: " . $redirect);
exit;
