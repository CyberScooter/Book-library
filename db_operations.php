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
      //adding multiple record to profile table if email is the same but username is different, FIX!
      //FIXED BUT UNDERSTAND HOW                                                                                                       //checks if email is same but that the profile username for that email is not the same as the one entered (this selects no records as user not found)
      $sqlSelectInnerJoin = "SELECT users.Email, users.Username FROM users INNER JOIN profile ON profile.Username = users.Username WHERE users.Email='$email' AND profile.Username != '$username'";
      $result = mysqli_query($conn, $sqlSelectInnerJoin);
      if(mysqli_num_rows($result) == 0){
         $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
         $sqlInsertUser = "INSERT INTO users(Email,Username,Hash) VALUES('$email','$username','$hashedPassword')";
         $sqlInsertProfile = "INSERT INTO profile(Username) VALUES('$username')";
         if(mysqli_query($conn, $sqlInsertProfile)){
            if(mysqli_query($conn, $sqlInsertUser)){
               $_SESSION['User'] = $email;
               header('Location: index.php');
               exit();
            }
         }  
      }
      $_SESSION['errmessage'] = "User already exists";
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
   $sqlSelectInnerJoin = "SELECT profile.Username, profile.Bio, profile.Picture FROM profile INNER JOIN users ON profile.Username = users.Username WHERE users.Email='$email'";
   $result = mysqli_query($conn, $sqlSelectInnerJoin);
   return mysqli_fetch_array($result, MYSQLI_ASSOC);

}

function searchUser($conn, $username){
   $sqlSelectProfile = "SELECT Username,Bio,Picture FROM profile WHERE Username='$username'";
   $result = mysqli_query($conn, $sqlSelectProfile);
   return mysqli_fetch_array($result, MYSQLI_ASSOC);
}

//TEST METHOD
function saveBookReview($conn, $email, $isbn, $title, $releaseDate, $description, $author, $authorDOB, $totalPages, $pagesRead, $review, $rating, $picture){
   $releaseDateSQL=date("Y-m-d",strtotime($releaseDate));
   $authorDOBSQL=date("Y-m-d",strtotime($authorDOB));


   $sqlInsertAuthor = "INSERT INTO author(Name, DOB) VALUES('$author', '$authorDOBSQL')";
   mysqli_query($conn, $sqlInsertAuthor);

   $sqlInsertBook = "INSERT INTO books(ISBN, Author, Title, DateReleased, Description, Picture) VALUES('$isbn','$author','$title','$releaseDateSQL','$description','$picture')";
   mysqli_query($conn, $sqlInsertBook);

   $sqlInsertPages = "INSERT INTO pages(Page,TotalPages) VALUES('$totalPages','$pagesRead')";
   if (mysqli_query($conn, $sqlInsertPages)){
      $pageID = mysqli_insert_id($conn);
   }

   $sqlInsertReview = "INSERT INTO reviews(ISBN,Review,Rating,Visible) VALUES('$isbn','$review','$rating',true)";
   if (mysqli_query($conn, $sqlInsertReview)) {
      $reviewID = mysqli_insert_id($conn);
   }

   $sqlInsertUserBooks = "INSERT INTO usersbooks(ReviewID, Email, PageID) VALUES('$reviewID','$email','$pageID')";
   mysqli_query($conn, $sqlInsertUserBooks);

   header('Location: /coursework/profile/index.php');
   exit();
   
}

//TEST METHOD
function getAllUserBookReviews($conn, $username){
   $userBooksArray = array();
   $bookReviewArray = array();
   $bookDetailsArray = array();
   $pagesDetailsArray = array();

   $sqlSelectUserBooks = "SELECT usersbooks.ID, usersbooks.ReviewID, usersbooks.Email, usersbooks.PageID FROM users INNER JOIN usersbooks
                           ON users.Email = usersbooks.Email WHERE users.Username='$username'";
   $resultUserBooks = mysqli_query($conn, $sqlSelectUserBooks);

   while($row = mysqli_fetch_assoc($resultUserBooks)) {
      $userBooksArray[] = $row;
   }

   //try for default for loop with index number if this loop doesnt work
   foreach($userBooksArray as $i => $item){
      $reviewID = $userBooksArray[$i]['ReviewID'];
      $pageID = $userBooksArray[$i]['PageID'];
      $sqlSelectBookReview = "SELECT ISBN, Review, Rating, Visible FROM reviews WHERE ID='$reviewID'";
      $resultBookReview = mysqli_query($conn, $sqlSelectBookReview);

      //should only be one row per review id so this only goes through once
      //used to map the data booksReviewArray
      while($row = mysqli_fetch_assoc($resultBookReview)) {
         $bookReviewArray[$i]['ISBN'] = $row['ISBN'];
         $bookReviewArray[$i]['Review'] = $row['Review'];
         $bookReviewArray[$i]['Rating'] = $row['Rating'];
         $bookReviewArray[$i]['Visible'] = $row['Visible'];
      }

      $isbn = $bookReviewArray[$i]['ISBN'];
      $sqlSelectBookDetails = "SELECT Author, Title, DateReleased, Description, Picture FROM books WHERE ISBN='$isbn'";
      $resultBookDetails = mysqli_query($conn, $sqlSelectBookDetails);

      //should only be one row per review id so this only goes through once
      while($row = mysqli_fetch_assoc($resultBookDetails)) {
         $bookDetailsArray[$i]['Author'] = $row['Author'];
         $bookDetailsArray[$i]['Title'] = $row['Title'];
         $bookDetailsArray[$i]['DateReleased'] = $row['DateReleased'];
         $bookDetailsArray[$i]['Description'] = $row['Description'];
         $bookDetailsArray[$i]['Picture'] = $row['Picture'];
      }

      $sqlSelectPageDetails = "SELECT Page, TotalPages FROM pages WHERE ID='$pageID'";
      $resultPagesDetails = mysqli_query($conn, $sqlSelectPageDetails);

      while($row = mysqli_fetch_assoc($resultPagesDetails)){
         $pagesDetailsArray[$i]['Page'] = $row['Page'];
         $pagesDetailsArray[$i]['TotalPages'] = $row['TotalPages'];
      }
   }

   return array($userBooksArray, $bookReviewArray, $bookDetailsArray, $pagesDetailsArray);
}

