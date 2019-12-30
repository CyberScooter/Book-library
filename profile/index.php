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

if(isset($_SESSION['User']) && !isset($_GET['user'])){
    $profileData = getProfileData($conn, $_SESSION['User']);
    $username = getUsernameFromUsersTable($conn, $_SESSION['User']);
    list($userBooksArray, $bookReviewArray, $bookDetailsArray, $pagesDetailsArray) = getAllUserBookReviews($conn, $username);
}

if(isset($_GET['user'])){
    $profileData = searchUser($conn, $_GET['user']);
    $username = $_GET['user'];
    list($userBooksArray, $bookReviewArray, $bookDetailsArray, $pagesDetailsArray) = getAllUserBookReviews($conn, $username);
}


global $editProfile;
$editProfile = false;
if(isset($_POST['edit'])){
    $editProfile = true;
}

if(isset($_POST['addComment'])){
    $id = $_POST['id'];
    $comment = $_POST['comment'];
    if($comment != null){
        insertNewComment($conn, $_SESSION['User'], $id, $comment);
    }
    if(isset($_POST['user'])){
        header('Location: ?user=' . $_POST['user'] . '#' . $id);
    }else{
        header('Location: #' . $id);
    }

}

if(isset($_POST['deleteComment'])){
    $id = $_POST['postIDToDelete'];
    deleteComment($conn, $_SESSION['User'], $id);

    if(isset($_POST['user'])){
        header('Location: ?user=' . $_POST['user']);
    }else{
        header('Location: index.php');
    }

}


//test this :


?>


