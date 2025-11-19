<?php
session_start();

if (!isset($_SESSION["db_username"]) || !isset($_SESSION["db_password"])) {
    // Not logged in or session expired
    header("Location: login.php");
    exit;
}

$user = $_SESSION["db_username"];
$pass = $_SESSION["db_password"];

$mysqli = new mysqli('localhost',$user ,$pass,'convenience_store');

   if($mysqli->connect_errno){
      die("Database connection failed: " . $mysqli->connect_error);
   }
?>