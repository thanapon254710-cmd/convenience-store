<?php
session_start();
$_SESSION['cart'] = [];

$back = $_SERVER['HTTP_REFERER'] ?? 'HOME.php';
header("Location: " . $back);
exit();
?>
