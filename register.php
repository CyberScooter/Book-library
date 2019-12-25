<?php 

include "db_operations.php";
include "./config/db_connection.php";

if(isset($_POST['submit'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
    $passConfirmation = $_POST['passwordConfirmation'];
    if($email != null  && $password != null && $passConfirmation != null){
        registerUser($conn, $email, $password, $passConfirmation);
    }else{
        $_SESSION['errmessage'] = 'Fill in all boxes';
    }
}

global $error;
if(isset($_SESSION['errmessage'])){
    $error = $_SESSION['errmessage'];
}

unset($_SESSION['errmessage']);

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
    <form action="register.php" method="POST">
        <input type="text" name="email" placeholder="Enter email address">
        <input type="password" name="password" placeholder="Create a password">
        <input type="password" name="passwordConfirmation" placeholder="Retype password">
        <input type="submit" name="submit">
    </form>

    <?php }else{ ?>

        <p> Session already active </p>

    <?php } ?>

</body>
</html>