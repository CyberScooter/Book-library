<?php
    session_start();
    include "../db_operations.php";
    include "../config/db_connection.php";

    $showReviewInputs = false;
    //if submit button is pressed
    if(isset($_POST['submit'])){
        $email = $_SESSION['User'];
        $isbn = htmlspecialchars($_POST['isbn']);
        $title = htmlspecialchars($_POST['title']);
        $releaseDate = htmlspecialchars($_POST['releasedDate']);
        $description = htmlspecialchars($_POST['description']);
        $author = htmlspecialchars($_POST['author']);
        $authorDOB = htmlspecialchars($_POST['authorDOB']);

        $authorDOBSplit = explode('-', $authorDOB);
        $releaseDateSplit = explode('-', $releaseDate);
        $checkDateAuthor = checkdate($authorDOBSplit[1], $authorDOBSplit[0], $authorDOBSplit[2]);
        $checkDateReleaseDate = checkdate($releaseDateSplit[1], $releaseDateSplit[0], $releaseDateSplit[2]);

        if(sizeof($authorDOBSplit) != 3 || sizeof($releaseDateSplit) != 3 || !$checkDateAuthor || !$checkDateReleaseDate){
            $_SESSION['errmessage'] = "Date is invalid";
            header('Location: add.php');
            exit();

        }

        $sqlAuthorDOB = date("Y-m-d",strtotime($authorDOB));
        $sqlReleaseDate = date("Y-m-d",strtotime($releaseDate));

        $totalPages = htmlspecialchars($_POST['totalPages']);
        $pagesRead = htmlspecialchars($_POST['pagesRead']);
        if(!is_numeric($pagesRead) || !is_numeric($totalPages)){
            $_SESSION['errmessage'] = "Pages inputs are not numbers";
            header('Location: add.php');
            exit();
        }
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


        if($pagesRead <= $totalPages && $email != null && $isbn != null && $title != null && $releaseDate && $description != null && $author != null && $authorDOB != null && $totalPages != null && $pagesRead != null){
            $visible = $_POST['visibility'] == 'visible' ? true : false;
            saveBookReview($conn, $email, $isbn, $title, $sqlReleaseDate, $description, $author, $sqlAuthorDOB, $totalPages, $pagesRead, $review, $rating, $picture, $visible);
            header('Location: /books/index.php');
            exit();
        }
        $_SESSION['errmessage'] = "Not all fields filled in or pages read is greater than total pages";
        header('Location: add.php');
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

        <form action="add.php" method="POST">
            <input class="TextBox" type="text" placeholder="Enter Book ISBN" name="isbn">
            <input class="TextBox" type="text" placeholder="Enter Book Title" name="title">
            <input class="TextBox" type="text" placeholder="Enter Book release date in (dd-mm-yyyy) format" name="releasedDate">
            <input class="TextBox" type="text" placeholder="Enter Book description" name="description">
            <input class="TextBox" type="text" placeholder="Enter author" name="author">
            <input class="TextBox" type="text" placeholder="Enter author DOB in (dd-mm-yyyy) format" name="authorDOB">
            <input class="TextBox" type="text" placeholder="Enter total pages" name="totalPages">
            <input class="TextBox" type="text" placeholder="Enter pages read" name="pagesRead">
            <h3>Checkbox should be ticked for book review to be public</h3>
            <input type="checkbox" name="visibility" value="visible" checked>Public</input>
            <h3>Select a book cover image from 'books' folder in 'resources' folder to add a book cover: </h3>
            <input class="TextBox" type="file" name="fileinput"/>
            <?php if($showReviewInputs){ ?>
            <input class="TextBox" type="text" placeholder="Enter review" name="review">
            <input class="TextBox" type="text" placeholder="Enter rating between 0-10" name="rating">
            <?php } ?>
            <input class="Search" type="submit" name="submit" value="Add book"> 
        </form>
    <?php }else { ?>
        <h1> Login/register required </h1>

    <?php } ?>

</div>

</body>
</html>