//check why this is not working
function getOneUserBookReview($conn, $email, $id){
   //Email in sql statement that comes from the user session to makes sure no other user can edit the post without them being in the same session
   $sqlSelectUserBooks = "SELECT ReviewID, PageID FROM usersbooks WHERE ID='$id' AND Email='$email'";

   $resultUserBooks = mysqli_query($conn, $sqlSelectUserBooks);
   $usersBooksArray = mysqli_fetch_array($resultUserBooks, MYSQLI_ASSOC);

   $reviewID = $usersBooksArray['ReviewID'];
   $pageID = $usersBooksArray['PageID'];

   $sqlSelectPages = "SELECT Page, TotalPages FROM pages WHERE ID='$pageID'";

   $resultPages = mysqli_query($conn, $sqlSelectPages);
   $pagesArray = mysqli_fetch_array($resultPages, MYSQLI_ASSOC);


   $sqlSelectInnerJoin = "SELECT reviews.ISBN, reviews.Review, reviews.Rating, reviews.Visible, books.Author, books.Title, books.DateReleased, 
                           books.Description, author.DOB FROM reviews INNER JOIN books ON reviews.ISBN = books.ISBN
                           INNER JOIN author ON author.Name = books.Author WHERE reviews.ID='$reviewID'";

   //error is  here somewhere CHEEEEEECKKKKKKKKKKK
   $resultInnerJoin = mysqli_query($conn, $sqlSelectInnerJoin);
   $bookDataArray = mysqli_fetch_array($resultInnerJoin, MYSQLI_ASSOC);



   return array_merge($bookDataArray, $pagesArray);


}

/**
 * Why it only updates pages and reviews is so that it makes it easier to manage books database and that the only thing that
 * needs to be editted in an existing book is the page count and the review
 */
function updateUserBookReview($conn, $email, $id, $pagesRead, $review, $rating){
   $sqlSelectUserBooks = "SELECT ReviewID, PageID FROM usersbooks WHERE ID='$id' AND Email='$email'";

   $resultUserBooks = mysqli_query($conn, $sqlSelectUserBooks);
   $usersBooksArray = mysqli_fetch_array($resultUserBooks, MYSQLI_ASSOC);

   $reviewID = $usersBooksArray['ReviewID'];
   $pageID = $usersBooksArray['PageID'];

   //This only updates pages of book, if it becomes the same then the review option is opened up
   $sqlUpdatePages = "UPDATE pages SET Page='$pagesRead' WHERE ID='$pageID'";
   mysqli_query($conn, $sqlUpdatePages);

   $sqlUpdateReview = "UPDATE reviews SET Review='$review', Rating='$rating' WHERE ID='$reviewID'";
   mysqli_query($conn, $sqlUpdateReview);

}

//change maybe so no return
function checkPagesReadAndTotalPagesEqual($conn, $id, $email){
   $sqlSelectUserBooks = "SELECT PageID FROM usersbooks WHERE ID='$id' AND Email='$email'";

   $resultUserBooks = mysqli_query($conn, $sqlSelectUserBooks);
   $usersBooksArray = mysqli_fetch_array($resultUserBooks, MYSQLI_ASSOC);

   $pageID = $usersBooksArray['PageID'];

   $sqlSelectPages = "SELECT Page, TotalPages FROM pages WHERE ID='$pageID'";
   $resultPages = mysqli_query($conn, $sqlSelectPages);
   $pagesArray = mysqli_fetch_array($resultPages, MYSQLI_ASSOC);

   return (int) $pagesArray['Page'] == (int) $pagesArray['TotalPages'];

}

function deleteBooksReview(){

}




?>