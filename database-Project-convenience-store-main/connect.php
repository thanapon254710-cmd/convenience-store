<?php
$mysqli = new mysqli('localhost','root','root','convenience');
   if($mysqli->connect_errno){
      echo $mysqli->connect_errno.": ".$mysqli->connect_error;
   } else {
      echo "Connected to the database successfully.";
   }
?>