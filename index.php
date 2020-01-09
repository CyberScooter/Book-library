<?php
session_start();

include "./db_operations.php";
include "./config/db_connection.php";

//When the logout button is pressed it unset session variables
if(isset($_GET['logout'])){
    unset($_SESSION['User']);
    unset($_SESSION['bg-image']);
    $_SESSION['successmessage'] = "Successfully logged out";
    header('Location: index.php');
    exit();
}

//Handles GET request to profile page
if(isset($_GET['profile'])){
    header('Location: ./profile/index.php');
    exit();
}

//User SESSION stores the email of the user
if(isset($_SESSION['User'])){
    if(checkIfPremiumUser($conn, $_SESSION['User']) && !isset($_SESSION['bg-image'])){
        setBackground($conn, $_SESSION['User']);
    }
    $username = getUsernameFromUsersTable($conn, $_SESSION['User']);
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

<!-- Dynamic page displayed whether user is logged in or not -->
<?php if(isset($_SESSION['User'])){ ?>
    <div class="container">
        <h1> Welcome <span class="User"><?php echo $username ?></span> to the Books list </h1>
        <h1> A social place to store reviews for books and manage the books you are reading!</h1>
    </div>
<?php } else { ?>
    <h1> Welcome to the Books list </h1>
    <h1> A social place to store reviews for books and manage the books you are reading!</h1>
    <h1> In order to start using the site please register or login above! </h1>
<?php } ?>

</div>
    
</body>
</html>