<?php
    session_start();
    include "../db_operations.php";
    include "../config/db_connection.php";

    $showReviewInputs = false;

    if(isset($_POST['submit'])){
        $email = $_SESSION['User'];
        $isbn = $_POST['isbn'];
        $title = $_POST['title'];
        $releaseDate = $_POST['releasedDate'];
        $description = $_POST['description'];
        $author = $_POST['author'];
        $authorDOB = $_POST['authorDOB'];
        $totalPages = $_POST['totalPages'];
        $pagesRead = $_POST['pagesRead'];
        $picture = $_POST['fileinput'];
        if(isset($_POST['review'])){
            $review = $_POST['review'];
            $rating = $_POST['rating'];
        }else {
            $review = NULL;
            $rating = 0;
        }
        if($pagesRead == $totalPages){
            $showReviewInputs = true;
        }
        if($pagesRead <= $totalPages && $email != null && $isbn != null && $title != null && $releaseDate && $description != null && $author != null && $authorDOB != null && $totalPages != null && $pagesRead != null){
            saveBookReview($conn, $email, $isbn, $title, $releaseDate, $description, $author, $authorDOB, $totalPages, $pagesRead, $review, $rating, $picture);
        }

    }
?>


<?php include '../templates/header.php'; ?>

<body>

    <?php if(isset($_SESSION['User'])){ ?>
        <form action="add.php" method="POST">
            <input class="TextBox" type="text" placeholder="Enter Book ISBN" name="isbn">
            <input class="TextBox" type="text" placeholder="Enter Book Title" name="title">
            <input class="TextBox" type="text" placeholder="Enter Book release date in (yyyy-mm-dd) format" name="releasedDate">
            <input class="TextBox" type="text" placeholder="Enter Book description" name="description">
            <input class="TextBox" type="text" placeholder="Enter author" name="author">
            <input class="TextBox" type="text" placeholder="Enter author DOB in (yyyy-mm-dd) format" name="authorDOB">
            <input class="TextBox" type="text" placeholder="Enter total pages" name="totalPages">
            <input class="TextBox" type="text" placeholder="Enter pages read" name="pagesRead">
            <input class="TextBox" type="file" name="fileinput"/>
            <?php if($showReviewInputs){ ?>
            <input class="TextBox" type="text" placeholder="Enter review" name="review">
            <input class="TextBox" type="text" placeholder="Enter rating" name="rating">
            <?php } ?>
            <input class="Search" type="submit" name="submit" value="Add book"> 
        </form>
    <?php }else { ?>
        <h1> Login/register required </h1>

    <?php } ?>

<?php include '../templates/footer.php'; ?>