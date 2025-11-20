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

        // IMPORTANT: passwords in DB are AES_ENCRYPT(password, SHA1('password'))
        // So here we DECRYPT and compare with the plain text the user typed.
        $sql = "
            SELECT user_id, username, role, points
            FROM users
            WHERE username = ?
              AND CAST(AES_DECRYPT(password, SHA1('password')) AS CHAR(50)) = ?
            LIMIT 1
        ";

        $q = $mysqli->prepare($sql);

        if (!$q) {
            $error = "Prepare failed: " . $mysqli->error;
        } else {
            $q->bind_param("ss", $u, $p);
            $q->execute();
            $result = $q->get_result();

            if ($row = $result->fetch_assoc()) {
                // LOGIN SUCCESS
                $_SESSION['user_id']  = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role']     = $row['role'];
                $_SESSION['points']   = $row['points'];

                // We now ALWAYS use the same DB user from connect.php (root/root),
                // so we don't need db_username/db_password any more.
                // Other pages will just use userconnect.php -> connect.php.

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

                <label for="username">Username</label>
                <input
                    id="username"
                    type="text"
                    name="username"
                    placeholder="Enter your username"
                    required
                >

                <label for="passwd">Password</label>
                <input
                    id="passwd"
                    type="password"
                    name="passwd"
                    placeholder="Enter your password"
                    required
                >

                <button type="submit" name="submit" class="primary">Login</button>

                <p class="small">
                    <a href="signup.php">Don't have an account?</a>
                </p>
            </div>
        </form>
    </main>
</body>
</html>
