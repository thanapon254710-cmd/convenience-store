<?php
session_start();

if (isset($_POST['setting']) && isset($_POST['value'])) {
    $key = $_POST['setting'];
    $value = $_POST['value'] == "1";

    $_SESSION['settings'][$key] = $value;
}

header("Location: setting.php");
exit;
?>
