<?php
include_once dirname(__DIR__) . '/classes/DBConnection.php';
include_once dirname(__DIR__) . '/classes/products.php';
include '../config/config.php';
include '../includes/navbar.php';
include '../includes/head.php';

if (!isset($_SESSION['user'])) {
  header('Location:./login.php');
  exit();
}
$obj = new Products();
$products = [];

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = (isset($_GET['perPage']) && $_GET['perPage'] > 0) ? (int) $_GET['perPage'] : 10;
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? '';
//  print_r($_GET['perPage']);
$products = $obj->listFilteredProducts($sort, $search, $page, $perPage);

//for pagination
$totalProducts = $obj->countFilteredProducts($search);
$totalPages = ceil((int)$totalProducts / $perPage);
?>

<h2 class="text-center" style="margin-top: 50px;">Products</h2>

<div class="d-flex justify-content-between mb-3" style="padding-left: 50px; padding-right: 50px;">
  <a href="./addProduct.php">
    <button type="button" class="btn btn-outline-info">Add new product</button>
  </a>
  <div class="d-flex">
    <form action="listProduct.php" method="get" class="d-flex mr-2">
      <select name="perPage" id="page" class="form-control mr-2" onchange="this.form.submit()">
        <option value="">Products per page</option>
        <?php foreach ([10, 20, 30, 40, 50] as $num): ?>
          <option value="<?= $num ?>" <?= $perPage === $num ? 'selected' : '' ?>><?= $num ?></option>
        <?php endforeach; ?>
      </select>

      <select name="sort" class="form-control mr-2" onchange="this.form.submit()">
        <option value="" <?= empty($sort) ? 'selected' : '' ?>>Sort by</option>
        <option value="latest" <?= strtolower($sort) === 'latest' ? 'selected' : '' ?>>Latest</option>
        <option value="oldest" <?= strtolower($sort) === 'oldest' ? 'selected' : '' ?>>Oldest</option>
        <option value="az" <?= strtolower($sort) === 'az' ? 'selected' : '' ?>>A-Z</option>
        <option value="za" <?= strtolower($sort) === 'za' ? 'selected' : '' ?>>Z-A</option>
      </select>
    </form>

    <form action="listProduct.php" method="GET" class="d-flex">
      <input type="text" name="search" class="form-control mr-2" placeholder="Search product..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn btn-outline-info">Search</button>
    </form>
  </div>
</div>

<div style="padding-left: 50px; padding-right: 50px; margin-top: 50px;">
  <table class="table">
    <thead class="table-dark">
      <tr>
        <th scope="col">Id</th>
        <th scope="col">Product Name</th>
        <th scope="col">Size</th>
        <th scope="col">Price</th>
        <th scope="col">Color</th>
        <th scope="col">Image</th>
        <th scope="col">Edit</th>
        <th scope="col">Delete</th>
      </tr>
    </thead>
    <tbody class="table-group-divider">
      <?php if (!is_array($products) || empty($products)): ?>
        <tr>
          <td colspan="8" class="text-center text-muted">No products added yet.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($products as $product): ?>
          <tr>
            <th scope="row"><?= $product['id'] ?></th>
            <td><?= $product['name'] ?></td>
            <td><?= $product['size'] ?></td>
            <td><?= $product['price'] ?></td>
            <td><?= $product['color'] ?></td>
            <td>
              <?php foreach ($product['images'] as $imgPath) : ?>
                <img src="../uploads/product-images/<?= $imgPath ?>" alt="Product Image" width='50' height="50" style="margin: 2px">
              <?php endforeach; ?>
            </td>
            <td><a href="./editProduct.php?id=<?= $product['id'] ?>"> Edit </a></td>
            <td><a href="./deleteProduct.php?id=<?= $product['id'] ?>" onclick="return confirm('Are you sure you want to delete the product?')"> Delete </a></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<nav aria-label="Page navigation">
  <ul class="pagination justify-content-center">
    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
      <a class="page-link" href="?page=<?= max(1, $page - 1) ?>&perPage=<?= $perPage ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>">Previous</a>
    </li>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <li class="page-item <?= $i === $page ? 'active' : '' ?>">
        <a class="page-link" href="?page=<?= $i ?>&perPage=<?= $perPage ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>

    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
      <a class="page-link" href="?page=<?= min($totalPages, $page + 1) ?>&perPage=<?= $perPage ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>">Next</a>
    </li>
  </ul>
</nav>
<?php
include '../includes/footer.php';
?>