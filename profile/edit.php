<?php
session_start();

include "../db_operations.php";
include "../config/db_connection.php";

global $profileData;
$profileData = false;

if(isset($_SESSION['User'])){
    $premium = checkIfPremiumUser($conn, $_SESSION['User']);
    $username = getUsernameFromUsersTable($conn, $_SESSION['User']);
    $profileData = getProfileData($conn, $username);
    if(isset($_POST['update'])){
        $bio = $_POST['bio'];
        $picture = $_POST['picture'];
        if($premium){
            $backgroundImage = htmlspecialchars($_POST['bgImage']);
            $badge = htmlspecialchars($_POST['badge']);
            $backgroundImage == null ? $backgroundImage = $profileData['BackgroundURL'] : NULL;
            $badge == null ? $badge = $profileData['BadgeURL'] : NULL;
        }
        $picture == null ? $picture = $profileData['Picture'] : NULL;

        updateProfile($conn, $_SESSION['User'], $bio, $picture, $backgroundImage, $badge, $premium);
    }
}

global $error; 
if(isset($_SESSION['errmessage'])){ 
    $error = $_SESSION['errmessage'];
    unset($_SESSION['errmessage']); 
} 

global $success; 
if(isset($_SESSION['successmessage'])){ 
    $success = $_SESSION['successmessage']; 
    unset($_SESSION['successmessage']); 
}

?>

<?php include '../templates/header.php'; ?>

<h1 class="error"><?php echo $error?></h1>
<h1 class="success"><?php echo $success?></h1>

<div class="container">

<?php if(isset($_SESSION['User'])) { ?>

    <h1> Update Profile </h1>
    <h2 class="Red">For any file input left empty then last updated data from database is used</H2>
    
    <form action="edit.php" method="POST">
        <input class="TextBox" type="text" placeholder="Enter new bio" name="bio" value="<?php echo $profileData['Bio'] ?>">
        <h2>Select any image file from the 'profile-pictures' folder in 'resources' folder to update profile picture:</h2>
        <input class="TextBox" type="file" name="picture" >
        <?php if($premium){ ?>
            <h2>Select any image file from the 'backgrounds' folder in 'resources' folder to update background: </h2>
            <input class="TextBox" type="file" name="bgImage">
            <h2>Select any image file from 'badges folder in 'resources' folder to update badge:</h2>
            <input class="TextBox" type="file" name="badge"> 
        <?php } ?>
        <input class="Button" type="submit" name="update" value="Update">
    </form>

<?php }else{ ?>

    <h1> Login/register required </h1>

<?php } ?>

<?php include '../templates/footer.php'; ?>