
<?php
/*
   - The '$conn' parameter is passed in from the config file as an argument to each of these functions when they're called
   - mysqli_real_escape_string function used to prevent sql injection, only used on some functions where it is absolutely essential because of user input
   - Error handling function created at end of file that takes in an argument of the connection and calls the 'die' keyword to terminate the current script and output
     a message
   - Some of the functions that have parameters '$email' when these types of functions are called they have a session variable passed in, this is another way 
     used to validate user inside of the SQL select statements
   - Comments have been made to code that required more thought process
*/

//LOGIN/REGISTER USER RELATED OPERATIONS:

function registerUser($conn, $email, $password, $passwordConfirmation, $username){
   $safeEmail = mysqli_real_escape_string($conn, $email);
   $safeUsername = mysqli_real_escape_string($conn, $username);
   if($password == $passwordConfirmation){            
      //Sql code below selects email and username from users, if the email matches the input OR the username matches the input then select it                                                                       
      $sqlSelectInnerJoin = "SELECT Email, Username FROM users WHERE Email='$safeEmail' OR Username = '$safeUsername'";
      if(!$result = mysqli_query($conn, $sqlSelectInnerJoin)){
         sqlError($conn);
      }
      if(mysqli_num_rows($result) == 0){
         //Stores password hash in database which in default uses bcrypt algorithm
         $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
         $sqlInsertUser = "INSERT INTO users(Email,Username,Hash) VALUES('$safeEmail','$safeUsername','$hashedPassword')";
         $sqlInsertProfile = "INSERT INTO profile(Username) VALUES('$safeUsername')";
         if(mysqli_query($conn, $sqlInsertProfile)){
            if(mysqli_query($conn, $sqlInsertUser)){
               insertStandardUser($conn, $safeEmail); 
               $_SESSION['User'] = $safeEmail;
               header('Location: index.php');
               exit();
            }else{
               sqlError($conn);
            }
         }else{
            sqlError($conn);
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
   $sqlInsertStandard = "INSERT INTO standard(Email) VALUES('$email')";
   if(!$result = mysqli_query($conn, $sqlInsertStandard)){
      sqlError($conn);
   }
}

function loginUser($conn, $email, $password){
   $safeEmail = mysqli_real_escape_string($conn, $email);
   $sqlSelect = "SELECT Hash FROM users WHERE Email='$safeEmail'";
   if(!$result = mysqli_query($conn,$sqlSelect)){
      sqlError($conn);
   }
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

// BOOK / BOOK REVIEWS / FAVOURITE BOOKS RELATED OPERATIONS:

function checkExistingBook($conn, $email, $isbn){
   $sqlSelectBook = "SELECT ISBN FROM books WHERE ISBN='$isbn'";
   if(!$resultBook = mysqli_query($conn, $sqlSelectBook)){
      sqlError($conn);
   }
   if(mysqli_num_rows($resultBook) != 0){
      return true;
   }
   return false;
}

function checkExistingAuthor($conn, $email, $author){
   $sqlSelectAuthor = "SELECT Name FROM author WHERE Name='$author'";
   if(!$resultAuthor = mysqli_query($conn, $sqlSelectAuthor)){
      sqlError($conn);
   }

   if(mysqli_num_rows($resultAuthor) != 0){
      return true;
   }
   return false;
}

function checkExistingReviewFromUser($conn, $email, $isbn){
   $sqlSelectReviewsISBN = "SELECT reviews.ISBN FROM users_reviews INNER JOIN reviews ON users_reviews.ReviewID = reviews.ID WHERE reviews.ISBN='$isbn' AND users_reviews.Email='$email'";
   if(!$resultReviewsISBN = mysqli_query($conn, $sqlSelectReviewsISBN)){
      sqlError($conn);
   }
   if(mysqli_num_rows($resultReviewsISBN) != 0){
      return true;
   }
   return false;
}

function checkPagesReadAndTotalPagesEqual($conn, $id, $email){
   $safeReviewID = mysqli_real_escape_string($conn, $id);
   $sqlSelectUsersReviews = "SELECT PageID FROM users_reviews WHERE ID='$safeReviewID' AND Email='$email'";

   if(!$resultUsersReviews = mysqli_query($conn, $sqlSelectUsersReviews)){
      sqlError($conn);
   }
   $usersReviewsArray = mysqli_fetch_array($resultUsersReviews, MYSQLI_ASSOC);

   $pageID = $usersReviewsArray['PageID'];

   $sqlSelectPages = "SELECT Page, TotalPages FROM pages WHERE ID='$pageID'";
   if(!$resultPages = mysqli_query($conn, $sqlSelectPages)){
      sqlError($conn);
   }
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
   $safeAuthor = mysqli_real_escape_string($conn, $author);

   if(!checkExistingAuthor($conn, $email, $safeAuthor)){
      $sqlInsertAuthor = "INSERT INTO author(Name, DOB) VALUES('$safeAuthor', '$authorDOB')";
      if(!mysqli_query($conn, $sqlInsertAuthor)){
         sqlError($conn);
      }
   }

   if(!checkExistingBook($conn, $email, $safeISBN)){
      $sqlInsertBook = "INSERT INTO books(ISBN, Author, Title, DateReleased, Description, Picture) VALUES('$safeISBN','$safeAuthor','$safeTitle','$releaseDate','$safeDescription','$safePicture')";
      if(!mysqli_query($conn, $sqlInsertBook)){
         sqlError($conn);
      }
   }

   if(!checkExistingReviewFromUser($conn, $email, $safeISBN)){
      $pageID;
      $reviewID;
      $sqlInsertPages = "INSERT INTO pages(TotalPages, Page) VALUES('$safeTotalPages','$safePagesRead')";
      if (mysqli_query($conn, $sqlInsertPages)){
         $pageID = mysqli_insert_id($conn);
      }else{
         sqlError($conn);
      }
   
      $sqlInsertReview = "INSERT INTO reviews(ISBN,Review,Rating,Visible) VALUES('$safeISBN','$safeReview','$rating','$visible')";
      if (mysqli_query($conn, $sqlInsertReview)) {
         $reviewID = mysqli_insert_id($conn);
      }else{
         sqlError($conn);
      }
   
      $sqlInsertUsersReviews = "INSERT INTO users_reviews(ReviewID, Email, PageID) VALUES('$reviewID','$email','$pageID')";
      if(!mysqli_query($conn, $sqlInsertUsersReviews)){
         sqlError($conn);
      }

   }else{
      $_SESSION['errmessage'] = "Book already exists!";
   }
   
}


/**
 * This method stores data in multidimensional array
 * The number of tuples/rows in the users_reviews table that belong to a specific user is the size of the index for the multidimensional array
 * Each index of the multi dimensional array stores data about the book through associative properties
 */
function getAllUserBookReviews($conn, $username){
   $safeUsername = mysqli_real_escape_string($conn, $username);
   $booksData = array();

   $sqlSelectUsersReviews = "SELECT users_reviews.* FROM users INNER JOIN users_reviews ON users.Email = users_reviews.Email WHERE users.Username='$safeUsername'";
   if(!$resultUserReviews = mysqli_query($conn, $sqlSelectUsersReviews)){
      sqlError($conn);
   }

   while($row = mysqli_fetch_assoc($resultUserReviews)) {
      $booksData[] = $row;
   }

   //try for default for loop with index number if this loop doesnt work
   foreach($booksData as $i => $item){
      $reviewID = $booksData[$i]['ReviewID'];
      $pageID = $booksData[$i]['PageID'];
      $sqlSelectBookReview = "SELECT ISBN, Review, Rating, Visible FROM reviews WHERE ID='$reviewID'";
      if(!$resultBookReview = mysqli_query($conn, $sqlSelectBookReview)){
         sqlError($conn);
      }

      //should only be one row per review id so this only loops through once
      //used to map the data for each '$row' property to the 'booksData' array
      while($row = mysqli_fetch_assoc($resultBookReview)) {
         $booksData[$i]['ISBN'] = $row['ISBN'];
         $booksData[$i]['Review'] = $row['Review'];
         $booksData[$i]['Rating'] = $row['Rating'];
         $booksData[$i]['Visible'] = $row['Visible'];
      }

      $isbn = $booksData[$i]['ISBN'];
      $sqlSelectBookDetails = "SELECT Author, Title, DateReleased, Description, Picture FROM books WHERE ISBN='$isbn'";
      if(!$resultBookDetails = mysqli_query($conn, $sqlSelectBookDetails)){
         sqlError($conn);
      }

      //should only be one row per review id so this only loops through once
      //used to map the data for each '$row' property to the 'booksData' array
      while($row = mysqli_fetch_assoc($resultBookDetails)) {
         $booksData[$i]['Author'] = $row['Author'];
         $booksData[$i]['Title'] = $row['Title'];
         $booksData[$i]['DateReleased'] = $row['DateReleased'];
         $booksData[$i]['Description'] = $row['Description'];
         $booksData[$i]['Picture'] = $row['Picture'];
      }

      $sqlSelectPageDetails = "SELECT Page, TotalPages FROM pages WHERE ID='$pageID'";
      if(!$resultPagesDetails = mysqli_query($conn, $sqlSelectPageDetails)){
         sqlError($conn);
      }

      //should only be one row per review id so this only loops through once
      //used to map the data for each '$row' property to the 'booksData' array
      while($row = mysqli_fetch_assoc($resultPagesDetails)){
         $booksData[$i]['Page'] = $row['Page'];
         $booksData[$i]['TotalPages'] = $row['TotalPages'];
      }

   }
   return $booksData;
}
/**
 * This returns data for one review, it is used for editing a review as it loads the currently existing values
 * from this function into the input tags
 */
function getOneUserBookReview($conn, $email, $id){
   $safeID = mysqli_real_escape_string($conn, $id);
   $sqlSelectUsersReviews = "SELECT ReviewID, PageID FROM users_reviews WHERE ID='$safeID' AND Email='$email'";

   if($resultUsersReviews = mysqli_query($conn, $sqlSelectUsersReviews)){
      //Redirects to books index page if another invalid index tries to be accessed from url to edit page
      if(mysqli_num_rows($resultUsersReviews) == 0){
         
         header('Location: /books/index.php');
         exit();
      }
      $usersReviewsArray = mysqli_fetch_array($resultUsersReviews, MYSQLI_ASSOC);
   }else{
      sqlError($conn);
   }

   $reviewID = $usersReviewsArray['ReviewID'];
   $pageID = $usersReviewsArray['PageID'];

   $sqlSelectPages = "SELECT Page, TotalPages FROM pages WHERE ID='$pageID'";

   if(!$resultPages = mysqli_query($conn, $sqlSelectPages)){
      sqlError($conn);
   }
   $pagesArray = mysqli_fetch_array($resultPages, MYSQLI_ASSOC);


   $sqlSelectInnerJoin = "SELECT reviews.ISBN, reviews.Review, reviews.Rating, reviews.Visible, books.Author, books.Title, books.DateReleased, 
                           books.Description, author.DOB FROM reviews INNER JOIN books ON reviews.ISBN = books.ISBN
                           INNER JOIN author ON author.Name = books.Author WHERE reviews.ID='$reviewID'";

   if(!$resultInnerJoin = mysqli_query($conn, $sqlSelectInnerJoin)){
      sqlError($conn);
   }
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
   }else{
      sqlError($conn);
   }

   $reviewID = $usersReviewsArray['ReviewID'];
   $pageID = $usersReviewsArray['PageID'];

   //This only updates pages of book, if it becomes the same then the review option is opened up
   $sqlUpdatePages = "UPDATE pages SET Page='$safePagesRead' WHERE ID='$pageID'";
   if(!mysqli_query($conn, $sqlUpdatePages)){
      sqlError($conn);
   }

   $sqlUpdateReview = "UPDATE reviews SET Review='$safeReview', Rating='$rating', Visible='$visible' WHERE ID='$reviewID'";
   if(!mysqli_query($conn, $sqlUpdateReview)){
      sqlError($conn);
   }

}


/**
 * This function checks whether the book review that is being deleted from the 'reviews' table has an 
 * ISBN which another existing user is already using, If thats the case then it doesnt delete the book and only the review
 * else it deletes both book and review
 * 
 * It also goes through further checking to make sure that if the author is also being used by other existing users then keep 
 * it in the database else delete
 */
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
               if(!mysqli_query($conn, $sqlDeletePostsID)){
                  sqlError($conn);
               }

               $sqlDeleteCommentID = "DELETE FROM comments WHERE ID='$commentID'";
               if(!mysqli_query($conn, $sqlDeleteCommentID)){
                  sqlError($conn);
               }
            }
            
         }

         if(checkFavouriteBook($conn, $email, $id)){
            $sqlDeleteFavourites = "DELETE FROM favourites WHERE Email='$email' AND ReviewID='$id'";
            if(!mysqli_query($conn, $sqlDeleteFavourites)){
               sqlError($conn);
            }
         }

         $sqlDeleteUsersReviewsID = "DELETE FROM users_reviews WHERE ID='$userBooksID'";
         if(!mysqli_query($conn, $sqlDeleteUsersReviewsID)){
            sqlError($conn);
         }

         $sqlDeleteReviewID = "DELETE FROM reviews WHERE ID='$id'";
         if(!mysqli_query($conn, $sqlDeleteReviewID)){
            sqlError($conn);
         }

         $sqlDeletePagesID = "DELETE FROM pages WHERE ID='$pagesID'";
         if(!mysqli_query($conn, $sqlDeletePagesID)){
            sqlError($conn);
         }

      }else{
         sqlError($conn);
      }

      //if there is only one review which matches the input isbn
      if(mysqli_num_rows($result) == 1){

         $sqlSelectAuthors = "SELECT * FROM books WHERE Author='$author'";

         if($resultAuthor = mysqli_query($conn, $sqlSelectAuthors)){

            if(mysqli_num_rows($resultAuthor) > 1){

               $sqlDeleteBook = "DELETE FROM books WHERE ISBN='$isbn'";
               if(!mysqli_query($conn, $sqlDeleteBook)){
                  sqlError($conn);
               }
            }

            $sqlDeleteBook = "DELETE FROM books WHERE ISBN='$isbn'";
            if(!mysqli_query($conn, $sqlDeleteBook)){
               sqlError($conn);
            }

            $sqlDeleteAuthor = "DELETE FROM author WHERE Name='$author'";
            if(!mysqli_query($conn, $sqlDeleteAuthor)){
               sqlError($conn);
            }
         
         }else{
            sqlError($conn);
         }

      }

      $reviewArray = mysqli_fetch_array($result, MYSQLI_ASSOC);
      if(checkIfStandardUser($conn, $email) && !$reviewArray['visible']){
         incrementPrivateReviews($conn, $email);
      }

      if(checkIfStandardUser($conn, $email)){
         incrementStandardLimitReviews($conn, $email);
      }
   }else{
      sqlError($conn);
   }
}