<?php include '../templates/header.php'; ?>


    <!-- USER NEEDS TO BE LOGGED IN/SESSION NEEDS TO BE ACTIVE IN ORDER TO ACCESS THE DATA-->
    <?php if(isset($_SESSION['User'])){ ?>
    <div class="container">
    <form action="index.php" method="GET">
        <input class="SearchBox" type="text" placeholder="Search for a user" name="user">
        <input class="Search" type="submit" value="Search"> 
    </form>

    <?php if($profileData != null) { ?>

    <hr> 

    <h1><?php echo (isset($_GET['user']) ? 'User: ' : 'Welcome ')?> <?php echo $profileData['Username'] ?> </h1>
    <h2> Bio: <?php echo ($profileData['Bio'] == null) ? ((isset($_GET['user'])) ? 'Nothing to show' : 'No bio added, try adding one') : $profileData['Bio'] ?> </h2>
    <div class="Picture"><img class="ProfilePicture" src="<?php echo (file_exists($profileData['Picture'])) ? $profileData['Picture'] : '/coursework/resources/pixabay-pp.png' ?>"/></div>

    <?php } ?>

    <h2><?php echo (isset($_GET['user']) && $profileData == null) ? 'User does not exist' : ' ' ?> </h2>
    <p><?php echo (isset($_GET['user'])) ? '<a class="Button" href="index.php"> Return to my profile </a>' : '<a class="Button" href="edit.php"> Edit profile </a>' ?></p>

    <hr>

    <h2> Books: </h2>

    <!-- IF THE USER IS NOT CURRENTLY ON THEIR OWN PROFILE THEN THE 'Add Book' BUTTON SHOULD BE REMOVED-->
    <?php echo (!isset($_GET['user'])) ? '<a class="Button" href="../book/add.php"> Add Book </a>' : NULL ?>

    <!-- MAKES SURE BOOK IS AVAILABLE BEFORE LOOPING TO PREVENT NULL ERRORS-->
    <?php if(isset($userBooksArray)){ ?>

    <!-- THIS LOOPS THROUGH ALL THE REVIEWS FOR A SPECIFIC USER AND STORES THE INDEX FOR EACH ONE AS '$i' -->
    <?php   foreach($userBooksArray as $i => $item){ ?>
    <div class="BookReview">

    <?php if(($bookReviewArray[$i]['Visible'] && isset($_GET['user'])) || ($bookReviewArray[$i]['Visible'] && !isset($_GET['user'])) || (!$bookReviewArray[$i]['Visible'] && !isset($_GET['user']))  ){ ?>
        
        <p>ISBN: <?php echo $bookReviewArray[$i]['ISBN'] ?></p>
        <p>Title: <?php echo $bookDetailsArray[$i]['Title'] ?></p>
        <p> <img src="/coursework/resources/books/<?php echo $bookDetailsArray[$i]['Picture'] ?>" /></p>
        <p>Description: <?php echo $bookDetailsArray[$i]['Description'] ?></p>
        <p>Author: <?php echo $bookDetailsArray[$i]['Author'] ?></p>
        <p>Date released: <?php echo $bookDetailsArray[$i]['DateReleased'] ?></p>
        <p>Read <?php echo $pagesDetailsArray[$i]['Page'] ?> out of <?php echo $pagesDetailsArray[$i]['TotalPages'] ?> pages in the book</p>

        <?php if( (int) $pagesDetailsArray[$i]['Page'] == (int) $pagesDetailsArray[$i]['TotalPages']   ){ ?>
            <p>Review: <?php echo $bookReviewArray[$i]['Review']?></p>
            <p>Rating: <?php echo $bookReviewArray[$i]['Rating']?></p>
        <?php } ?>

        <?php if(!isset($_GET['user'])){ ?>
            <a class="Button" href="/coursework/book/edit.php?id=<?php echo $userBooksArray[$i]['ID'] ?>"> Update Book details</a>
        <?php } ?>

        <!-- FORM TO ADD COMMENT FOR A SPECIFIC POST-->
        <form action="index.php" method="post">
            <input class="TextBox" type="text" placeholder="Enter a comment" name="comment">
            <input class="Button"  type="submit" name="addComment" value="Add comment">
            <!-- HIDDEN INPUT THAT STORES ID OF BOOKSREVIEW ADDED SO THAT IT CAN USE THE ID TO UPDATE THE DATABASE -->
            <input type="hidden" name="id" value="<?php echo $userBooksArray[$i]['ReviewID']; ?>">
            <!-- IF A USER MAKES A COMMENT ON ANOTHER USER'S PAGE THEN STORE THE USERNAME SO IT CAN BE ACCESSED AT TOP OF FILE TO REDIRECT BACK ON THE SAME PAGE -->
            <input type="hidden" name="user" value="<?php echo (isset($_GET['user'])) ? $_GET['user'] : NULL ?>">
        </form>

        <!-- THIS LOADS THE COMMENTS FOR EACH POST--> 
        <p id="<?php echo $userBooksArray[$i]['ReviewID'] ?>"> Comments: </p>
        <?php list($comments, $commentsDateCreated, $user, $commentId) = selectCommentsFromReview($conn, $userBooksArray[$i]['ReviewID']);?>
        <?php for($j = 0; $j < count($comments); $j++){?>
            <div class="Comment"><span><span class="BoldComment"><?php echo $user[$j] ?></span>: <?php echo $comments[$j] ?></span><span><span class="BoldComment">Added:</span> <?php echo $commentsDateCreated[$j] ?> </span></div>
            <?php if($user[$j] == getUsernameFromUsersTable($conn, $_SESSION['User'])){ ?>
                <form action="index.php" method="post">
                    <input class="DeleteComment" type="submit" name="deleteComment" value="Delete Comment">
                    <input type="hidden" name="postIDToDelete" value="<?php echo $commentId[$j] ?>">
                </form>
            <?php } ?>
        <?php } ?>

        <hr>
         



    <?php }else { ?>
        <h1>No books to show </h1>
    <?php } ?>
    </div>
    <?php   } ?>
    <?php } ?>

    <?php }else { ?>
        <h1> Login/register required </h1>
    <?php } ?>

<?php include '../templates/footer.php'; ?>