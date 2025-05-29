<?php
include_once dirname(__DIR__) . '/classes/DBConnection.php';
include_once dirname(__DIR__) . '/classes/users.php';
include '../includes/head.php';
include '../includes/navbar.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location:./login.php');
    exit();
}
$userObj = new Users();
$dir = dirname(__DIR__) . '/uploads/user-profile-img/';
if (isset($_GET['id'])) {
    $user = $userObj->userById($_GET['id']); 
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $country = $_POST['country'];
    $profileImg = $_FILES['profile_image'];

    $updatedUser = $userObj->editUser($id, $name, $country, $profileImg);

    if ($updatedUser) {
        header("Location:./listUsers.php");
        exit();
    } else {
        return "Error updating user.";
    }
}
?>

<h3 class="text-center" style="margin-top: 50px;">Edit User Details</h3>
<form action="" method="post" enctype="multipart/form-data" style="display: flex; flex-direction: column; align-items: center; margin-top: 80px;">
    <div class="mb-3 col-md-6">
        <input type="hidden" name="id" value="<?= $user['id'] ?>">

        <label for="username" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" aria-describedby="name" value="<?php echo ($user['name']); ?>">

        <label for="country" class="form-label">Country</label>
        <input type="text" class="form-control" id="country" name="country" aria-describedby="country" value="<?php echo ($user['country']); ?>">

        <label for="image" class="form-label">Upload image</label> 
        <p>Current Image:</p>
        <img src="../uploads/user-profile-img/<?=$user['profile_img'] ?>" width="60" height="60" class="mb-2 d-block">
        <input type="file" class="form-control" id="image" name="profile_image" aria-describedby="imageHelp">
        <small id="imageHelp" class="form-text text-muted">Leave empty if you don't want to change the profile image.</small>
        <!-- <input type="file" class="form-control" id="image" name="image" aria-describedby="image" value=" ?>">  -->
        
    </div>
    <button type="submit" name="submit" class="btn btn-primary" style="margin-top: 30px;">Update</button>
</form>

<?php include '../includes/footer.php';
?>