function addBookToFavourite($conn, $email, $reviewID){
   $sqlInsertFavourites = "INSERT INTO favourites(Email,ReviewID) VALUES('$email','$reviewID')";
   if(!mysqli_query($conn, $sqlInsertFavourites)){
      sqlError($conn);
   }

}
/**
 * This is used to select all of the favourite book reviews for a given user
 * 
 * This function has the same type of functionality as the 'getAllUserBookReviews' function above
 */
function selectAllFavouriteBooks($conn, $email){
   $favouriteBooksData = array();

   $sqlSelectFavouriteBooks = "SELECT ReviewID FROM favourites";
   if(!$resultFavouriteBooks = mysqli_query($conn, $sqlSelectFavouriteBooks)){
      sqlError($conn);
   }

   while($row = mysqli_fetch_assoc($resultFavouriteBooks)) {
      $favouriteBooksData[] = $row;
   }

   foreach($favouriteBooksData as $i => $item){
      $reviewID = $favouriteBooksData[$i]['ReviewID'];
      $sqlSelectReviews = "SELECT ISBN, Review, Rating FROM reviews WHERE ID='$reviewID'";
      if(!$resultReviews = mysqli_query($conn, $sqlSelectReviews)){
         sqlError($conn);
      }

      while($row = mysqli_fetch_assoc($resultReviews)) {
         $favouriteBooksData[$i]['ISBN'] = $row['ISBN'];
         $favouriteBooksData[$i]['Rating'] = $row['Rating'];
         $favouriteBooksData[$i]['Review'] = $row['Review'];
      }

      $isbn = $favouriteBooksData[$i]['ISBN'];
      $sqlSelectBook = "SELECT Author, Title, DateReleased, Description, Picture FROM books WHERE ISBN='$isbn'";
      if(!$resultBook = mysqli_query($conn, $sqlSelectBook)){
         sqlError($conn);
      }

      while($row = mysqli_fetch_assoc($resultBook)) {
         $favouriteBooksData[$i]['Author'] = $row['Author'];
         $favouriteBooksData[$i]['Title'] = $row['Title'];
         $favouriteBooksData[$i]['DateReleased'] = $row['DateReleased'];
         $favouriteBooksData[$i]['Picture'] = $row['Picture'];
         $favouriteBooksData[$i]['Description'] = $row['Description'];
      }
   }

   return $favouriteBooksData;
}  

