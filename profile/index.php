<?php 
session_start();

include "../db_operations.php";
include "../config/db_connection.php";

global $profileData;

global $comments;
global $commentsDateCreated;

global $booksData;


//Allows use to upgrade to premium if they're already not a premium user

$premiumButton = false;
if(isset($_POST['premium'])){
    upgradeToPremium($conn, $_SESSION['User']); 
}

//If user is on another person's profile:
if(isset($_GET['user'])){
    $profileData = getProfileData($conn, $_GET['user']);
    $username = htmlspecialchars($_GET['user']);
    $booksData = getAllUserBookReviews($conn, $username);
}

//User on their own profile
if(!isset($_GET['user']) && isset($_SESSION['User']) ){
    $username = getUsernameFromUsersTable($conn, $_SESSION['User']);    
    $profileData = getProfileData($conn, $username);
    $premiumButton = checkIfStandardUser($conn, $_SESSION['User']);
}

//Comment operations:

if(isset($_POST['addComment'])){
    $id = $_POST['id'];
    $comment = htmlspecialchars($_POST['comment']);
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

//Error handling:

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

    <!-- 'profileData' variable stores the data collected from database to be displayed on profile -->
    <?php if($profileData != null) { ?>

        <!-- Displays specific data based on whether the user is on their own profile or someone else's -->
        
        <!-- @GetImageSize causes a delay when updating images because it is opening up the image beforehand to make sure it is an image -->

        <hr> 
        <h1><?php echo (isset($_GET['user']) ? 'User: ' : 'Welcome ')?> <?php echo $profileData['Username'] ?> </h1>
        <!-- If user is own their own profile and is premium then display their own badge -->
        <?php if(checkIfPremiumUser($conn, $_SESSION['User']) && !isset($_GET['user'])) { ?>
            <img width="50px" height="50px" src="<?php echo ($profileData['BadgeURL'] != null) ? "/resources/badges/".$profileData['BadgeURL'] : "" ?>" alt="No badge added, try adding one" />
        <?php } ?>
        <!-- If user is on another person's profile and the other person is premium then display their badge-->
        <?php if(isset($_GET['user']) && checkIfPremiumUser($conn, getEmailFromUsersTable($conn, $_GET['user']))) {?>
            <img width="50px" height="50px" src="<?php echo ($profileData['BadgeURL'] != null) ? "/resources/badges/".$profileData['BadgeURL'] : "" ?>" alt="No badge added, try adding one" />
        <?php } ?>

        <h2> Bio: <?php echo ($profileData['Bio'] == null) ? ((isset($_GET['user'])) ? 'Nothing to show' : 'No bio added, try adding one') : $profileData['Bio'] ?> </h2>
        <div class="Picture"><img class="ProfilePicture" src="<?php echo ($profileData['Picture'] != null) ? '/resources/profile-pictures/' . $profileData['Picture'] : '/resources/profile-pictures/default.png' ?>"/></div>

    <?php } ?>

    <h2><?php echo (isset($_GET['user']) && $profileData == null) ? 'User does not exist' : ' ' ?> </h2>

    <p><?php echo (isset($_GET['user'])) ? '<a class="Button" href="index.php"> Return to my profile </a>' : '<a class="Button" href="edit.php"> Edit profile </a>' ?></p>

    <!-- Display premium button if user is on their own profile -->
    <?php if($premiumButton && !isset($_GET['user'])){ ?>
        <form action="index.php" method="post"> 
            <input type="submit" class="Button" value="Upgrade to premium" />
            <input type="hidden" name="premium">
        </form>
    <?php } ?>

    <!-- BOOKS SECTION -->

    <!-- Makes sure there is data set for books so that it doesnt loop through nothing and cause an error-->
    <?php if(isset($booksData)){ ?>
        <hr>
        <h2 style="font-size:2rem"> Books: </h2>

        <!-- This loops through each review made by a user and the review of each one is referenced by the index location '$i' -->
        <?php   foreach($booksData as $i => $item){ ?>
        
            <div class="BookReview">

            <!-- This if statement below determines access privileges to view the books displayed on the page 
                Certain constraints need to be made for instance if there was a the case that a user makes their book private it should not be seen by anyone else except themselves 
                The first part of the if statement makes sure that the book is visible and the user is on their own profile 
                The second part of the if statement makes sure that the if the book is on private and the user is on their own profile then the book should be seen as it is their own 

                If current user uses to find their own books using this page then it should display the books as someone else viewing their profile 
            -->

            <?php if(($booksData[$i]['Visible'] && isset($_GET['user'])) || ($booksData[$i]['Visible'] && !isset($_GET['user'])) || (!$booksData[$i]['Visible'] && !isset($_GET['user']))  ){ ?>
                
                <p><span class="Attributes"> ISBN:</span> <?php echo $booksData[$i]['ISBN'] ?></p>
                <p><span class="Attributes"> Title:</span> <?php echo $booksData[$i]['Title'] ?></p>
                <p> <img src="/resources/books/<?php echo $booksData[$i]['Picture'] ?>" /></p>
                <p><span class="Attributes"> Description:</span> <?php echo $booksData[$i]['Description'] ?></p>
                <p><span class="Attributes"> Author:</span> <?php echo $booksData[$i]['Author'] ?></p>
                <p><span class="Attributes"> Date released:</span> <?php echo $booksData[$i]['DateReleased'] ?></p>
                <p><span class="Attributes"> Read</span> <?php echo $booksData[$i]['Page'] ?> out of <?php echo $booksData[$i]['TotalPages'] ?> pages in the book</p>
                <p><span class="Attributes"> Created at :</span> <?php echo $booksData[$i]['created_at']?></p>

                <?php if( (int) $booksData[$i]['Page'] == (int) $booksData[$i]['TotalPages']   ){ ?>
                    <p><span class="Attributes"> Review:</span> <?php echo $booksData[$i]['Review']?></p>
                    <p><span class="Attributes"> Rating:</span> <?php echo $booksData[$i]['Rating']?></p>
                <?php } ?>

                <?php if(!isset($_GET['user'])){ ?>
                    <a class="Button" href="/books/edit.php?id=<?php echo $booksData[$i]['ID'] ?>"> Update Book details</a>
                    <form action="index.php" method="post">
                        <input class="Button" type="submit" name="deleteBook" value="Delete Book">
                        <input type="hidden" name="deleteISBN" value="<?php echo $booksData[$i]['ISBN'] ?>">
                        <input type="hidden" name="deleteReviewID" value="<?php echo $booksData[$i]['ReviewID'] ?>">
                        <input type="hidden" name="deleteAuthor" value="<?php echo $booksData[$i]['Author'] ?>">
                    </form>
                <?php } ?>

                <!-- COMMENTS SECTION -->

                <!-- This form below allows a comment to be added to a book
                    hidden inputs have been added to the form so that specific data like the ID and the current user is sent so that the comment can be
                    added to a specific book and user 
                -->
                <form action="index.php" method="post">
                    <input class="TextBox" type="text" placeholder="Enter a comment" name="comment">
                    <input class="Button"  type="submit" name="addComment" value="Add comment">
                    <input type="hidden" name="id" value="<?php echo $booksData[$i]['ReviewID']; ?>">
                    <input type="hidden" name="user" value="<?php echo (isset($_GET['user'])) ? $_GET['user'] : NULL ?>">
                </form>

                <p id="<?php echo $booksData[$i]['ReviewID'] ?>"> <span class="Attributes"> Comments:</span> </p>

                <!--
                    An appropriate function from the db_operations.php file is called that selects all the required comments data for each review
                    so that it can be displayed onto the web page.
                    An if statement is added inside the block of code below to determine if the comment belongs to the user, if so then delete form can be added
                -->
                <?php list($comments, $commentsDateCreated, $user, $commentId) = selectCommentsFromReview($conn, $booksData[$i]['ReviewID']);?>
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