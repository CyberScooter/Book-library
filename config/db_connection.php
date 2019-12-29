<?php 


$conn = mysqli_connect('localhost', 'hrithik', 'dbtest1233', 'books_list');

if(!$conn){
    echo 'Connection error: ' . mysqli_connect_error();
}



?>