function deleteFromFavourites($conn, $email, $reviewID){
   $sqlDeleteFromFavourites = "DELETE FROM favourites WHERE Email='$email' AND ReviewID='$reviewID'";
   if(!mysqli_query($conn, $sqlDeleteFromFavourites)){
      sqlError($conn);
   }
}

function checkFavouriteBook($conn, $email, $reviewID){
   $sqlSelectBook = "SELECT ReviewID from favourites WHERE ReviewID='$reviewID' AND Email='$email'";
   if(!$result = mysqli_query($conn, $sqlSelectBook)){
      sqlError($conn);
   }
   if(mysqli_num_rows($result) == 1){
      return true;
   }
   return false;
}

//=================================================================================================================================================================
// BOOK REVIEW COMMENTS OPERATIONS:

/**
 * This selects all the data related to the comments that is needed when displaying to the user
 * It retrieves the Email, CommentID and created_at then uses the Email to convert to a username
 * and CommentID to retrieve the comment.
 * 
 * The reviewID parameter determines which the comments needed to be selected
 * 
 * Each of the data: Comment, Username, date created and commentID is added to each individual array
 * The index at each of the four arrays correspond to the data all four arrays are related to
 * at the end these four arrays are returned seperately and are used in the comments section to display the comment
 */
function selectCommentsFromReview($conn, $reviewID){
   $user = array();
   $comments = array();
   $dateCreated = array();
   $commentIDs = array();

   $sqlSelectCommentID = "SELECT User, CommentID, created_at FROM posts WHERE ReviewID='$reviewID'";
   if(!$resultCommentID = mysqli_query($conn, $sqlSelectCommentID)){
      sqlError($conn);
   }

   while($row = mysqli_fetch_assoc($resultCommentID)){
      $username = getUsernameFromUsersTable($conn, $row['User']);
      $commentID = $row['CommentID'];
      $created_at = $row['created_at'];
      $sqlSelectComment = "SELECT Comment FROM comments WHERE ID='$commentID'";
      if(!$resultComment = mysqli_query($conn, $sqlSelectComment)){
         sqlError($conn);
      }
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
   }else{
      sqlError($conn);
   }

   $sqlInsertToPosts = "INSERT INTO posts(User, CommentID, ReviewID) VALUES('$email','$id','$reviewID')";
   if(!$result = mysqli_query($conn, $sqlInsertToPosts)){
      sqlError($conn);
   }

   
}

