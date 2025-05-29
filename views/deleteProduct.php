<?php
    include_once dirname(__DIR__) . '/classes/DBConnection.php';
    include_once dirname(__DIR__) . '/classes/products.php';

    if(!isset($_GET['id'])) {
        die("Id is not available.");
    }

    $obj = new Products();
    $obj->deleteProduct($_GET['id']);
?>