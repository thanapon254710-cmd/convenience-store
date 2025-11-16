<?php
session_start();

unset($_SESSION['cart']); // delete all cart items

header("Location: HOME.php");
exit();
