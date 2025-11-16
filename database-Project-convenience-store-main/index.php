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
    <?php 
        echo '<div class="card">';
        echo '<h2>Login</h2>';
        echo '<label>Username</label>';
        echo '<input type="text" placeholder="Enter your username">';
        echo '<label>Password</label>';
        echo '<input type="password" placeholder="Enter your password">';
        echo '<a class="primary" href="HOME.php">Login</a>';
        echo '<p class="small"><a href="signup.php">Don\'t have an account?</a></p>';
        echo '</div>';
    ?>
</main>
    
</body>
</html>