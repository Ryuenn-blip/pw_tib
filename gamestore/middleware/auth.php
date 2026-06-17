<?php

if(!isset($_SESSION['user'])){

    header("Location: ../auth/login.php");
    exit;
}