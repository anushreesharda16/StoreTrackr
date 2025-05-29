<?php
include_once dirname(__DIR__) . '/classes/DBConnection.php';
include_once dirname(__DIR__) . '/classes/users.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location:../views/login.php');
    exit();
}

$idToDelete = (int)$_GET['id'];
$loggedInUserId = $_SESSION['user']['id'];

$obj = new Users();
$obj->deleteUser($idToDelete);

if ($loggedInUserId == $idToDelete) {
    session_destroy();
    header('Location:../views/login.php');
    exit();
}

header('Location:./listUsers.php');
exit();
?>