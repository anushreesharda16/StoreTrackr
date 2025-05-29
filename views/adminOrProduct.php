<?php
    include '../config/config.php';
    include '../includes/head.php';
    include '../includes/navbar.php';
    if(!isset($_SESSION['user'])) {
        header('Location:./login.php');
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome | StoreTrackr</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
  <style>
    body {
      background: #f8f9fa;
    }
    .welcome-box {
      background: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    }
    h1 {
      font-weight: bold;
    }
    .btn-custom {
      width: 200px;
    }
  </style>
</head>
<body>
  <div class="d-flex justify-content-center align-items-center vh-100">
    <div class="welcome-box text-center">
      <h1>Welcome to <span class="text-primary">StoreTrackr</span>!</h1>
      <p class="lead mb-4">Manage and store your products effortlessly.</p>

      <a href="./listProduct.php">
        <button class="btn btn-primary btn-custom mb-3">Manage Products</button>
      </a>
      <br>
      <a href="../user_views/listUsers.php">
        <button class="btn btn-outline-secondary btn-custom">Manage Users</button>
      </a>
    </div>
  </div>
</body>
</html>