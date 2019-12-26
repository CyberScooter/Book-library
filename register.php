<?php 
session_start();

include "db_operations.php";
include "./config/db_connection.php";

if(isset($_POST['submit'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
    $passConfirmation = $_POST['passwordConfirmation'];
    $username = $_POST['username'];
    if($email != null  && $password != null && $passConfirmation != null && $username != null){
        registerUser($conn, $email, $password, $passConfirmation, $username);
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

<?php include './templates/header.php'; ?>

    <?php if(!isset($_SESSION['User'])){ ?>

    <p><?php echo $error ?></p>
    <form action="register.php" method="POST">
        <input type="text" name="username" placeholder="Enter username">
        <input type="text" name="email" placeholder="Enter email address">
        <input type="password" name="password" placeholder="Create a password">
        <input type="password" name="passwordConfirmation" placeholder="Retype password">
        <input type="submit" name="submit">
    </form>

    <?php }else{ ?>

        <p> Session is already active </p>

    <?php } ?>

<?php include './templates/footer.php'; ?>