<?php
session_start();

include "../db_operations.php";
include "../config/db_connection.php";

global $profileData;
$profileData = false;

$premium = checkIfPremiumUser($conn, $_SESSION['User']);

if(isset($_SESSION['User'])){
    $profileData = getProfileData($conn, $_SESSION['User']);
    if(isset($_POST['update'])){
        $data = array("Bio"=>$_POST['bio'], "Picture"=>$_POST['picture']);
        if($premium){
            $backgroundImage = $_POST['bgImage'];
            $badge = $_POST['badge'];
        }
        updateProfile($conn, $_SESSION['User'], $data, $backgroundImage, $badge, $premium);
    }
}


?>

<?php include '../templates/header.php'; ?>

    <?php if(isset($_SESSION['User'])) { ?>
    <h1> Update Profile </h1>
    <form action="edit.php" method="POST">
        <input class="TextBox" type="text" placeholder="Enter new bio" name="bio" value="<?php echo $profileData['Bio'] ?>" >
        <input class="TextBox" type="text" placeholder="Enter Web URL of new picture" name="picture" value = "<?php echo $profileData['Picture'] ?>">
        <?php if($premium){ ?>
            <input class="TextBox" type="text" placeholder="Enter Web URL of new background picture" name="bgImage" 
            value = "<?php echo (isset($_SESSION['bg-image'])) ? $_SESSION['bg-image'] : NULL?>">
            Enter Badge file again:
            <input class="TextBox" type="file" name="badge"> 
        <?php } ?>
        <input class="Button" type="submit" name="update" value="Update">
    </form>

    <?php }else{ ?>
        <h1> Login/register required </h1>
    <?php } ?>

<?php include '../templates/footer.php'; ?>