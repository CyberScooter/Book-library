<?php 
session_start();

include "../db_operations.php";
include "../config/db_connection.php";

global $profileData;
global $booksData;

if(isset($_GET['user'])){
    $profileData = searchUser($conn, $_GET['user']);
    //also get books data of that user
}

if(isset($_GET['fileinput'])){
    echo $_GET['fileinput'];
}

if(isset($_SESSION['User']) && !isset($_GET['user'])){
    $profileData = getProfileData($conn, $_SESSION['User']);
    //also get books data of that user
}

global $editProfile;
$editProfile = false;
if(isset($_POST['edit'])){
    $editProfile = true;
}


//test this :

global $userBooksArray;
global $bookReviewArray;
global $bookDetailsArray;
global $pagesDetailsArray;
if(!isset($_GET['user'])){
    $username = getUsernameFromUsersTable($conn, $_SESSION['User']);
    list($userBooksArray, $bookReviewArray, $bookDetailsArray, $pagesDetailsArray) = getAllUserBookReviews($conn, $username);
}else {
    //else display books for the user
    $username = getUsernameFromUsersTable($conn, $_GET['user']);
    list($userBooksArray, $bookReviewArray, $bookDetailsArray, $pagesDetailsArray) = getAllUserBookReviews($conn, $username);
}

?>


<?php include '../templates/header.php'; ?>


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
    <div class="Picture"><img class="ProfilePicture" src="<?php echo (file_exists($profileData['Picture'])) ? $profileData['Picture'] : '../resources/pixabay-pp.png' ?>"/></div>

    <?php } ?>

    <p><?php echo (isset($_GET['user']) && $profileData == null) ? 'User does not exist' : ' ' ?> </p>
    <p><?php echo (isset($_GET['user'])) ? '<a class="Button" href="index.php"> Return to my profile </a>' : '<a class="Button" href="edit.php"> Edit profile </a>' ?></p>

    <hr>

    <h2> Books: </h2>
    <?php echo (!isset($_GET['user'])) ? '<a class="Button" href="../book/add.php"> Add Book </a>' : NULL ?>
    <?php if(isset($userBooksArray)){ ?>
    <?php   foreach($userBooksArray as $i => $item){ ?>
    <div class="BookReview">
    <?php if((!$bookReviewArray[$i]['Visible'] && isset($_GET['user'])) || ($bookReviewArray[$i]['Visible'] && !isset($_GET['user'])) || (!$bookReviewArray[$i]['Visible'] && !isset($_GET['user']))  ){ ?>
        
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
        <a class="Button" href="/coursework/book/edit.php?id=<?php echo $userBooksArray[$i]['ID'] ?>"> Update </a>
        <hr>
    <?php } ?>
    </div>
    <?php   } ?>
    <?php } ?>

    <?php }else { ?>
        <h1> Login/register required </h1>
    <?php } ?>

<?php include '../templates/footer.php'; ?>