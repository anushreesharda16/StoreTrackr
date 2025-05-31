<?php
include '../classes/auth.php';
include '../config/config.php';
include '../includes/head.php';
include '../includes/navbar.php';

$user = new Users();
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $country = $_POST['country'];
    $password = $_POST['password'];
    $profileImg = $_FILES['profileImg'];

    $message = $user->register($name, $email, $password,  $country, $profileImg);
}
?>

<title>Signup</title>
<h2 class="text-center" style="margin-top: 50px;">Signup to StoreTrackr</h2>
<div class="container">
    <form action="" method="post" style="display: flex; flex-direction: column; align-items: center; margin-top: 80px;" enctype="multipart/form-data">
        <div class="mb-3 col-md-6">
            <label for="name" class="form-label">Name</label>
            <input type="name" class="form-control" id="name" name="name" aria-describedby="name" required>
            <label for="exampleInputEmail1" class="form-label">Email</label>
            <input type="email" class="form-control" id="exampleInputEmail1" name="email" aria-describedby="emailHelp" required>
            <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
            <label for="country" class="form-label">Country</label>
            <input type="text" class="form-control" id="country" name="country" aria-describedby="country">
            <label for="profileImg" class="form-label">Upload your profile photo here..</label>
            <input type="file" class="form-control" name="profileImg" id="profileImg">
            <label for="exampleInputPassword1" class="form-label">Password</label>
            <input type="password" class="form-control" id="exampleInputPassword1" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary" name="submit">Signup</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (!empty($message)): ?>
    <script>
        Swal.fire({
            toast: true,
            position: 'top',
            icon: 'error',
            title: <?= json_encode($message) ?>,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: false
        });
    </script>
<?php endif; ?>

<?php
include '../includes/footer.php';
?>