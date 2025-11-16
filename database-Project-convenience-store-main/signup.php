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
        // Using echo and Heredoc syntax (<<<EOT) is a good way to output large blocks of HTML 
        // within PHP without needing to escape quotes repeatedly.
        echo <<<EOT
        <div class="card">
            <h2>Signup</h2>
            <label>Username</label>
            <input type="text" placeholder="Enter your username" />
            <label>Password</label>
            <input type="password" placeholder="Enter your password" />
            <label>Confirm Password</label>
            <input type="password" placeholder="Confirm your password" />
            
            <a class="primary" href="index.php">Sign Up</a> 
            <p class="small"><a href="index.php">Already have an account?</a></p>
        </div>
        EOT;
    ?>

    </main>
</body>
</html>