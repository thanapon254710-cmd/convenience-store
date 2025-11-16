<?php
session_start();
unset($_SESSION['cart']); 

header("Location: HOME.php");  // <-- MATCH YOUR REAL FILE NAME EXACTLY
exit();
?>
