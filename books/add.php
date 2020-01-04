<?php
    session_start();
    include "../db_operations.php";
    include "../config/db_connection.php";

    $showReviewInputs = false;
    
    if(isset($_POST['submit'])){
        $email = htmlspecialchars($_SESSION['User']);
        $isbn = htmlspecialchars($_POST['isbn']);
        $title = htmlspecialchars($_POST['title']);
        $releaseDate = htmlspecialchars($_POST['releasedDate']);
        $description = htmlspecialchars($_POST['description']);
        $author = htmlspecialchars($_POST['author']);
        $authorDOB = htmlspecialchars($_POST['authorDOB']);
        $totalPages = htmlspecialchars($_POST['totalPages']);
        $pagesRead = htmlspecialchars($_POST['pagesRead']);
        $picture = htmlspecialchars($_POST['fileinput']);
        $badge = null;
        $review = NULL;
        $rating = 0;
        if(isset($_POST['review'])){
            $review = htmlspecialchars($_POST['review']);
            $rating = htmlspecialchars($_POST['rating']);
        }
        if($pagesRead == $totalPages){
            $showReviewInputs = true;
        }
        $visible = $_POST['visibility'] == 'visible' ? true : false;

        if($pagesRead <= $totalPages && $email != null && $isbn != null && $title != null && $releaseDate && $description != null && $author != null && $authorDOB != null && $totalPages != null && $pagesRead != null){

            if(checkIfStandardUser($conn, $_SESSION['User'])){
                !$visible ? decrementPrivatePostReviews($conn, $_SESSION['User']) : NULL;
                decrementStandardLimitReviews($conn, $_SESSION['User']);
            }
            saveBookReview($conn, $email, $isbn, $title, $releaseDate, $description, $author, $authorDOB, $totalPages, $pagesRead, $review, $rating, $picture, $visible);
            header('Location: /books/index.php');
            exit();
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
            <input type="checkbox" name="visibility" value="visible" checked>Public</input>
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