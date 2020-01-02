<?php 
session_start();

include "../db_operations.php";
include "../config/db_connection.php";

global $profileData;
global $booksData;

global $comments;
global $commentsDateCreated;

global $userBooksArray;
global $bookReviewArray;
global $bookDetailsArray;
global $pagesDetailsArray;

//Allows use to upgrade to premium if they're already not a premium user
$premiumButton = false;

if(isset($_POST['premium'])){
    upgradeToPremium($conn, $_SESSION['User']); 
}

//If user is on their own profile:
if(isset($_SESSION['User']) && !isset($_GET['user'])){
    $profileData = getProfileData($conn, $_SESSION['User']);
    $username = getUsernameFromUsersTable($conn, $_SESSION['User']);
    list($userBooksArray, $bookReviewArray, $bookDetailsArray, $pagesDetailsArray) = getAllUserBookReviews($conn, $username);
    $premiumButton = checkIfStandardUser($conn, $_SESSION['User']);
}

//If user is on another person's profile:
if(isset($_GET['user'])){
    $profileData = searchUser($conn, $_GET['user']);
    $username = $_GET['user'];
    list($userBooksArray, $bookReviewArray, $bookDetailsArray, $pagesDetailsArray) = getAllUserBookReviews($conn, $username);
}

//
//TO DELETE MAYBE:

// global $editProfile;
// $editProfile = false;
// if(isset($_POST['edit'])){
//     $editProfile = true;
// }

//Comment operations

if(isset($_POST['addComment'])){
    $id = $_POST['id'];
    $comment = $_POST['comment'];
    if($comment != null){
        insertNewComment($conn, $_SESSION['User'], $id, $comment);
    }

    //Handles redirection, if POST variable below is not null then it redirects to other users website
    //else their own. This is determined via the hidden input in the form below
    if($_POST['user'] != null){
        header('Location: ?user=' . $_POST['user'] . '#' . $id);
    }else{
        header('Location: #' . $id);
    }

}

if(isset($_POST['deleteComment'])){
    $id = $_POST['postIDToDelete'];
    deleteComment($conn, $_SESSION['User'], $id);

    //Handles redirection, if POST variable below is not null then it redirects to other users website
    //else their own. This is determined via the hidden input in the form below
    if($_POST['user'] != null){
        header('Location: ?user=' . $_POST['user']);
    }else{
        header('Location: index.php');
    }

}

//Error handling
global $error;
if(isset($_SESSION['errmessage'])){
    $error = $_SESSION['errmessage'];
    unset($_SESSION['errmessage']);
}

?>


