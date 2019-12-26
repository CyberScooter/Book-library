<?php
session_start();
include "../db_operations.php";
include "../config/db_connection.php";


?>


<?php include '../templates/header.php'; ?>

<body>

    <?php if(isset($_SESSION['User'])){ ?>
        
        <a class="Button" href> Add </a>
    <?php }else { ?>
        <h1> Login/register required </h1>

    <?php } ?>

<?php include '../templates/footer.php'; ?>