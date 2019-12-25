
<?php
include "../db_operations.php";
include "../config/db_connection.php";
include "../session_handling.php"


$isbn = "1222223123123123";
$title = "test title 2";
$description = "test description 2";

addBook($conn, $isbn, $title, $description);

mysqli_close($conn);






?>