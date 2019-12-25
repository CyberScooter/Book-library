<?php
session_start();

function addBook($conn,$isbn, $title, $description){
   $sql = "INSERT INTO books(ISBN,Title,Description) VALUES('$isbn','$title','$description')";
   if(mysqli_query($conn, $sql)){
      header('Location: index.php');
   }else {
      echo 'Query error' . mysqli_error($conn);
   }
}

function removeBook($bookID)
{
   //remove book if ISBN is not in reviews table, run everytime review is removed
   return $num;
}

function addReview($email, $reviewID, $review, $rating, $isbn, $title, $description){
   //checking if a session exists
   if(isset($_SESSION['User'])){
      $sql = "INSERT INTO books(ISBN,Title,Description) VALUES('$isbn','$title','$description')";
      if(mysqli_query($conn, $sql)){
         header('Location: index.php');
      }else {
         echo 'Query error' . mysqli_error($conn);
      }

      
   }
}

//view detailed review for specific post for AUTHORISED user, not any
//figure out how to do this by getting id from url
function viewDetailedReview($email ){

}


//use inner join to remove data from review table and bookslists table
//check for right join if it works
function removeReview($email, $reviewID){

}

//for index page
function selectAllUserBooksWithReviews(){

}

function registerUser($conn, $email, $password, $passwordConfirmation){
   if($password == $passwordConfirmation){
      $sqlSelect = "SELECT Email FROM users WHERE Email='$email'";
      $result = mysqli_query($conn, $sqlSelect);
      if(mysqli_num_rows($result) == 0){
         $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
         $sqlInsert = "INSERT INTO users(Email,Hash) VALUES('$email','$hashedPassword')";
         if(mysqli_query($conn, $sqlInsert)){
            $_SESSION['User'] = $email;
            header('Location: index.php');
            exit();
         }  
      }
      $_SESSION['errmessage'] = 'User already exists';
      header('Location: register.php');
      exit();
   }
   $_SESSION['errmessage'] = "Password does not match";
   header('Location: register.php');
   exit();
}

function loginUser($conn, $email, $password){
   $sqlSelect = "SELECT Hash FROM users WHERE Email='$email'";
   $result = mysqli_query($conn,$sqlSelect);
   $userArray = mysqli_fetch_array($result, MYSQLI_ASSOC);
   if(password_verify($password, $userArray['Hash'])){
      $_SESSION['User'] = $email;
      header('Location: index.php');
      exit();
   }
   $_SESSION['errmessage'] = 'User does not exist';
   header('Location: login.php');
   exit();
}

?>