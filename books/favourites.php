<?php 
session_start();

include "../db_operations.php";
include "../config/db_connection.php";


if(isset($_SESSION['User'])){
    $booksData = selectAllFavouriteBooks($conn, $_SESSION['User']);
}

if(isset($_POST['deleteFavourite'])){
    deleteFromFavourites($conn, $_SESSION['User'], $_POST['deleteReviewID']);
    header('Location: favourites.php');
    exit();
}

?>

<?php include '../templates/header.php'; ?>

<?php if(isset($_SESSION['User'])){ ?>
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
                <?php if( $booksData[$i]['Review'] != null ){ ?>
                    <p><span class="Attributes">Review:</span> <?php echo $booksData[$i]['Review']?></p>
                    <p><span class="Attributes">Rating:</span> <?php echo $booksData[$i]['Rating']?></p>
                <?php } ?>
                <form action="favourites.php" method="post">
                    <input class="Button" type="submit" name="deleteFavourite" value="Remove from favourites">
                    <input type="hidden" name="deleteReviewID" value="<?php echo $booksData[$i]['ReviewID'] ?>">
                </form>
            </div>

            <hr>
            
        <?php } ?>

    <?php } else { ?>
        <h1> No favourite books found </h1>
    <?php } ?>

<?php } else { ?>
    <h1> Login/register required </h1>
<?php } ?>
