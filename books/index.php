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

//If user is on their own profile:
if(isset($_SESSION['User'])){
    $profileData = getProfileData($conn, $_SESSION['User']);
    $username = getUsernameFromUsersTable($conn, $_SESSION['User']);
    $booksData = getAllUserBookReviews($conn, $username);
    $premiumButton = checkIfStandardUser($conn, $_SESSION['User']);
}

if(isset($_POST['deleteBook'])){
    $deleteISBN = $_POST['deleteISBN'];
    $deleteReviewID = $_POST['deleteReviewID'];
    $deleteAuthor = $_POST['deleteAuthor'];
    deleteUserBookReview($conn, $_SESSION['User'], $deleteReviewID, $deleteISBN, $deleteAuthor);
    $_SESSION['successmessage'] = "Book review successfully removed";
    header('Location: index.php');
    exit();
}

if(isset($_POST['favouriteBook'])){
    addBookToFavourite($conn, $_SESSION['User'], $_POST['ReviewID']);
    $_SESSION['successmessage'] = "Added book review to favourites";
    header('Location: favourites.php');
    exit();
}

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
    $_SESSION['successmessage'] = "Comment successfully removed";
    if($_POST['user'] != null){
        header('Location: ?user=' . $_POST['user']);
        exit();
    }else{
        header('Location: index.php');
        exit();
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

<?php if(isset($_SESSION['User'])){ ?>
    <?php echo (checkStandardBooksLimit($conn, $_SESSION['User']) || checkIfPremiumUser($conn, $_SESSION['User'])) ? '<a class="AddButton" href="../books/add.php"> Add Book </a>' : NULL ?>
    <?php if($booksData != null){ ?>

        <?php foreach($booksData as $i => $item){ ?>
            
            <!-- CSS properties used to make it easier to visualise and see the data on the web page  -->
            <div class="BookReview">

                <p><span class="Attributes">ISBN:</span> <?php echo $booksData[$i]['ISBN'] ?></p>
                <p><span class="Attributes">Title:</span> <?php echo $booksData[$i]['Title'] ?></p>
                <p> <img src="/resources/books/<?php echo $booksData[$i]['Picture'] ?>" /></p>
                <p><span class="Attributes">Description:</span> <?php echo $booksData[$i]['Description'] ?></p>
                <p><span class="Attributes">Author:</span> <?php echo $booksData[$i]['Author'] ?></p>
                <p><span class="Attributes">Date released:</span> <?php echo $booksData[$i]['DateReleased'] ?></p>
                <p><span class="Attributes">Read</span> <?php echo $booksData[$i]['Page'] ?> out of <?php echo $booksData[$i]['TotalPages'] ?> pages in the book</p>
                <p <?php echo ($booksData[$i]['Visible']) ? 'class="success"' : 'class="error"' ?>><?php echo ($booksData[$i]['Visible']) ? 'Public review' : 'Private review' ?></p>
                <p><span class="Attributes">Created at :</span> <?php echo $booksData[$i]['created_at']?></p>

                <?php if( (int) $booksData[$i]['Page'] == (int) $booksData[$i]['TotalPages']   ){ ?>
                    <p><span class="Attributes">Review:</span> <?php echo $booksData[$i]['Review']?></p>
                    <p><span class="Attributes">Rating:</span> <?php echo $booksData[$i]['Rating']?></p>
                <?php } ?>

                <a class="Button" href="/books/edit.php?id=<?php echo $booksData[$i]['ID'] ?>"> Update Book details</a>
                <form action="index.php" method="post">
                    <input class="Button" type="submit" name="deleteBook" value="Delete Book">
                    <input type="hidden" name="deleteISBN" value="<?php echo $booksData[$i]['ISBN'] ?>">
                    <input type="hidden" name="deleteReviewID" value="<?php echo $booksData[$i]['ReviewID'] ?>">
                    <input type="hidden" name="deleteAuthor" value="<?php echo $booksData[$i]['Author'] ?>">
                </form>

                <?php if(!checkFavouriteBook($conn, $_SESSION['User'], $booksData[$i]['ReviewID'])) {?>
                    <form action="index.php" method="post">
                        <input class="Button" type="submit" name="favouriteBook" value="Add book to favourite">
                        <input type="hidden" name="ReviewID" value="<?php echo $booksData[$i]['ReviewID'] ?>">
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

                <p id="<?php echo $booksData[$i]['ReviewID'] ?>"> <span class="Attributes"> Comments: </span></p>

                <!--
                    An appropriate function from the db_operations.php file is called that selects all the required commenting data for the review
                    so that it can be displayed onto the web page.
                    An if statement is added inside the block of code below to determine if the comment belongs to the user, if so then delete option can be added
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

        </div>
        <?php } ?>

    <?php }else { ?>

        <h1> No books found, try adding one </h1>

    <?php }?>

<?php } else { ?>

    <h1> Login/register required </h1>
    
<?php } ?>




<?php include '../templates/footer.php'; ?>