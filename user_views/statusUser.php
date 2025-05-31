<?php
include '../config/config.php';
include '../classes/DBConnection.php';
include '../classes/users.php';

if(isset($_POST['toggle_status'])) {
    $userId = $_POST['user_id'];
    $currentStatus = $_POST['current_status'];
    $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';

    $conn = (new DatabaseConnect()) -> connect();
    // $conn->connect();
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $newStatus, $userId);
    $stmt->execute();
    $stmt->close();
    header('Location:./listUsers.php');
}


?>