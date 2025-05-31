<?php
    include_once dirname(__DIR__) .  '/classes/DBConnection.php';
    include_once dirname(__DIR__) . '/classes/auth.php';
    include '../config/config.php';
    include '../includes/head.php';
    include '../includes/navbar.php';

    if(session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    $user = new Users();
    if(isset($_POST['submit'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $message = $user->login($email, $password);
    }
?>

<title>Login</title>
<h2 class="text-center" style="margin-top: 50px;">Login to StoreTrackr</h2>
<form action="" method="post" style="display: flex; flex-direction: column; align-items: center; margin-top: 100px;">
  <div class="form-group col-md-6"  style="margin-bottom: 20px; width: 50%;">
    <label for="email">Email</label>
    <input type="email" class="form-control" id="email" name="email" aria-describedby="username" placeholder="Enter email" required>
  </div>
  <div class="form-group col-md-6"  style="margin-bottom: 20px; width: 50%;">
    <label for="exampleInputPassword1">Password</label>
    <input type="password" class="form-control" id="exampleInputPassword1" name="password" placeholder="Enter password" required>
  </div>
  <button type="submit" class="btn btn-primary" style="margin-top: 10px;" name="submit">Login</button>
</form>

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

<?php if (isset($_GET['msg'])): ?>
<script>
    Swal.fire({
        toast: true,
        position: 'top',
        icon: 'success',
        title: <?= json_encode($_GET['msg']) ?>,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: false
    });
</script>
<?php endif; ?>

<?php
    include '../includes/footer.php';
?>