<?php
// CRITICAL FIX 1: Start output buffering
ob_start(); 
session_start();

// Check if the request is a GET and if the 'index' parameter is present
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['index'])) {
    
    // 1. SECURELY Sanitize the index: Use filter_var to ensure it's a valid integer
    $index_to_remove = filter_var($_GET['index'], FILTER_VALIDATE_INT);

    // 2. Proceed only if the index is a valid non-negative integer (CRITICAL)
    if ($index_to_remove !== false && $index_to_remove >= 0) {
        
        // 3. Check if the cart exists and if the index is valid
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && array_key_exists($index_to_remove, $_SESSION['cart'])) {
            
            // 4. Remove the item
            unset($_SESSION['cart'][$index_to_remove]);
            
            // 5. Re-index the array: CRITICAL for cart stability
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
    }
    
    // 6. Redirect the user
    header("Location: HOME.php");
    exit();
}

// Redirect back if accessed without a valid index
header("Location: HOME.php");
exit();
?>