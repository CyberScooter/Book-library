<?php 
session_start();

include "db_operations.php";
include "./config/db_connection.php";

if(isset($_POST['registerStandard'])){
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    $passConfirmation = htmlspecialchars($_POST['passwordConfirmation']);
    $username = htmlspecialchars($_POST['username']);
    if($email != null  && $password != null && $passConfirmation != null && $username != null){
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $_SESSION['errmessage'] = "Invalid email format";
            header('Location: ./register.php');
            exit();
        }
        if(strlen($password) <= 8){
            $_SESSION['errmessage'] = "Password must be more than 8 characters long";
            header('Location: ./register.php');
            exit();
        }
        registerUser($conn, $email, $password, $passConfirmation, $username);
    }else{
        $_SESSION['errmessage'] = 'Fill in all boxes';
    }
}

/**
 * If the error message session is set
 * then set the 'error' variable to be rendered in html to the error message
 * finally unset/clear the session variable for the error so that it doesnt display error after refresh
 */
global $error;
if(isset($_SESSION['errmessage'])){
    $error = $_SESSION['errmessage'];
}
unset($_SESSION['errmessage']);

?>

<?php include './templates/header.php'; ?>

<?php if(!isset($_SESSION['User'])){ ?>

    <p class="error"><?php echo $error ?></p>
    <form action="register.php" method="POST">
        <input class="TextBox" type="text" name="username" placeholder="Enter username">
        <input class="TextBox" type="text" name="email" placeholder="Enter email address">
        <input class="TextBox" type="password" name="password" placeholder="Create a password">
        <input class="TextBox" type="password" name="passwordConfirmation" placeholder="Retype password">
        <input class="Button" type="submit" name="registerStandard" value="Register">
    </form>

<?php } else { ?>

    <p> Session is already active </p>
    
<?php } ?>

<?php include './templates/footer.php'; ?>