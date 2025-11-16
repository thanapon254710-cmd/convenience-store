<?php
// CRITICAL FIX 1: Start output buffering
ob_start();
session_start();

// Check if the request is a GET and if the 'index' parameter is present
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Determine if removing one item or all items
    if (isset($_GET['one'])) {
        $index = filter_var($_GET['one'], FILTER_VALIDATE_INT);
        if ($index !== false && isset($_SESSION['cart'][$index])) {
            // Decrease quantity by 1 or remove item if quantity is 1
            if (isset($_SESSION['cart'][$index]['quantity']) && $_SESSION['cart'][$index]['quantity'] > 1) {
                $_SESSION['cart'][$index]['quantity'] -= 1;
            } else {
                unset($_SESSION['cart'][$index]);
                // Reindex the array to maintain consistent indices
                $_SESSION['cart'] = array_values($_SESSION['cart']);
            }
        }
    
    } elseif (isset($_GET['increase'])) {
        $index = filter_var($_GET['increase'], FILTER_VALIDATE_INT);
        if ($index !== false && isset($_SESSION['cart'][$index])) {
            // If quantity is not set yet, assume 1
            $_SESSION['cart'][$index]['quantity'] += 1;  // <— increase by 1
        }
        
    } elseif (isset($_GET['all'])) {
        $index = filter_var($_GET['all'], FILTER_VALIDATE_INT);
        if ($index !== false && isset($_SESSION['cart'][$index])) {
            // Remove the item entirely from the cart
            unset($_SESSION['cart'][$index]);
            // Reindex the array to maintain consistent indices
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
    }
}
// Redirect back to HOME.php after modification
header("Location: HOME.php");
exit();
?>