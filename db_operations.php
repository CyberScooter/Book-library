<?php


//LOGIN/REGISTER USER RELATED OPERATIONS:

function registerUser($conn, $email, $password, $passwordConfirmation, $username){
   if($password == $passwordConfirmation){
      //FIXED                                                                                                        //checks if email is same but that the profile username for that email is not the same as the one entered (this selects no records as user not found)
      $sqlSelectInnerJoin = "SELECT users.Email, users.Username FROM users INNER JOIN profile ON profile.Username = users.Username WHERE users.Email='$email' AND profile.Username != '$username'";
      $result = mysqli_query($conn, $sqlSelectInnerJoin);
      if(mysqli_num_rows($result) == 0){
         $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
         $sqlInsertUser = "INSERT INTO users(Email,Username,Hash) VALUES('$email','$username','$hashedPassword')";
         $sqlInsertProfile = "INSERT INTO profile(Username) VALUES('$username')";
         if(mysqli_query($conn, $sqlInsertProfile)){
            if(mysqli_query($conn, $sqlInsertUser)){
               insertStandardUser($conn, $email); 
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

function insertStandardUser($conn, $email){
   $sqlInsertStandard = "INSERT INTO standard(Email, BooksLimit, PrivatePosts) VALUES('$email', 5, 2)";
   $result = mysqli_query($conn, $sqlInsertStandard);
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

//=================================================================================================================================================================

//BOOK/BOOK REVIEWS RELATED OPERATIONS:

function checkExistingBook($conn, $email, $isbn){
   $sqlSelectBook = "SELECT ISBN FROM books WHERE ISBN='$isbn'";
   $resultBook = mysqli_query($conn, $sqlSelectBook);
   if(mysqli_num_rows($resultBook) != 0){
      return true;
   }
   return false;
}

function checkExistingAuthor($conn, $email, $author){
   $sqlSelectAuthor = "SELECT Author FROM author WHERE Name='$author'";
   $resultAuthor = mysqli_query($conn, $sqlSelectAuthor);
   if(mysqli_num_rows($resultAuthor) != 0){
      return true;
   }
   return false;
}

function checkExistingReviewFromUser($conn, $email, $isbn){
   $sqlSelectReviewsISBN = "SELECT reviews.ISBN FROM usersbooks INNER JOIN reviews ON usersbooks.ReviewID = reviews.ID WHERE reviews.ISBN='$isbn' AND usersbooks.Email='$email'";
   $resultReviewsISBN = mysqli_query($conn, $sqlSelectReviewsISBN);
   if(mysqli_num_rows($resultReviewsISBN) != 0){
      return true;
   }
   return false;
}

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

function saveBookReview($conn, $email, $isbn, $title, $releaseDate, $description, $author, $authorDOB, $totalPages, $pagesRead, $review, $rating, $picture, $visible){
   $releaseDateSQL=date("Y-m-d",strtotime($releaseDate));
   $authorDOBSQL=date("Y-m-d",strtotime($authorDOB));

   if(!checkExistingAuthor($conn, $email, $author)){
      $sqlInsertAuthor = "INSERT INTO author(Name, DOB) VALUES('$author', '$authorDOBSQL')";
      mysqli_query($conn, $sqlInsertAuthor);
   }

   if(!checkExistingBook($conn, $email, $isbn)){
      $sqlInsertBook = "INSERT INTO books(ISBN, Author, Title, DateReleased, Description, Picture) VALUES('$isbn','$author','$title','$releaseDateSQL','$description','$picture')";
      mysqli_query($conn, $sqlInsertBook);
   }

   if(!checkExistingReviewFromUser($conn, $email, $isbn)){
      $sqlInsertPages = "INSERT INTO pages(TotalPages, Page) VALUES('$totalPages','$pagesRead')";
      if (mysqli_query($conn, $sqlInsertPages)){
         $pageID = mysqli_insert_id($conn);
      }
   
      $sqlInsertReview = "INSERT INTO reviews(ISBN,Review,Rating,Visible) VALUES('$isbn','$review','$rating','$visible')";
      if (mysqli_query($conn, $sqlInsertReview)) {
         $reviewID = mysqli_insert_id($conn);
      }
   
      $sqlInsertUserBooks = "INSERT INTO usersbooks(ReviewID, Email, PageID) VALUES('$reviewID','$email','$pageID')";
      mysqli_query($conn, $sqlInsertUserBooks);
   }else{
      $_SESSION['errmessage'] = "Book already exists!";
   }
   
}

function getAllUserBookReviews($conn, $username){
   $booksData = array();

   $sqlSelectUserBooks = "SELECT usersbooks.ID, usersbooks.ReviewID, usersbooks.Email, usersbooks.PageID, usersbooks.created_at FROM users INNER JOIN usersbooks
                           ON users.Email = usersbooks.Email WHERE users.Username='$username'";
   $resultUserBooks = mysqli_query($conn, $sqlSelectUserBooks);

   while($row = mysqli_fetch_assoc($resultUserBooks)) {
      $booksData[] = $row;
   }

   //try for default for loop with index number if this loop doesnt work
   foreach($booksData as $i => $item){
      $reviewID = $booksData[$i]['ReviewID'];
      $pageID = $booksData[$i]['PageID'];
      $sqlSelectBookReview = "SELECT ISBN, Review, Rating, Visible FROM reviews WHERE ID='$reviewID'";
      $resultBookReview = mysqli_query($conn, $sqlSelectBookReview);

      //should only be one row per review id so this only goes through once
      //used to map the data booksReviewArray
      while($row = mysqli_fetch_assoc($resultBookReview)) {
         $booksData[$i]['ISBN'] = $row['ISBN'];
         $booksData[$i]['Review'] = $row['Review'];
         $booksData[$i]['Rating'] = $row['Rating'];
         $booksData[$i]['Visible'] = $row['Visible'];
      }

      $isbn = $booksData[$i]['ISBN'];
      $sqlSelectBookDetails = "SELECT Author, Title, DateReleased, Description, Picture FROM books WHERE ISBN='$isbn'";
      $resultBookDetails = mysqli_query($conn, $sqlSelectBookDetails);

      //should only be one row per review id so this only goes through once
      while($row = mysqli_fetch_assoc($resultBookDetails)) {
         $booksData[$i]['Author'] = $row['Author'];
         $booksData[$i]['Title'] = $row['Title'];
         $booksData[$i]['DateReleased'] = $row['DateReleased'];
         $booksData[$i]['Description'] = $row['Description'];
         $booksData[$i]['Picture'] = $row['Picture'];
      }

      $sqlSelectPageDetails = "SELECT Page, TotalPages FROM pages WHERE ID='$pageID'";
      $resultPagesDetails = mysqli_query($conn, $sqlSelectPageDetails);

      while($row = mysqli_fetch_assoc($resultPagesDetails)){
         $booksData[$i]['Page'] = $row['Page'];
         $booksData[$i]['TotalPages'] = $row['TotalPages'];
      }

   }

   return $booksData;
}

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
function updateUserBookReview($conn, $email, $id, $pagesRead, $review, $rating, $visible){
   $sqlSelectUserBooks = "SELECT ReviewID, PageID FROM usersbooks WHERE ID='$id' AND Email='$email'";

   $resultUserBooks = mysqli_query($conn, $sqlSelectUserBooks);
   $usersBooksArray = mysqli_fetch_array($resultUserBooks, MYSQLI_ASSOC);

   $reviewID = $usersBooksArray['ReviewID'];
   $pageID = $usersBooksArray['PageID'];

   //This only updates pages of book, if it becomes the same then the review option is opened up
   $sqlUpdatePages = "UPDATE pages SET Page='$pagesRead' WHERE ID='$pageID'";
   mysqli_query($conn, $sqlUpdatePages);

   $sqlUpdateReview = "UPDATE reviews SET Review='$review', Rating='$rating', Visible='$visible' WHERE ID='$reviewID'";
   mysqli_query($conn, $sqlUpdateReview);

}

function deleteUserBookReview($conn, $email, $id, $isbn, $author){
   $sqlSelectISBNReviews = "SELECT * FROM reviews WHERE ISBN = '$isbn'";
   if($result = mysqli_query($conn, $sqlSelectISBNReviews)){

         $sqlSelectUsersBook = "SELECT usersbooks.ID, usersbooks.PageID FROM usersbooks WHERE usersbooks.ReviewID='$id'";


         if($resultSelectUsersBook = mysqli_query($conn, $sqlSelectUsersBook)){
            $userBooksArray = mysqli_fetch_array($resultSelectUsersBook, MYSQLI_ASSOC);
            //check if this below works 
            $userBooksID = $userBooksArray['ID'];
            $pagesID = $userBooksArray['PageID'];

            $sqlSelectAllPosts = "SELECT ID, CommentID FROM posts WHERE posts.ReviewID='$id'";

            if($resultSqlSelectAllPosts = mysqli_query($conn, $sqlSelectAllPosts)){
               while($row = mysqli_fetch_assoc($resultSqlSelectAllPosts)){
                  $postsID = $row['ID'];
                  $commentID = $row['CommentID'];

                  $sqlDeletePostsID = "DELETE FROM posts WHERE ID='$postsID'";
                  mysqli_query($conn, $sqlDeletePostsID);

                  $sqlDeleteCommentID = "DELETE FROM comments WHERE ID='$commentID'";
                  mysqli_query($conn, $sqlDeleteCommentID);
               }
               
            }

            $sqlDeleteUserBookID = "DELETE FROM usersbooks WHERE ID='$userBooksID'";
            mysqli_query($conn, $sqlDeleteUserBookID);

            $sqlDeleteReviewID = "DELETE FROM reviews WHERE ID='$id'";
            mysqli_query($conn, $sqlDeleteReviewID);

            $sqlDeletePagesID = "DELETE FROM pages WHERE ID='$pagesID'";
            mysqli_query($conn, $sqlDeletePagesID);

         }

      if(mysqli_num_rows($result) == 1){

         $sqlSelectAuthors = "SELECT * FROM books WHERE Author='$author'";
         if($resultAuthor = mysqli_query($conn, $sqlSelectAuthors)){
            if(mysqli_num_rows($resultAuthor) > 1){
               $sqlDeleteBook = "DELETE FROM books WHERE ISBN='$isbn'";
               mysqli_query($conn, $sqlDeleteBook);
            }
            $sqlDeleteBook = "DELETE FROM books WHERE ISBN='$isbn'";
            mysqli_query($conn, $sqlDeleteBook);
            $sqlDeleteAuthor = "DELETE FROM author WHERE Name='$author'";
            mysqli_query($conn, $sqlDeleteAuthor);
         }

      }

      $reviewArray = mysqli_fetch_array($result, MYSQLI_ASSOC);
      if(checkIfStandardUser($conn, $email) && !$reviewArray['visible']){
         incrementPrivatePostReviews($conn, $email);
      }

      if(checkIfStandardUser($conn, $email)){
         incrementStandardLimitReviews($conn, $email);
      }
   }

}

//=================================================================================================================================================================
//BOOK COMMENTS OPERATIONS:

function selectCommentsFromReview($conn, $reviewID){
   $user = array();
   $comments = array();
   $dateCreated = array();
   $commentIDs = array();

   $sqlSelectCommentID = "SELECT User, CommentID, created_at FROM posts WHERE ReviewID='$reviewID'";
   $resultCommentID = mysqli_query($conn, $sqlSelectCommentID);

   while($row = mysqli_fetch_assoc($resultCommentID)){
      $username = getUsernameFromUsersTable($conn, $row['User']);
      $commentID = $row['CommentID'];
      $created_at = $row['created_at'];
      $sqlSelectComment = "SELECT Comment FROM comments WHERE ID='$commentID'";
      $resultComment = mysqli_query($conn, $sqlSelectComment);
      while($row = mysqli_fetch_assoc($resultComment)){
         array_push($comments, $row['Comment']);
         array_push($dateCreated, $created_at);
         array_push($user, $username);
         array_push($commentIDs, $commentID);
      }

   }

   return array($comments, $dateCreated, $user, $commentIDs);
}

function insertNewComment($conn, $email, $reviewID, $comment){
   $sqlInsertComment = "INSERT INTO comments(Comment) VALUES('$comment')";
   if(mysqli_query($conn, $sqlInsertComment)){
      $id = mysqli_insert_id($conn);
   }

   $sqlInsertToPosts = "INSERT INTO posts(User, CommentID, ReviewID) VALUES('$email','$id','$reviewID')";
   $result = mysqli_query($conn, $sqlInsertToPosts);
}

function deleteComment($conn, $email, $commentID){
   // Getting primary key directly from posts table of a specific comment so that It can be deleted directly which in result deletes the whole record
   $sqlSelectPostsID = "SELECT ID FROM posts WHERE CommentID='$commentID'";
   $resultSelectPostsID = mysqli_query($conn, $sqlSelectPostsID);
   $postsIDArray = mysqli_fetch_array($resultSelectPostsID, MYSQLI_ASSOC);
   $postID = $postsIDArray['ID'];

   $sqlDeletePostID = "DELETE FROM posts WHERE ID='$postID'";
   $resultDeletePostID = mysqli_query($conn, $sqlDeletePostID);

   //deleting comment from comments table
   $sqlDeleteComment =  "DELETE FROM comments WHERE ID='$commentID'";
   $resultDeleteComment = mysqli_query($conn, $sqlDeleteComment);

}

//=================================================================================================================================================================
//USER OPERATIONS

function getUsernameFromUsersTable($conn, $email){
   $sqlSelect = "SELECT Username FROM users WHERE Email='$email'";
   $result = mysqli_query($conn, $sqlSelect);
   $userArray = mysqli_fetch_array($result, MYSQLI_ASSOC);
   return $userArray['Username'];
}

function updateProfile($conn, $email, $dataArray, $backgroundImageURL, $badgeURL, $premium){
   if($premium){
      $sqlUpdatePremium = "UPDATE premium SET BadgeURL='$badgeURL',BackgroundURL='$backgroundImageURL' WHERE Email='$email'";
      if(mysqli_query($conn, $sqlUpdatePremium)){
         $_SESSION['bg-image'] = $backgroundImageURL;
         header('Location: index.php');
         exit();
      }
   }
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
   $badgeData = array();
   $sqlSelectInnerJoin = "SELECT profile.Username, profile.Bio, profile.Picture FROM profile INNER JOIN users ON profile.Username = users.Username WHERE users.Email='$email'";
   if(checkIfPremiumUser($conn, $email)){
      $sqlSelectBadge = "SELECT BadgeURL FROM premium WHERE Email='$email'";
      $resultBadge = mysqli_query($conn, $sqlSelectBadge);
      $badgeData = mysqli_fetch_array($resultBadge, MYSQLI_ASSOC);
   }
   $resultInnerJoin = mysqli_query($conn, $sqlSelectInnerJoin);
   $profileData = mysqli_fetch_array($resultInnerJoin, MYSQLI_ASSOC);
   
   return array_merge($badgeData, $profileData);   

}

function searchUser($conn, $username){
   $sqlSelectProfile = "SELECT Username,Bio,Picture FROM profile WHERE Username='$username'";
   $result = mysqli_query($conn, $sqlSelectProfile);
   return mysqli_fetch_array($result, MYSQLI_ASSOC);
}

//=================================================================================================================================================================
//PREMIUM USER OPERATIONS:

function upgradeToPremium($conn, $email){
   $sqlInsertPremium = "INSERT INTO premium(Email) VALUES('$email')";
   $resultInsert = mysqli_query($conn, $sqlInsertPremium);

   $sqlDeleteStandard = "DELETE FROM standard WHERE Email='$email'";
   $resultDelete = mysqli_query($conn , $sqlDeleteStandard);
}

function checkIfPremiumUser($conn, $email){
   $sqlSelectPremium = "SELECT Email FROM premium WHERE Email='$email'";
   $result = mysqli_query($conn, $sqlSelectPremium);
   if(mysqli_num_rows($result) != 0){
      return true;
   }
   return false;
}

function setBackgroundURL($conn, $email){
   $sqlSelectBackground = "SELECT BackgroundURL FROM premium WHERE Email='$email'";
   if($result = mysqli_query($conn, $sqlSelectBackground)){
      $backgroundArray = mysqli_fetch_array($result, MYSQLI_ASSOC);
      $_SESSION['bg-image'] = $backgroundArray['BackgroundURL'];
   }
}

//=================================================================================================================================================================
//STANDARD USER OPERATIONS

function checkIfStandardUser($conn, $email){
   $sqlSelectStandard = "SELECT Email FROM standard WHERE Email='$email'";
   $result = mysqli_query($conn, $sqlSelectStandard);
   if(mysqli_num_rows($result) != 0){
      return true;
   }
   return false;
}

function incrementStandardLimitReviews($conn, $email){
   $sqlSelectStandard = "SELECT BooksLimit FROM standard WHERE Email='$email'";
   $resultSelect = mysqli_query($conn, $sqlSelectStandard);
   $selectArray = mysqli_fetch_array($resultSelect, MYSQLI_ASSOC);
   $booksLimit = (int) $selectArray['BooksLimit'] + 1;
   if($booksLimit <= 5){
      $sqlUpdateStandard = "UPDATE standard SET BooksLimit='$booksLimit' WHERE Email = '$email'";
      $resultUpdate = mysqli_query($conn, $sqlUpdateStandard);
   }else{
      header('Location: /coursework/profile/index.php');
      $_SESSION['errmessage'] = "Book limit reached";
      exit();   
   }
}

function decrementStandardLimitReviews($conn, $email){
   $sqlSelectStandard = "SELECT BooksLimit FROM standard WHERE Email='$email'";
   $resultSelect = mysqli_query($conn, $sqlSelectStandard);
   $selectArray = mysqli_fetch_array($resultSelect, MYSQLI_ASSOC);
   $booksLimit = (int) $selectArray['BooksLimit'] - 1;
   if($booksLimit >= 0){
      $sqlUpdateStandard = "UPDATE standard SET BooksLimit='$booksLimit' WHERE Email = '$email'";
      $resultUpdate = mysqli_query($conn, $sqlUpdateStandard);
   }else{
      header('Location: /coursework/profile/index.php');
      $_SESSION['errmessage'] = "Book limit reached";
      exit();   
   }
}

function decrementPrivatePostReviews($conn, $email){
   $sqlSelectStandard = "SELECT PrivatePosts FROM standard WHERE Email='$email'";
   $resultSelect = mysqli_query($conn, $sqlSelectStandard);
   $selectArray = mysqli_fetch_array($resultSelect, MYSQLI_ASSOC);
   $privatePosts = (int) $selectArray['PrivatePosts'] - 1;
   if($privatePosts >= 0){
      $sqlUpdateStandard = "UPDATE standard SET PrivatePosts='$privatePosts' WHERE Email = '$email'";
      $resultUpdate = mysqli_query($conn, $sqlUpdateStandard);
   }else{
      $_SESSION['errmessage'] = "Private posts limit reached";
      header('Location: /coursework/profile/index.php');
      exit();   
   }
}

function incrementPrivatePostReviews($conn, $email){
   $sqlSelectStandard = "SELECT PrivatePosts FROM standard WHERE Email='$email'";
   $resultSelect = mysqli_query($conn, $sqlSelectStandard);
   $selectArray = mysqli_fetch_array($resultSelect, MYSQLI_ASSOC);
   $privatePosts = (int) $selectArray['PrivatePosts'] + 1;
   $sqlUpdateStandard = "UPDATE standard SET PrivatePosts='$privatePosts' WHERE Email = '$email'";
   $resultUpdate = mysqli_query($conn, $sqlUpdateStandard);
}

function checkStandardPrivatePosts($conn, $email){
   $sqlSelectStandard = "SELECT PrivatePosts FROM standard WHERE Email='$email'";
   $resultSelect = mysqli_query($conn, $sqlSelectStandard);
   $selectArray = mysqli_fetch_array($resultSelect, MYSQLI_ASSOC);
   return (int) $selectArray['PrivatePosts'];
}

function checkStandardBooksLimit($conn, $email){
   $sqlSelectStandard = "SELECT BooksLimit FROM standard WHERE Email='$email'";
   $resultSelect = mysqli_query($conn, $sqlSelectStandard);
   $selectArray = mysqli_fetch_array($resultSelect, MYSQLI_ASSOC);
   return ((int) $selectArray['BooksLimit'] != 0);
}

//=================================================================================================================================================================
//ERROR HANDLING

function errorRedirect(){
   $_SESSION['errmessage'] = 'Error description: ' . mysqli_error($conn);
   header('Location: /coursework/index.php');
}






?>