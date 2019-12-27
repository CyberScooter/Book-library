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
$username = getUsernameFromUsersTable($conn, $_SESSION['User']);
global $ua;
global $bra;
global $bda;

list($ua, $bra, $bda) = getBookReview($conn, $username);



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
    <p> <?php echo $ua[0]['Email'] ?> </p>
    <p> <?php echo  $bra[0]['Review'] ?> </p>

    <?php }else { ?>
        <h1> Login/register required </h1>
    <?php } ?>

<?php include '../templates/footer.php'; ?>