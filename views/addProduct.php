<?php
    include_once dirname(__DIR__) . '/classes/DBConnection.php';
    include_once dirname(__DIR__) . '/classes/products.php';
    include '../includes/head.php';
    include '../includes/navbar.php';

    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $obj = new Products();
    if(isset($_POST['submit'])) {
        $name = $_POST['name'];
        $size = $_POST['size'] ?? NULL;
        $price = $_POST['price'];
        $color = $_POST['color'];
        $image = $_FILES['image'];

        $message = $obj->addProduct($name, $price, $size, $color, $image);
    }
?>

<h2 class="text-center" style="margin-top: 50px;">Add Products</h2>
<div class="container">
    <form action="<?= $_SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data" style="display: flex; flex-direction: column; align-items: center; margin-top: 60px;">
        <div class="mb-3 col-md-6">
            <label for="name" class="form-label">Product name</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="name" required>

            <label for="price" class="form-label">Price</label>
            <input type="text" class="form-control" id="price" name="price" aria-describedby="price" required>

            <label for="color" class="form-label">Color</label>
            <input type="text" class="form-control" id="color" name="color" aria-describedby="color" required>

            <label for="image" class="form-label">Upload images</label>
            <input type="file" class="form-control" id="image" name="image[]" aria-describedby="image" multiple required>

            <label for="size" class="form-label">size</label>
            <select class="form-control" id="size" name="size" aria-label="Default select example" required> 
                <option value="" disabled selected>Select Size</option>
                <option value="Small">S</option>
                <option value="Medium">M</option>
                <option value="Large">L</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 30px;" name="submit">Add Product</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (!empty($message)): ?>
<script>
  Swal.fire({
    toast: true,
    position: 'top',
    icon: 'success',
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