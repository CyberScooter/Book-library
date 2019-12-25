<?php
    session_start();

    //CHANGE 'User' to a session ID ELSE IT WILL DELETE ALL SESSIONS IF MULTIPLE USERS USE IT
    if(isset($_GET['Logout'])){
        unset($_SESSION['User']);
        header('Location: index.php');
    }
    

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

    <?php if(isset($_SESSION['User'])){ ?>
        <form action="index.php" method="GET">
            <input type="submit" value="Logout" name="Logout">
        </form>
        <p> welcome to books list </p>
    <?php }else{ ?>
        <p> Please login or register </p>
    <?php } ?>


</body>
</html>