<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'connect.php';

$error = '';

// Handle login BEFORE any HTML output
if (isset($_POST['submit'])) {
    $u = $_POST['username'] ?? '';
    $p = $_POST['passwd'] ?? '';

    if ($u === '' || $p === '') {
        $error = "Please enter both username and password.";
    } else {
        // Prepared statement to avoid SQL injection
        $q = $mysqli->prepare("
            SELECT user_id, username, role, points 
            FROM users 
            WHERE username = ? AND password = AES_ENCRYPT(?, SHA1('password'))
        ");

        if (!$q) {
            $error = "Prepare failed: " . $mysqli->error;
        } else {
            $q->bind_param("ss", $u, $p);
            $q->execute();

            // Get result set from prepared statement
            $result = $q->get_result();

            if ($row = $result->fetch_assoc()) {
                // Login success
                $_SESSION['user_id']  = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role']     = $row['role'];
                $_SESSION['points']   = $row['points'];

                $roleDbAccounts = [
                    'Admin'    => ['user' => 'admin', 'pass' => 'admin'],
                    'Staff'    => ['user' => 'staff', 'pass' => 'staff'],
                    'Customer' => ['user' => 'customer', 'pass' => 'customer'],
                ];

                $role = $row['role']; 
                $dbUser = $roleDbAccounts[$role]['user'];
                $dbPass = $roleDbAccounts[$role]['pass'];

                $mysqli = new mysqli('localhost', $dbUser, $dbPass, 'convenience_store');

                if($mysqli->connect_errno){
                    die("Database connection failed: " . $mysqli->connect_error);
                }
                $_SESSION["usercon"] = $dbUser;
                $_SESSION["passcon"] = $dbPass;

                // Redirect depending on role
                // $_SESSION['db_message'] = "Connected to MySQL as $dbUser"; //check if connect successful or not
                if ($row['role'] === 'Admin') {
                    header("Location: ADMIN_HOME.php");
                } elseif ($row['role'] === 'Staff') {
                    header("Location: STAFF.php");
                } else {
                    header("Location: HOME.php");
                }
                exit;
            } else {
                // No matching user
                $error = "Invalid username or password.";
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
            <h2>Login</h2>
            <label>Username</label>
            <input type="text" name="username" placeholder="Enter your username" required>

            <label>Password</label>
            <input type="password" name="passwd" placeholder="Enter your password" required>

            <button type="submit" name="submit" class="primary">Login</button>

            <p class="small">
                <a href="signup.php">Don't have an account?</a>
            </p>
        </div>
    </form>
</main>
    
</body>
</html>