function deleteComment($conn, $email, $commentID){
   $sqlSelectPostsID = "SELECT ID FROM posts WHERE CommentID='$commentID'";
   if(!$resultSelectPostsID = mysqli_query($conn, $sqlSelectPostsID)){
      sqlError($conn);
   }
   $postsIDArray = mysqli_fetch_array($resultSelectPostsID, MYSQLI_ASSOC);
   $postID = $postsIDArray['ID'];

   $sqlDeletePostID = "DELETE FROM posts WHERE ID='$postID'";
   if(!$resultDeletePostID = mysqli_query($conn, $sqlDeletePostID)){
      sqlError($conn);
   }

   $sqlDeleteComment =  "DELETE FROM comments WHERE ID='$commentID'";
   if(!$resultDeleteComment = mysqli_query($conn, $sqlDeleteComment)){
      sqlError($conn);
   }

}

//=================================================================================================================================================================
// USERS OPERATIONS

/**
 * Two functions below allow easy conversion between username and email which is needed as The user session is stored as an email
 * and sometimes the username is needed to display on the website or for certain functions listed in this file
 */
function getUsernameFromUsersTable($conn, $email){
   $sqlSelect = "SELECT Username FROM users WHERE Email='$email'";
   if(!$result = mysqli_query($conn, $sqlSelect)){
      sqlError($conn);
   }
   $userArray = mysqli_fetch_array($result, MYSQLI_ASSOC);
   
   return $userArray['Username'];
}

