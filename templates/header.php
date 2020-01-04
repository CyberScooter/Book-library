
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/coursework/resources/index.css">
    <title>Books list</title>
    <style>
        body {
            padding: 0;
            margin: 0;
            /* PREMIUM USERS FEATURE */
            /* If bg-image session is set then set the background-image css property to the image set in the session */
            background-image: url("<?php echo (isset($_SESSION['bg-image'])) ? $_SESSION['bg-image'] : null ?>");
            /* STANDARD USERS FEATURE */
            /* Else if it is a standard account then just set 'background'color' to some grey colour */
            background-color: <?php echo (!isset($_SESSION['bg-image'])) ? '#DCDCDC;' : NULL;?>
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
    </style>
</head>
<body>
    
    <ul class="nav">
        <!-- This displays the username on the leftmost part of the navbar if there is an active session-->
        <?php if(isset($_SESSION['User'])){ ?>
            <!-- Retrieves the username from the session variable through db_operations.php file which should
            be included in all php files and displays it in an '<li>' tag -->
            <?php $username = getUsernameFromUsersTable($conn, $_SESSION["User"]) ?>
            <?php echo "<li><a class='User'> $username </a></li>" ?>
        <?php } ?>

        <!-- These REQUEST_URI gets the current URL the user is on, this is compared with specific urls and is used to make an active css property on the navitems-->

        <li><a <?php echo ($_SERVER['REQUEST_URI'] == "/coursework/index.php") ? 'class="active"' : NULL ?> href="/coursework/index.php">Home</a></li>

        <?php if(isset($_SESSION['User'])) { ?>
            <li><a <?php echo ($_SERVER['REQUEST_URI'] == "/coursework/profile/index.php") ? 'class="active"' : NULL ?> href="/coursework/profile/index.php">Profile</a></li>
            <li><a <?php echo ($_SERVER['REQUEST_URI'] == "/coursework/book/add.php") ? 'class="active"' : NULL ?> href="/coursework/book/index.php">My Books</a></li>
            <!-- This sends a GET request which is triggered by an HTML element, it redirect to the landing page and has a query of 'logout' which is read by the
            isset global 'GET' variable stored in that file that unsets the session which would basically logout the user -->
            <li style="float:right"><a href="/coursework/index.php?logout" name="Logout">Logout </a></li>
        <?php }else { ?>
            <li style="float:right"><a href="/coursework/register.php">Register</a></li>
            <li style="float:right"><a href="/coursework/login.php">Login</a></li>
        <?php } ?>

    </ul>

    <div class="container">
