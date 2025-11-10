<?php
// CRITICAL FIX 1: Start output buffering FIRST to ensure headers can be sent later (like redirects)
ob_start();
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Get and SECURELY sanitize product details
    $name = $_POST['product_name'] ?? 'Unknown Product';
    // CRITICAL FIX 2: Use filter_var for robust float sanitization/validation
    $price = filter_var($_POST['product_price'] ?? 0.00, FILTER_VALIDATE_FLOAT);
    $action = $_POST['action_type'] ?? 'add_to_cart'; 

    // Initialize the cart array if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // --- Quantity Management Fix: Check if the product is ALREADY in the cart ---
    $found = false;
    foreach ($_SESSION['cart'] as $key => &$item) {
        // Compare by name AND price for a unique match
        if ($item['name'] === $name && $item['price'] === $price) {
            // Product found: increment quantity
            $item['quantity'] = ($item['quantity'] ?? 1) + 1;
            $found = true;
            break;
        }
    }
    unset($item); // Remove reference

    // If the product was NOT found, add it as a new item with quantity 1
    if (!$found) {
        $_SESSION['cart'][] = [
            'name' => $name,
            'price' => $price,
            'quantity' => 1
        ];
    }

    // 4. Redirect the user (Post-Redirect-Get pattern)
    if ($action === 'buy_now') {
        header("Location: checkout.php"); 
    } else {
        header("Location: HOME.php"); 
    }
    exit();
}

// Redirect if accessed directly without POST data
header("Location: HOME.php");
exit();
?>