function getEmailFromUsersTable($conn, $username){
   $sqlSelectSubQuery = "SELECT Email FROM users WHERE Username ='$username'";
   if(!$result = mysqli_query($conn, $sqlSelectSubQuery)){
      sqlError($conn);
   }
   $userArray = mysqli_fetch_array($result, MYSQLI_ASSOC);
   
   return $userArray['Email'];
}

/**
 * This takes care of updating profile information like the Bio, background image, profile picture or badge
 */
function updateProfile($conn, $email, $bio, $picture, $backgroundImageURL, $badgeURL, $premium){
   $safeBackgroundImageURL = mysqli_real_escape_string($conn, $backgroundImageURL);
   if($badgeURL != null){
      $safeBadgeURL = mysqli_real_escape_string($conn, $badgeURL);
   }

   if($premium){
      $sqlUpdatePremium = "UPDATE premium SET BackgroundURL='$backgroundImageURL' WHERE Email='$email'";

      if($badgeURL != null){
         $sqlUpdatePremium = "UPDATE premium SET BadgeURL='$safeBadgeURL',BackgroundURL='$backgroundImageURL' WHERE Email='$email'";
      }


      if(mysqli_query($conn, $sqlUpdatePremium)){
         $_SESSION['bg-image'] = $safeBackgroundImageURL;
      }else{
         sqlError($conn);
      }
   }

   $username = getUsernameFromUsersTable($conn, $email);

   $safeBio = mysqli_real_escape_string($conn, $bio);
   $safePicture = mysqli_real_escape_string($conn, $picture);

   $sqlUpdateProfile = "UPDATE profile SET Bio='$safeBio', Picture='$safePicture' WHERE Username='$username'";
   if(mysqli_query($conn, $sqlUpdateProfile)){
      
      header('Location: index.php');
      exit();
   }else{
      sqlError($conn);
   }
   
   header('Location: edit.php');
   exit();
}

