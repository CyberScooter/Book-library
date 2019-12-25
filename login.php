<?php

    include "db_operations.php";
    include "./config/db_connection.php";

    global $error;
    if(isset($_SESSION['errmessage'])){
        $error = $_SESSION['errmessage'];
    }
    
    unset($_SESSION['errmessage']);
    
    if(isset($_POST['submit'])){
        loginUser($conn, $_POST['email'], $_POST['password']);
    }

    mysqli_close($conn);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

    <?php if(!isset($_SESSION['User'])){ ?>
    <p><?php echo $error ?></p>
    <form action="login.php" method="POST">
        <input type="text" name="email" placeholder="Enter email address">
        <input type="password" name="password" placeholder="Enter password">
        <input type="submit" name="submit">
    </form>

    <?php }else{ ?>
        <p> Session is already active </p>


    <?php } ?>
</body>
</html>