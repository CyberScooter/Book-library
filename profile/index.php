<?php 
session_start();

include "../db_operations.php";
include "../config/db_connection.php";

global $profileData;

if(isset($_GET['search'])){
    $profileData = searchUser($conn, $_GET['search']);
}

if(isset($_SESSION['User']) && !isset($_GET['search'])){
    $profileData = getProfileData($conn, $_SESSION['User']);
}

global $editProfile;
$editProfile = false;
if(isset($_POST['edit'])){
    $editProfile = true;
}


?>


<?php include '../templates/header.php'; ?>


    <?php if(isset($_SESSION['User'])){ ?>
    <div class="container">
    <form action="index.php" method="GET">
        <input class="TextBox" type="text" placeholder="Search for a user" name="search">
        <input class="Search" type="submit" value="Search"> 
    </form>

    <?php if($profileData != null) { ?>

    <hr> 

    <h1><?php echo (isset($_GET['search']) ? 'User: ' : 'Welcome ')?> <?php echo $profileData['Username'] ?> </h1>
    <h2> Bio: <?php echo ($profileData['Bio'] == null) ? ((isset($_GET['search'])) ? 'Nothing to show' : 'No bio added, try adding one') : $profileData['Bio'] ?> </h2>
    <div class="Picture"><img class="ProfilePicture" src="<?php echo (file_exists($profileData['Picture'])) ? $profileData['Picture'] : '../resources/pixabay-pp.png' ?>"/></div>

    <?php } ?>

    <p><?php echo (isset($_GET['search']) && $profileData == null) ? 'User does not exist' : ' ' ?> </p>
    <p><?php echo (isset($_GET['search'])) ? '<a class="Button" href="index.php"> Return to my profile </a>' : '<a class="Button" href="edit.php"> Edit profile </a>' ?></p>

    <hr>

    <h2> Books: </h2>
    <a class="Button" href="../book/add.php"> Add Book </a>

    <?php }else { ?>
        <h1> Login/register required </h1>
    <?php } ?>

<?php include '../templates/footer.php'; ?>