/**
 * This function gets the data on the user like the badge, bio, profile picture.
 */
function getProfileData($conn, $username){
   $email = getEmailFromUsersTable($conn, $username);
   $badgeData = array();
   $profileData = array();

   //returns username, bio, picture from profile where username is in the subquery, acts like an inner join
   $sqlSelectSubQuery = "SELECT Username, Bio, Picture FROM profile WHERE Username IN (SELECT Username FROM users WHERE Email = '$email')";
   if(checkIfPremiumUser($conn, $email)){
      $sqlSelectBadge = "SELECT BadgeURL, BackgroundURL FROM premium WHERE Email='$email'";
      if(!$resultBadge = mysqli_query($conn, $sqlSelectBadge)){
         sqlError($conn);
      }
      $badgeData = mysqli_fetch_array($resultBadge, MYSQLI_ASSOC);
   }
   if(!$resultSubQuery = mysqli_query($conn, $sqlSelectSubQuery)){
      sqlError($conn);
   }
   $profileData = mysqli_fetch_array($resultSubQuery, MYSQLI_ASSOC);
   
   if($profileData == null){
      return array();
   }
   return array_merge($badgeData, $profileData);   

}


//=================================================================================================================================================================
// PREMIUM USERS OPERATIONS:

/**
 * Removed record from standard and adds a new one to premium
 * In doing so this breaks all the standard account limitations
 */
function upgradeToPremium($conn, $email){
   $sqlInsertPremium = "INSERT INTO premium(Email) VALUES('$email')";
   if(!$resultInsert = mysqli_query($conn, $sqlInsertPremium)){
      sqlError($conn);
   }

   $sqlDeleteStandard = "DELETE FROM standard WHERE Email='$email'";
   if(!$resultDelete = mysqli_query($conn , $sqlDeleteStandard)){
      sqlError($conn);
   }

}

/**
 * To allow certain privileges for premium users
 */
function checkIfPremiumUser($conn, $email){
   $sqlSelectPremium = "SELECT Email FROM premium WHERE Email='$email'";
   if(!$result = mysqli_query($conn, $sqlSelectPremium)){
      sqlError($conn);
   }
   if(mysqli_num_rows($result) != 0){
      return true;
   }
   return false;
}

