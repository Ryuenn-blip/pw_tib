<?php

if(
!isset($_SESSION['user']) ||
$_SESSION['user']['role'] != 'admin'
){
    header("Location: ../index.php");
    exit;
}