
<?php
session_start();

include "../db_operations.php";
include "../config/db_connection.php";

if(isset($_GET['id'])){
    $bookReviewID = $_GET['id'];
    $bookReviewData = getOneUserBookReview($conn, $_SESSION['User'], $bookReviewID);
    $showReviewInputs = checkPagesReadAndTotalPagesEqual($conn, $bookReviewID, $_SESSION['User']);
}

if(isset($_POST['submit'])){
    $email = $_SESSION['User'];
    $id = htmlspecialchars($_POST['id']);
    $pagesRead = htmlspecialchars($_POST['pagesRead']);
    $totalPages = htmlspecialchars($_POST['totalPages']);
    $visible = $_POST['visibility'] == 'visible' ? true : false;
    if((int) $pagesRead <= (int) $totalPages && (int) $pagesRead >= 0){
        if(isset($_POST['review'])){
            $review = htmlspecialchars($_POST['review']);
            $rating = htmlspecialchars($_POST['rating']);
        }else {
            $review = NULL;
            $rating = 0;
        }
        if((int) $rating > 10 || (int)  $rating < 0){
            $_SESSION['errmessage'] = "Invalid rating please try again, rating should be between 0-10";
            header('Location: index.php');
            exit();
        }
        if(checkIfStandardUser($conn, $_SESSION['User'])){
            $previousVisibility = $_POST['previousVisibility'] == 'visible' ? true : false;
            if($visible != $previousVisibility){ //if visibility option has changed from before
                if(!$visible){
                    decrementPrivateReviews($conn, $_SESSION['User']);
                    $_SESSION['successmessage'] = "Book review set to private";
                }else if(!$previousVisibility && $visible){
                    incrementPrivateReviews($conn, $_SESSION['User']);
                    $_SESSION['successmessage'] = "Book review set to public";
                }
            }
        }
        updateUserBookReview($conn, $email, $id, $pagesRead, $review, $rating, $visible);
        mysqli_close($conn);
        header('Location: /books/index.php');
        exit();
    }
    $_SESSION['errmessage'] = "Invalid pages read, could not update book";
    header('Location: /books/index.php');
    exit();
}

global $error; 
if(isset($_SESSION['errmessage'])){ 
    $error = $_SESSION['errmessage'];
    unset($_SESSION['errmessage']); 
} 

?>

<?php include '../templates/header.php'; ?>

<h1 class="error"><?php echo $error?></h1>

<div class="container">

<body>

    <?php if(isset($_SESSION['User'])){ ?>
        <form action="edit.php" method="POST">
            <input id="FixedTextBox" class="TextBox" type="text" value="<?php echo $bookReviewData['ISBN'] ?>" placeholder="Enter Book ISBN" readonly>
            <input id="FixedTextBox" class="TextBox" type="text" value="<?php echo $bookReviewData['Title'] ?>" placeholder="Enter Book Title" readonly>
            <input id="FixedTextBox" class="TextBox" type="text" value="<?php echo $bookReviewData['DateReleased'] ?>" placeholder="Enter Book release date in (yyyy-mm-dd) format" readonly>
            <input id="FixedTextBox" class="TextBox" type="text" value="<?php echo $bookReviewData['Description'] ?>" placeholder="Enter Book description" readonly>
            <input id="FixedTextBox" class="TextBox" type="text" value="<?php echo $bookReviewData['Author'] ?>" placeholder="Enter author" readonly>
            <input id="FixedTextBox" class="TextBox" type="text" value="<?php echo $bookReviewData['DOB'] ?>" placeholder="Enter author DOB in (yyyy-mm-dd) format" readonly>
            <input id="FixedTextBox" class="TextBox" type="text" value="<?php echo $bookReviewData['TotalPages'] ?>" placeholder="Enter total pages" name="totalPages" readonly>
            <h3>Checkbox should be ticked for review to be public</h3>
            <input type="checkbox" name="visibility" value="visible" <?php echo ($bookReviewData['Visible']) ? 'checked' : null ?>> Public </input>
            <h3>If pages read is the same as total pages in book then rating and review feature is unlocked</h3>
            <input class="TextBox" type="text" value="<?php echo $bookReviewData['Page'] ?>" placeholder="Enter pages read" name="pagesRead">

            <input type="hidden" name="previousVisibility" value="<?php echo ($bookReviewData['Visible']) ? 'visible' : null ?>" >

            <?php if($showReviewInputs){ ?>
            <input class="TextBox" type="text" value="<?php echo $bookReviewData['Review'] ?>" placeholder="Enter review" name="review">
            <input class="TextBox" type="text" value="<?php echo $bookReviewData['Rating'] ?>" placeholder="Enter rating between 0-10" name="rating">
            <?php } ?>
            <input type="hidden" name="id" value="<?php echo $bookReviewID; ?>">
            <input class="Search" type="submit" name="submit" value="Update book"> 
        </form>
    <?php }else { ?>
        <h1> Login/register required </h1>

    <?php } ?>

</div>

</body>
</html>