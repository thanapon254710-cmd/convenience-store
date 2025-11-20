<?php
session_start();
require_once 'connect.php';

$error = '';

if (isset($_POST['submit'])) {
    $u = $_POST['username'] ?? '';
    $p = $_POST['passwd'] ?? '';

    if ($u === '' || $p === '') {
        $error = "Please enter both username and password.";
    } else {

        $q = $mysqli->prepare("
            SELECT user_id, username, role, points 
            FROM users
            WHERE username = ?
              AND password = AES_ENCRYPT(?, SHA1('password'))
            LIMIT 1
        ");

        if (!$q) {
            $error = "Prepare failed: " . $mysqli->error;
        } else {
            $q->bind_param("ss", $u, $p);
            $q->execute();
            $result = $q->get_result();

            if ($row = $result->fetch_assoc()) {

                $_SESSION['user_id']  = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role']     = $row['role'];
                $_SESSION['points']   = $row['points'];

                // IMPORTANT: Your MySQL accounts have NO PASSWORD
                // always use ONE database account
$mysqli = new mysqli('localhost', 'root', 'root', 'convenience_store');
if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}


                if ($mysqli->connect_errno) {
                    die("Database connection failed: " . $mysqli->connect_error);
                }

                $_SESSION['db_username'] = $dbUser;
                $_SESSION['db_password'] = $dbPass;

                if ($row['role'] === 'Admin') {
                    header("Location: ADMIN_HOME.php");
                } elseif ($row['role'] === 'Staff') {
                    header("Location: STAFF.php");
                } else {
                    header("Location: HOME.php");
                }
                exit;

            } else {
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
    <title>Login — Number 1 Shop</title>
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

                <?php if ($error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

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
