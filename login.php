<?php
session_start();

include "db_operations.php";
include "./config/db_connection.php";

// If login button is pressed
if(isset($_POST['login'])){
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    if($email != null && $password != null){
        loginUser($conn, $_POST['email'], $_POST['password']);
    }else{
        $_SESSION['errmessage'] = "Fill in all boxes";
    }

}

global $error; 
if(isset($_SESSION['errmessage'])){ 
    $error = $_SESSION['errmessage'];
    unset($_SESSION['errmessage']); 
} 

global $success; 
if(isset($_SESSION['successmessage'])){ 
    $success = $_SESSION['successmessage']; 
    unset($_SESSION['successmessage']); 
}

?>


<?php include './templates/header.php'; ?>

<h1 class="error"><?php echo $error?></h1>
<h1 class="success"><?php echo $success?></h1>

<div class="container">

<?php if(!isset($_SESSION['User'])){ ?>

    <form action="login.php" method="POST">
        <input class="TextBox" type="text" name="email" placeholder="Enter email address">
        <input class="TextBox" type="password" name="password" placeholder="Enter password">
        <input class="Button" type="submit" name="login" value="Login">
    </form>

<?php } else { ?>

    <p> Session is already active </p>

<?php } ?>

<?php include './templates/footer.php'; ?>