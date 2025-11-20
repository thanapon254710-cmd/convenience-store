<?php
// userconnect.php
// Shared DB connection for logged-in pages

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optional: if you want to protect pages, they can still check user_id/role themselves
// Example (DON'T add here if you already check inside each page):
// if (!isset($_SESSION['user_id'])) {
//     header("Location: index.php");
//     exit;
// }

require_once 'connect.php';   // $mysqli is created here (root / root / convenience_store)
