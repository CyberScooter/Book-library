
<?php
session_start();

include "../db_operations.php";
include "../config/db_connection.php";


$bookReviewID = $_GET['id'];

global $bookReviewData;

if(isset($_POST['submit']){
    $bookReviewData = getOneUserBookReview($conn, $_SESSION['User']);
})






?>





<?php include '../templates/header.php'; ?>

<body>

    <?php if(isset($_SESSION['User'])){ ?>
        <form action="add.php" method="POST">
            <input class="TextBox" type="text" value="<?php echo $bookReviewArray[] ?>" placeholder="Enter Book ISBN" name="isbn">
            <input class="TextBox" type="text" value="<?php echo  ?>" placeholder="Enter Book Title" name="title">
            <input class="TextBox" type="text" value="<?php echo  ?>" placeholder="Enter Book release date in (yyyy-mm-dd) format" name="releasedDate">
            <input class="TextBox" type="text" value="<?php echo  ?>" placeholder="Enter Book description" name="description">
            <input class="TextBox" type="text" value="<?php echo  ?>" placeholder="Enter author" name="author">
            <input class="TextBox" type="text" value="<?php echo  ?>" placeholder="Enter author DOB in (yyyy-mm-dd) format" name="authorDOB">
            <input class="TextBox" type="text" value="<?php echo  ?>" placeholder="Enter total pages" name="totalPages">
            <input class="TextBox" type="text" value="<?php echo  ?>" placeholder="Enter pages read" name="pagesRead">
            <input class="TextBox" type="file" name="fileinput"/>
            <?php if($showReviewInputs){ ?>
            <input class="TextBox" type="text" value="<?php echo  ?>" placeholder="Enter review" name="review">
            <input class="TextBox" type="text" value="<?php echo  ?>" placeholder="Enter rating" name="rating">
            <?php } ?>
            <input class="Search" type="submit" name="submit" value="Add book"> 
        </form>
    <?php }else { ?>
        <h1> Login/register required </h1>

    <?php } ?>

<?php include '../templates/footer.php'; ?>