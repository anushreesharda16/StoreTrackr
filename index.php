<?php
include './config/config.php';
include './includes/head.php';
include './includes/navbar.php';
?>

<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="text-center">
        <h1>Welcome to <span class="text-primary">StoreTrackr</span>!</h1>
        <p class="lead">Manage your store products easily and efficiently.</p>
        <a href="<?= base_url ?>views/login.php">
            <button class="btn btn-primary mt-3">Login</button>
        </a>
    </div>
</div>


<?php
include './includes/footer.php';
?>