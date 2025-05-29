<?php //include'../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<nav class="navbar navbar-expand-lg  navbar bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"> StoreTrackr </a>

    <?php
    $currentPage = basename($_SERVER['PHP_SELF']);
    if (isset($_SESSION['user'])) {
      if ($currentPage === 'adminOrProduct.php' || $currentPage === 'listProduct.php' || $currentPage === 'listUsers.php') {
        echo '<a href=" ' . base_url . 'views/logout.php ">
                    <button class="btn btn-outline-light" type="submit">Logout</button> 
                </a>';
      }
    } else {
      if ($currentPage === 'signup.php') {
        echo '<a href="' . base_url . 'views/login.php">
                <button class="btn btn-outline-light" type="submit">Login</button>
            </a>';
      } else if ($currentPage === 'login.php') {
        echo '<a href="' . base_url . 'views/signup.php">
                <button class="btn btn-outline-light" type="submit">Signup</button>
            </a>';
      }
    }
    ?>


  </div>
</nav>