/**
 * Premium feature allows to change the background to whatever link set
 */
function setBackground($conn, $email){
   $sqlSelectBackground = "SELECT BackgroundURL FROM premium WHERE Email='$email'";
   if($result = mysqli_query($conn, $sqlSelectBackground)){
      $backgroundArray = mysqli_fetch_array($result, MYSQLI_ASSOC);
      $_SESSION['bg-image'] = $backgroundArray['BackgroundURL'];
   }else{
      sqlError($conn);
   }
}

//=================================================================================================================================================================
// STANDARD USERS OPERATIONS

/**
 * To allow certain privileges for standard users
 */
function checkIfStandardUser($conn, $email){
   $sqlSelectStandard = "SELECT Email FROM standard WHERE Email='$email'";
   if(!$result = mysqli_query($conn, $sqlSelectStandard)){
      sqlError($conn);
   }
   if(mysqli_num_rows($result) != 0){
      return true;
   }
   return false;
}

/**
 * Two functions below keep track of the standard account book reviews limit
 * They increment and decrement the value of BooksLimit
 */
function incrementStandardLimitReviews($conn, $email){
   $sqlSelectStandard = "SELECT BooksLimit FROM standard WHERE Email='$email'";
   if($resultSelect = mysqli_query($conn, $sqlSelectStandard)){
      $selectArray = mysqli_fetch_array($resultSelect, MYSQLI_ASSOC);
   }else {
      sqlError($conn);
   }

   $booksLimit = (int) $selectArray['BooksLimit'] + 1;
   if($booksLimit <= 5){
      $sqlUpdateStandard = "UPDATE standard SET BooksLimit='$booksLimit' WHERE Email = '$email'";
      if(!$resultUpdate = mysqli_query($conn, $sqlUpdateStandard)){
         sqlError($conn);
      }
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

/**
 * Two functions below keep track of the standard account private reviews limit
 * They increment and decrement the value of PrivateReviews
 */

function decrementPrivateReviews($conn, $email){
   $sqlSelectStandard = "SELECT PrivateReviews FROM standard WHERE Email='$email'";
   if($resultSelect = mysqli_query($conn, $sqlSelectStandard)){
      $selectArray = mysqli_fetch_array($resultSelect, MYSQLI_ASSOC);
   }else{
      sqlError($conn);
   }
   $privateReviews = (int) $selectArray['PrivateReviews'] - 1;
   if($privateReviews >= 0){
      $sqlUpdateStandard = "UPDATE standard SET PrivateReviews='$privateReviews' WHERE Email = '$email'";
      if(!$resultUpdate = mysqli_query($conn, $sqlUpdateStandard)){
         sqlError($conn);
      }
   }else{
      $_SESSION['errmessage'] = "Private reviews limit reached";
      header('Location: /profile/index.php');
      exit();   
   }
}

function incrementPrivateReviews($conn, $email){
   $sqlSelectStandard = "SELECT PrivateReviews FROM standard WHERE Email='$email'";
   if($resultSelect = mysqli_query($conn, $sqlSelectStandard)){
      $selectArray = mysqli_fetch_array($resultSelect, MYSQLI_ASSOC);
   }else{
      sqlError($conn);
   }

   $privateReviews = (int) $selectArray['PrivateReviews'] + 1;
   $sqlUpdateStandard = "UPDATE standard SET PrivateReviews='$privateReviews' WHERE Email = '$email'";
   if(!$resultUpdate = mysqli_query($conn, $sqlUpdateStandard)){
      sqlError($conn);
   }
}

function checkStandardBooksLimit($conn, $email){
   $sqlSelectStandard = "SELECT BooksLimit FROM standard WHERE Email='$email'";
   if($resultSelect = mysqli_query($conn, $sqlSelectStandard)){
      $selectArray = mysqli_fetch_array($resultSelect, MYSQLI_ASSOC);
   }else {
      sqlError($conn);
   }
   return ((int) $selectArray['BooksLimit'] != 0);
}

//=================================================================================================================================================================
//ERROR HANDLING

function sqlError($conn){
   die('Invalid operation: '. mysqli_error($conn));
}

?>