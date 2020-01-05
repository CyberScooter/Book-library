
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
    if((int) $pagesRead <= (int) $totalPages && $pagesRead >= 0){
        if(isset($_POST['review'])){
            $review = htmlspecialchars($_POST['review']);
            $rating = htmlspecialchars($_POST['rating']);
        }else {
            $review = NULL;
            $rating = 0;
        }
        if(checkIfStandardUser($conn, $_SESSION['User'])){
            $previousVisibility = $_POST['previousVisibility'] == 'visible' ? true : false;
            if(!$visible){
                decrementPrivatePostReviews($conn, $_SESSION['User']);
            }else if(!$previousVisibility && $visible){
                incrementPrivatePostReviews($conn, $_SESSION['User']);
            }
        }
        ($rating > 10 || $rating < 0)? $_SESSION['errmessage'] = "Invalid rating please retry, rating should be between 0-10": $bookReviewData = updateUserBookReview($conn, $email, $id, $pagesRead, $review, $rating, $visible);
    }
    header('Location: /books/index.php');
    
}

?>

<?php include '../templates/header.php'; ?>

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
            <input class="TextBox" type="text" value="<?php echo $bookReviewData['Page'] ?>" placeholder="Enter pages read" name="pagesRead">
            
            <input type="checkbox" name="visibility" value="visible" <?php echo ($bookReviewData['Visible']) ? 'checked' : null ?>> Public </input>
            <input type="hidden" name="previousVisiblity" value="<?php echo ($bookReviewData['Visible']) ? 'visible' : null ?>" >

            <?php if($showReviewInputs){ ?>
            <input class="TextBox" type="text" value="<?php echo $bookReviewData['Review'] ?>" placeholder="Enter review" name="review">
            <input class="TextBox" type="text" value="<?php echo $bookReviewData['Rating'] ?>" placeholder="Enter rating" name="rating">
            <?php } ?>
            <input type="hidden" name="id" value="<?php echo $bookReviewID; ?>">
            <input class="Search" type="submit" name="submit" value="Update book"> 
        </form>
    <?php }else { ?>
        <h1> Login/register required </h1>

    <?php } ?>

<?php include '../templates/footer.php'; ?>