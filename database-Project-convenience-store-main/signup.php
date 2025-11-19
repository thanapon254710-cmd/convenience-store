<?php
session_start();
require_once 'connect.php';

$error = '';
$success = '';

if (isset($_POST['submit'])) {
    $u  = $_POST['username'] ?? '';
    $p  = $_POST['passwd'] ?? '';
    $cp = $_POST['cpasswd'] ?? '';

    // Basic validation
    if ($u === '' || $p === '' || $cp === '') {
        $error = "Please fill in all fields.";
    } elseif ($p !== $cp) {
        $error = "Passwords do not match.";
    } else {
        // Insert new user (role = Customer, points = 0)
        $q = $mysqli->prepare(
            "INSERT INTO users (username, password, email, phone_number, role, points) 
             VALUES (?, ?, '', '', 'Customer', 0)"
        );

        if (!$q) {
            $error = "Prepare failed: " . $mysqli->error;
        } else {
            $q->bind_param("ss", $u, $p); // <-- for now storing plain password
            if ($q->execute()) {
                header("Location: index.php");
                exit;
            } else {
                $error = "Could not create account: " . $q->error;
            }
            $q->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header">
        <img class="logo logo--bgless" src="asset/2960679-2182.png" alt="The convenience store">
    </header>
    <main class="page">
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
            <div class="card">
                <h2>Signup</h2>
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter your username" />
                <label>Password</label>
                <input type="password" name="passwd" placeholder="Enter your password" />
                <label>Confirm Password</label>
                <input type="password" name="cpasswd" placeholder="Confirm your password" />
                
                <button type="submit" name="submit" class="primary">Sign Up</button>
                <p class="small"><a href="index.php">Already have an account?</a></p>
            </div>
        </form>
    </main>
</body>
</html>