<?php include '../templates/header.php'; ?>


    <!-- User session needs to be set in order to access profile-->
    <?php if(isset($_SESSION['User'])){ ?>

        <h2 class="error"><?php echo $error ?></h2>

        <div class="container">

        <form action="index.php" method="GET">
            <input class="SearchBox" type="text" placeholder="Search for a user" name="user">
            <input class="Search" type="submit" value="Search"> 
        </form>

        <?php if($profileData != null) { ?>

        <!-- Displays specific data based on whether the user is on their own profile or someone elses -->
        <hr> 
        <h1><?php echo (isset($_GET['user']) ? 'User: ' : 'Welcome ')?> <?php echo $profileData['Username'] ?> </h1>
        <?php if(checkIfPremiumUser($conn, $_SESSION['User'])) { ?>
            <img width="50px" height="50px" src="<?php echo ($profileData['BadgeURL'] != null) ? "/coursework/resources/badges/".$profileData['BadgeURL'] : "" ?>" />
        <?php } ?>

        <h2> Bio: <?php echo ($profileData['Bio'] == null) ? ((isset($_GET['user'])) ? 'Nothing to show' : 'No bio added, try adding one') : $profileData['Bio'] ?> </h2>
        <div class="Picture"><img class="ProfilePicture" src="<?php echo (file_exists($profileData['Picture'])) ? $profileData['Picture'] : '/coursework/resources/pixabay-pp.png' ?>"/></div>

    <?php } ?>

    <h2><?php echo (isset($_GET['user']) && $profileData == null) ? 'User does not exist' : ' ' ?> </h2>


    <p><?php echo (isset($_GET['user'])) ? '<a class="Button" href="index.php"> Return to my profile </a>' : '<a class="Button" href="edit.php"> Edit profile </a>' ?></p>
    <?php if($premiumButton && !isset($_GET['user'])){ ?>
        <form action="index.php" method="post"> 
        <input type="submit" class="Button" value="Upgrade to premium" />
        <input type="hidden" name="premium">
        </form>
    <?php } ?>


    <hr>

    <!-- BOOKS SECTION -->

    <h2> Books: </h2>

    <!-- If the user is not currently on ANOTHER profile AND the standard bookslimit is not equal to zero OR however if the user is premium  -->
    <!-- then the 'Add book' button should be displayed -->
    <!-- checkStandardBooksLimit and checkIfPremiumUser functions interact with the database and output a result, stored in db_operations file-->

    <?php echo ((!isset($_GET['user']) && checkStandardBooksLimit($conn, $_SESSION['User'])) || checkIfPremiumUser($conn, $_SESSION['User'])) ? '<a class="Button" href="../book/add.php"> Add Book </a>' : NULL ?>

    <!-- Makes sure there is data set for books so that it doesnt loop through nothing and cause an error-->
    <?php if(isset($userBooksArray)){ ?>

    <!-- This loops through each review made by a user and the index of each one is referenced by the index location '$i' -->
    <?php   foreach($userBooksArray as $i => $item){ ?>
    
    <!-- CSS properties used to make it easier to visualise and see the data on the web page  -->
    <div class="BookReview">

    <!-- This if statement below determines access privileges to view the books displayed on the page -->
    <!-- Certain constraints need to be made for instance if there was a the case if a user makes their book private it should not be seen by anyone else except themselves -->
    <!-- The first part of if statement makes sure that the book is visible and the current user is viewing another profile -->
    <!-- The second part of the if statement makes sure that the book is visible and the user is on their own profile -->
    <!-- The third part of the if statement makes sure that the if the book is on private and the user is on their own profile then the book should be seen as it is their own -->

    <?php if(($bookReviewArray[$i]['Visible'] && isset($_GET['user'])) || ($bookReviewArray[$i]['Visible'] && !isset($_GET['user'])) || (!$bookReviewArray[$i]['Visible'] && !isset($_GET['user']))  ){ ?>
        
        <p>ISBN: <?php echo $bookReviewArray[$i]['ISBN'] ?></p>
        <p>Title: <?php echo $bookDetailsArray[$i]['Title'] ?></p>
        <p> <img src="/coursework/resources/books/<?php echo $bookDetailsArray[$i]['Picture'] ?>" /></p>
        <p>Description: <?php echo $bookDetailsArray[$i]['Description'] ?></p>
        <p>Author: <?php echo $bookDetailsArray[$i]['Author'] ?></p>
        <p>Date released: <?php echo $bookDetailsArray[$i]['DateReleased'] ?></p>
        <p>Read <?php echo $pagesDetailsArray[$i]['Page'] ?> out of <?php echo $pagesDetailsArray[$i]['TotalPages'] ?> pages in the book</p>
        <p><?php echo ($bookReviewArray[$i]['Visible']) ? 'Public review' : 'Private review' ?></p>
        <p>Created at : <?php echo $userBooksArray[$i]['created_at']?></p>

        <?php if( (int) $pagesDetailsArray[$i]['Page'] == (int) $pagesDetailsArray[$i]['TotalPages']   ){ ?>
            <p>Review: <?php echo $bookReviewArray[$i]['Review']?></p>
            <p>Rating: <?php echo $bookReviewArray[$i]['Rating']?></p>
        <?php } ?>

        <?php if(!isset($_GET['user'])){ ?>
            <a class="Button" href="/coursework/book/edit.php?id=<?php echo $userBooksArray[$i]['ID'] ?>"> Update Book details</a>
        <?php } ?>

        <!-- COMMENTS SECTION -->

        <!-- This form below allows a comment to be added to a book
            hidden inputs have been added to the form so that specific data like the ID and the current user is sent so that the comment can be
            added to a specific book and user 
        -->
        <form action="index.php" method="post">
            <input class="TextBox" type="text" placeholder="Enter a comment" name="comment">
            <input class="Button"  type="submit" name="addComment" value="Add comment">
            <input type="hidden" name="id" value="<?php echo $userBooksArray[$i]['ReviewID']; ?>">
            <input type="hidden" name="user" value="<?php echo (isset($_GET['user'])) ? $_GET['user'] : NULL ?>">
        </form>

        <p id="<?php echo $userBooksArray[$i]['ReviewID'] ?>"> Comments: </p>

        <!--
            An appropriate function from the db_operations.php file is called that selects all the required commenting data for the review
            so that it can be displayed onto the web page.
            An if statement is added inside the block of code below to determine if the comment belongs to the user, if so then delete option can be added
        -->
        <?php list($comments, $commentsDateCreated, $user, $commentId) = selectCommentsFromReview($conn, $userBooksArray[$i]['ReviewID']);?>
        <?php for($j = 0; $j < count($comments); $j++){?>
            <div class="Comment"><span><span class="BoldComment"><?php echo $user[$j] ?></span>: <?php echo $comments[$j] ?></span><span><span class="BoldComment">Added:</span> <?php echo $commentsDateCreated[$j] ?> </span></div>
            <?php if($user[$j] == getUsernameFromUsersTable($conn, $_SESSION['User'])){ ?>
                <form action="index.php" method="post">
                    <input class="DeleteComment" type="submit" name="deleteComment" value="Delete Comment">
                    <input type="hidden" name="postIDToDelete" value="<?php echo $commentId[$j] ?>">
                    <input type="hidden" name="user" value="<?php echo (isset($_GET['user'])) ? $_GET['user'] : NULL ?>">
                </form>
            <?php } ?>
        <?php } ?>

        <hr>

    <?php } ?>

    </div>
    <?php } ?>
    <?php } ?>

    <?php }else { ?>
        <h1> Login/register required </h1>
    <?php } ?>

<?php include '../templates/footer.php'; ?>