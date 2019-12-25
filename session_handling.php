<?php
session_start();

function verifyUser($email){
    if(isset($_SESSION['User'])){
        return true;
    }
    return false;
}



?>