<?php

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

function registerUser($conn, $email, $password, $passwordConfirmation, $username){
   if($password == $passwordConfirmation){
      $sqlSelectEmail = "SELECT Email FROM users WHERE Email='$email'";
      $resultEmail = mysqli_query($conn, $sqlSelectEmail);
      if(mysqli_num_rows($resultEmail) == 0){
         $sqlSelectUsername = "SELECT Username FROM profile WHERE Username='$username'";
         $resultUsername = mysqli_query($conn, $sqlSelectUsername);
         if(mysqli_num_rows($resultUsername) == 0){
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sqlInsertUser = "INSERT INTO users(Email,Username,Hash) VALUES('$email','$username','$hashedPassword')";
            $sqlInsertProfile = "INSERT INTO profile(Username) VALUES('$username')";
            if(mysqli_query($conn, $sqlInsertUser) && mysqli_query($conn, $sqlInsertProfile)){
               $_SESSION['User'] = $email;
               header('Location: index.php');
               exit();
            }  
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

function getUsernameFromUsersTable($conn, $email){
   $sqlSelect = "SELECT Username FROM users WHERE Email='$email'";
   $result = mysqli_query($conn, $sqlSelect);
   $userArray = mysqli_fetch_array($result, MYSQLI_ASSOC);
   return $userArray['Username'];
}

function updateProfile($conn, $email, $dataArray){
   $username = getUsernameFromUsersTable($conn, $email);
   $bio = $dataArray['Bio'];
   $picture = $dataArray['Picture'];
   $sqlUpdateProfile = "UPDATE profile SET Bio='$bio', Picture='$picture' WHERE Username='$username'";
   if(mysqli_query($conn, $sqlUpdateProfile)){
      header('Location: index.php');
      exit();
   }
   header('Location: edit.php');
   exit();
}

function getProfileData($conn, $email){
   $username = getUsernameFromUsersTable($conn, $email);
   $sqlSelectProfile = "SELECT Username,Bio,Picture FROM profile WHERE Username='$username'";
   $result = mysqli_query($conn, $sqlSelectProfile);
   return mysqli_fetch_array($result, MYSQLI_ASSOC);

}

function searchUser($conn, $username){
   $sqlSelectProfile = "SELECT Username,Bio,Picture FROM profile WHERE Username='$username'";
   $result = mysqli_query($conn, $sqlSelectProfile);
   return mysqli_fetch_array($result, MYSQLI_ASSOC);
}

function getBooksData($conn){

}

?>