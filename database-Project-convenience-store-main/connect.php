<?php
$mysqli = new mysqli('localhost', 'root', 'root', 'convenience_store');

if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}
?>
