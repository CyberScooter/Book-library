<?php 


$conn = mysqli_connect('localhost', 'hrithik', 'databasetest123', 'books_list');

if(!$conn){
    echo 'Connection error: ' . mysqli_connect_error();
}



?>