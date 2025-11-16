<?php
session_start();

if (!isset($_SESSION["usercon"]) || !isset($_SESSION["passcon"])) {
    // Not logged in or session expired
    header("Location: login.php");
    exit;
}

$user = $_SESSION["usercon"];
$pass = $_SESSION["passcon"];

$mysqli = new mysqli('localhost',$user ,$pass,'convenience_store');

   if($mysqli->connect_errno){
      die("Database connection failed: " . $mysqli->connect_error);
   }
?>