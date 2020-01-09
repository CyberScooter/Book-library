<?php 


$conn = mysqli_connect('localhost', 'root', '', 'books_reviews');

if(!$conn){
    echo 'Connection error: ' . mysqli_connect_error();
}

?>