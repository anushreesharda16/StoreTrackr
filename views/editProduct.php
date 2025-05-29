<?php
include_once dirname(__DIR__) . '/classes/DBConnection.php';
include_once dirname(__DIR__) . '/classes/products.php';
include '../includes/head.php';
include '../includes/navbar.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header('Location: ./login.php');
    exit();
}

$conn = (new DatabaseConnect())->connect();
$productId = $_GET['id'];

if (!$productId) {
    die('Product ID not available.');
}
// Retrieving the product details in order to show the value of the products for different parameters
$productQuery = $conn->prepare("SELECT * FROM products WHERE id = ?");
$productQuery->bind_param('s', $productId);
$productQuery->execute();
$productResult = $productQuery->get_result();
$product = $productResult->fetch_assoc();
$productQuery->close();

if (!$product) {
    die('Product is not available.');
}
// Retreiving images of the product
$imageQuery = $conn->prepare("SELECT * FROM product_images WHERE product_id = ?");
$imageQuery->bind_param('s', $productId);
$imageQuery->execute();
$imageResult = $imageQuery->get_result();
$images = [];
while ($row = $imageResult->fetch_assoc()) {
    $images[] = $row;
}

if (isset($_POST['submit'])) {
    $dataToUpdate = [
        'name'  => $_POST['name'],
        'price' => $_POST['price'],
        'color' => $_POST['color'],
        'size'  => $_POST['size']
    ];
    $image = $_FILES['image'];
    $productObj = new Products();
    $deleteImages = $_POST['delete_images'] ?? [];
    $message = $productObj->updateProduct($productId, $dataToUpdate, $image, $deleteImages);
    header("Location: listProduct.php");
    exit();
}
?>

<h3 class="text-center mt-5">Edit Product</h3>
<form action="" method="post" enctype="multipart/form-data" class="d-flex flex-column align-items-center mt-5">
    <div class="mb-3 col-md-6">
        <label for="name" class="form-label">Product Name</label>
        <input type="text" class="form-control" id="name" name="name" value="<?= $product['name'] ?>">

        <label for="price" class="form-label mt-3">Price</label>
        <input type="text" class="form-control" id="price" name="price" value="<?= $product['price'] ?>">

        <label for="color" class="form-label mt-3">Color</label>
        <input type="text" class="form-control" id="color" name="color" value="<?= $product['color'] ?>">

        <label class="form-label mt-3">Current Images</label><br>
        <?php foreach ($images as $img): ?>
            <div class="mb-2">
                <img src="../uploads/product-images/<?= $img['image_path'] ?>" width="50" height="50" style="margin-right: 10px;">
                <label>
                    <input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>"> Delete
                </label>
            </div>
        <?php endforeach; ?>

        <label for="image" class="form-label mt-3">Upload New Images</label>
        <input type="file" class="form-control" id="image" name="image[]" multiple>

        <label for="size" class="form-label mt-3">Size</label>
        <select class="form-control" id="size" name="size">
            <option value="">Select Size</option>
            <option value="Small" <?= $product['size'] == "Small" ? 'selected' : '' ?>>Small</option>
            <option value="Medium" <?= $product['size'] == "Medium" ? 'selected' : '' ?>>Medium</option>
            <option value="Large" <?= $product['size'] == "Large" ? 'selected' : '' ?>>Large</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary mt-4" name="submit">Update</button>
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

<?php
include '../includes/footer.php';
?>