<?php

//LOGIN/REGISTER USER RELATED OPERATIONS:

function registerUser($conn, $email, $password, $passwordConfirmation, $username){
   $safeEmail = mysqli_real_escape_string($conn, $email);
   $safeUsername = mysqli_real_escape_string($conn, $username);
   if($password == $passwordConfirmation){                                                                                           //checks if email is same but that the profile username for that email is not the same as the one entered (this selects no records as user not found)
      $sqlSelectInnerJoin = "SELECT users.Email, users.Username FROM users INNER JOIN profile ON profile.Username = users.Username WHERE users.Email='$safeEmail' AND profile.Username != '$safeUsername'";
      $result = mysqli_query($conn, $sqlSelectInnerJoin);
      if(mysqli_num_rows($result) == 0){
         $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
         $sqlInsertUser = "INSERT INTO users(Email,Username,Hash) VALUES('$safeEmail','$safeUsername','$hashedPassword')";
         $sqlInsertProfile = "INSERT INTO profile(Username) VALUES('$safeUsername')";
         if(mysqli_query($conn, $sqlInsertProfile)){
            if(mysqli_query($conn, $sqlInsertUser)){
               insertStandardUser($conn, $safeEmail); 
               $_SESSION['User'] = $safeEmail;
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
   $safeEmail = mysqli_real_escape_string($conn, $email);
   $sqlSelect = "SELECT Hash FROM users WHERE Email='$safeEmail'";
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
   $sqlSelectReviewsISBN = "SELECT reviews.ISBN FROM users_reviews INNER JOIN reviews ON users_reviews.ReviewID = reviews.ID WHERE reviews.ISBN='$isbn' AND users_reviews.Email='$email'";
   $resultReviewsISBN = mysqli_query($conn, $sqlSelectReviewsISBN);
   if(mysqli_num_rows($resultReviewsISBN) != 0){
      return true;
   }
   return false;
}

function checkPagesReadAndTotalPagesEqual($conn, $id, $email){
   $safeReviewID = mysqli_real_escape_string($conn, $id);
   $sqlSelectUsersReviews = "SELECT PageID FROM users_reviews WHERE ID='$safeReviewID' AND Email='$email'";

   $resultUsersReviews = mysqli_query($conn, $sqlSelectUsersReviews);
   $usersReviewsArray = mysqli_fetch_array($resultUsersReviews, MYSQLI_ASSOC);

   $pageID = $usersReviewsArray['PageID'];

   $sqlSelectPages = "SELECT Page, TotalPages FROM pages WHERE ID='$pageID'";
   $resultPages = mysqli_query($conn, $sqlSelectPages);
   $pagesArray = mysqli_fetch_array($resultPages, MYSQLI_ASSOC);


   return (int) $pagesArray['Page'] == (int) $pagesArray['TotalPages'];

}

function saveBookReview($conn, $email, $isbn, $title, $releaseDate, $description, $author, $authorDOB, $totalPages, $pagesRead, $review, $rating, $picture, $visible){
   $safeReleaseDate = mysqli_real_escape_string($conn, $releaseDate);
   $safeAuthorDOB = mysqli_real_escape_string($conn, $authorDOB);
   $safeTitle = mysqli_real_escape_string($conn, $title);
   $safeDescription = mysqli_real_escape_string($conn, $description);
   $safePicture = mysqli_real_escape_string($conn, $picture);
   $safeISBN = mysqli_real_escape_string($conn, $isbn);
   $safeTotalPages = mysqli_real_escape_string($conn, $totalPages);
   $safePagesRead = mysqli_real_escape_string($conn, $pagesRead);
   $safeReview = mysqli_real_escape_string($conn, $review);
   
   $releaseDateSQL=date("Y-m-d",strtotime($safeReleaseDate));
   $authorDOBSQL=date("Y-m-d",strtotime($safeAuthorDOB));

   $safeAuthor = mysqli_real_escape_string($conn, $author);

   if(!checkExistingAuthor($conn, $email, $safeAuthor)){
      $sqlInsertAuthor = "INSERT INTO author(Name, DOB) VALUES('$safeAuthor', '$authorDOBSQL')";
      mysqli_query($conn, $sqlInsertAuthor);
   }

   if(!checkExistingBook($conn, $email, $safeISBN)){
      $sqlInsertBook = "INSERT INTO books(ISBN, Author, Title, DateReleased, Description, Picture) VALUES('$safeISBN','$safeAuthor','$safeTitle','$releaseDateSQL','$safeDescription','$safePicture')";
      mysqli_query($conn, $sqlInsertBook);
   }

   if(!checkExistingReviewFromUser($conn, $email, $safeISBN)){
      $sqlInsertPages = "INSERT INTO pages(TotalPages, Page) VALUES('$safeTotalPages','$safePagesRead')";
      if (mysqli_query($conn, $sqlInsertPages)){
         $pageID = mysqli_insert_id($conn);
      }
   
      $sqlInsertReview = "INSERT INTO reviews(ISBN,Review,Rating,Visible) VALUES('$safeISBN','$safeReview','$rating','$visible')";
      if (mysqli_query($conn, $sqlInsertReview)) {
         $reviewID = mysqli_insert_id($conn);
      }
   
      $sqlInsertUsersReviews = "INSERT INTO users_reviews(ReviewID, Email, PageID) VALUES('$reviewID','$email','$pageID')";
      mysqli_query($conn, $sqlInsertUsersReviews);

   }else{
      $_SESSION['errmessage'] = "Book already exists!";
   }
   
}

function getAllUserBookReviews($conn, $username){
   $safeUsername = mysqli_real_escape_string($conn, $username);
   $booksData = array();

   $sqlSelectUsersReviews = "SELECT users_reviews.ID, users_reviews.ReviewID, users_reviews.Email, users_reviews.PageID, users_reviews.created_at FROM users INNER JOIN users_reviews
                           ON users.Email = users_reviews.Email WHERE users.Username='$safeUsername'";
   $resultUserReviews = mysqli_query($conn, $sqlSelectUsersReviews);

   while($row = mysqli_fetch_assoc($resultUserReviews)) {
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
   $safeID = mysqli_real_escape_string($conn, $id);
   //Email in sql statement that comes from the user session to makes sure no other user can edit the post without them being in the same session
   $sqlSelectUsersReviews = "SELECT ReviewID, PageID FROM users_reviews WHERE ID='$safeID' AND Email='$email'";

   if($resultUsersReviews = mysqli_query($conn, $sqlSelectUsersReviews)){
      //Redirects to books index page if another invalid index tries to be accessed from url to edit page
      if(mysqli_num_rows($resultUsersReviews) == 0){
         
         header('Location: /books/index.php');
         exit();
      }
      $usersReviewsArray = mysqli_fetch_array($resultUsersReviews, MYSQLI_ASSOC);
   }

   $reviewID = $usersReviewsArray['ReviewID'];
   $pageID = $usersReviewsArray['PageID'];

   $sqlSelectPages = "SELECT Page, TotalPages FROM pages WHERE ID='$pageID'";

   $resultPages = mysqli_query($conn, $sqlSelectPages);
   $pagesArray = mysqli_fetch_array($resultPages, MYSQLI_ASSOC);


   $sqlSelectInnerJoin = "SELECT reviews.ISBN, reviews.Review, reviews.Rating, reviews.Visible, books.Author, books.Title, books.DateReleased, 
                           books.Description, author.DOB FROM reviews INNER JOIN books ON reviews.ISBN = books.ISBN
                           INNER JOIN author ON author.Name = books.Author WHERE reviews.ID='$reviewID'";

   $resultInnerJoin = mysqli_query($conn, $sqlSelectInnerJoin);
   $bookDataArray = mysqli_fetch_array($resultInnerJoin, MYSQLI_ASSOC);

   return array_merge($bookDataArray, $pagesArray);

}

/**
 * Why it only updates pages and reviews is so that it makes it easier to manage books database and that the only thing that
 * needs to be editted in an existing book is the page count and the review
 */
function updateUserBookReview($conn, $email, $id, $pagesRead, $review, $rating, $visible){
   $safePagesRead = mysqli_real_escape_string($conn, $pagesRead);
   $safeReview = mysqli_real_escape_string($conn, $review);

   $sqlSelectUsersReviews = "SELECT ReviewID, PageID FROM users_reviews WHERE ID='$id' AND Email='$email'";

   if($resultUsersReviews = mysqli_query($conn, $sqlSelectUsersReviews)){
      $usersReviewsArray = mysqli_fetch_array($resultUsersReviews, MYSQLI_ASSOC);
   }

   $reviewID = $usersReviewsArray['ReviewID'];
   $pageID = $usersReviewsArray['PageID'];

   //This only updates pages of book, if it becomes the same then the review option is opened up
   $sqlUpdatePages = "UPDATE pages SET Page='$safePagesRead' WHERE ID='$pageID'";
   mysqli_query($conn, $sqlUpdatePages);

   $sqlUpdateReview = "UPDATE reviews SET Review='$safeReview', Rating='$rating', Visible='$visible' WHERE ID='$reviewID'";
   mysqli_query($conn, $sqlUpdateReview);

}

function deleteUserBookReview($conn, $email, $id, $isbn, $author){
   $sqlSelectISBNReviews = "SELECT * FROM reviews WHERE ISBN = '$isbn'";
   if($result = mysqli_query($conn, $sqlSelectISBNReviews)){

      $sqlSelectUsersReviews = "SELECT users_reviews.ID, users_reviews.PageID FROM users_reviews WHERE users_reviews.ReviewID='$id'";

      if($resultSelectUsersBook = mysqli_query($conn, $sqlSelectUsersReviews)){
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

         $sqlDeleteUsersReviewsID = "DELETE FROM users_reviews WHERE ID='$userBooksID'";
         mysqli_query($conn, $sqlDeleteUsersReviewsID);

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
   $safeComment = mysqli_real_escape_string($conn, $comment);
   $sqlInsertComment = "INSERT INTO comments(Comment) VALUES('$safeComment')";
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
   $safeBackgroundImageURL = mysqli_real_escape_string($conn, $backgroundImageURL);
   $safeBadgeURL = mysqli_real_escape_string($conn, $badgeURL);
   
   if($premium){
      $sqlUpdatePremium = "UPDATE premium SET BadgeURL='$safeBadgeURL',BackgroundURL='$safeBackgroundImageURL' WHERE Email='$email'";
      if(mysqli_query($conn, $sqlUpdatePremium)){
         $_SESSION['bg-image'] = $safeBackgroundImageURL;
         
         header('Location: index.php');
         exit();
      }
   }
   $username = getUsernameFromUsersTable($conn, $email);
   $bio = $dataArray['Bio'];
   $safeBio = mysqli_real_escape_string($conn, $bio);

   $picture = $dataArray['Picture'];
   $safePicture = mysqli_real_escape_string($conn, $picture);

   $sqlUpdateProfile = "UPDATE profile SET Bio='$safeBio', Picture='$safePicture' WHERE Username='$username'";
   if(mysqli_query($conn, $sqlUpdateProfile)){
      
      header('Location: index.php');
      exit();
   }
   
   header('Location: edit.php');
   exit();
}

function getProfileData($conn, $email){
   $badgeData = array();
   $profileData = array();
   //$sqlSelectInnerJoin = "SELECT profile.Username, profile.Bio, profile.Picture FROM profile INNER JOIN users ON profile.Username = users.Username WHERE users.Email='$email'";
   
   //returns username, bio, picture from profile where username is in the subquery, acts like an inner join
   $sqlSelectSubQuery = "SELECT Username, Bio, Picture FROM profile WHERE Username IN (SELECT Username FROM users WHERE Email = '$email')";
   if(checkIfPremiumUser($conn, $email)){
      $sqlSelectBadge = "SELECT BadgeURL FROM premium WHERE Email='$email'";
      $resultBadge = mysqli_query($conn, $sqlSelectBadge);
      $badgeData = mysqli_fetch_array($resultBadge, MYSQLI_ASSOC);
   }
   $resultSubQuery = mysqli_query($conn, $sqlSelectSubQuery);
   $profileData = mysqli_fetch_array($resultSubQuery, MYSQLI_ASSOC);
   
   return array_merge($badgeData, $profileData);   

}

function searchUser($conn, $username){
   $safeUsername = mysqli_real_escape_string($conn, $username);
   $sqlSelectProfile = "SELECT Username,Bio,Picture FROM profile WHERE Username='$safeUsername'";
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

function setBackground($conn, $email){
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
      header('Location: /profile/index.php');
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
      header('Location: /profile/index.php');
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
      header('Location: /profile/index.php');
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
   header('Location: /index.php');
}






?>