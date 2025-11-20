<?php
session_start();

if (!isset($_SESSION["db_username"]) || !isset($_SESSION["db_password"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["db_username"];
$pass = $_SESSION["db_password"];

$mysqli = new mysqli('localhost', $user, $pass, 'convenience_store');

if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}
?>
