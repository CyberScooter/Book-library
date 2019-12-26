
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/coursework/resources/index.css">
    <title>Books list</title>
</head>
<body>
    
    <ul class="nav">
        <?php echo (isset($_SESSION['User'])) ? '<li><a class="User">< echo $username = getUsernameFromUsersTable($conn, $_SESSION["User"]) </a></li>' : NULL ?>
        <li><a <?php echo ($_SERVER['REQUEST_URI'] == "/coursework/index.php") ? 'class="active"' : NULL ?> href="/coursework/index.php">Home</a></li>

        <?php if(isset($_SESSION['User'])) { ?>
            <li><a <?php echo ($_SERVER['REQUEST_URI'] == "/coursework/profile/index.php") ? 'class="active"' : NULL ?> href="/coursework/profile/index.php">Profile</a></li>
            <li><a <?php echo ($_SERVER['REQUEST_URI'] == "/coursework/book/add.php") ? 'class="active"' : NULL ?> href="/coursework/book/add.php">Add Book</a></li>
            <li style="float:right"><a href="/coursework/index.php?logout" name="Logout">Logout </a></li>
        <?php }else { ?>
            <li style="float:right"><a href="/coursework/register.php">Register</a></li>
            <li style="float:right"><a href="/coursework/login.php">Login</a></li>
        <?php } ?>

    </ul>

    <div class="container">
