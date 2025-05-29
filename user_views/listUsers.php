<?php

include_once dirname(__DIR__) . '/classes/DBConnection.php';
include_once dirname(__DIR__) . '/classes/users.php';
include '../config/config.php';
include '../includes/head.php';
include '../includes/navbar.php';

if (!isset($_SESSION['user'])) {
    header('Location:./login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $userId = $_POST['user_id'];
    $currentStatus = $_POST['current_status'];
    $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';

    $conn = (new DatabaseConnect())->connect();
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $newStatus, $userId);
    $stmt->execute();
    $stmt->close();
}

$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? '';
$page = $_GET['page'] ?? 1;
// $perPage = ($_GET['perPage'] && $_GET['perPage'] > 0) ?? 10;
$perPage = 10;
$obj = new Users();
$users = $obj->listUsers($search, $sort, $perPage, $page);

$totalRows = $obj->getAllUsers($search);
$totalPages = ceil((int)$totalRows / (int)$perPage);
$dir = dirname(__DIR__) . '/uploads/user-profile-img/';
?>

<h2 class="text-center" style="margin-top: 50px;">Users List</h2>

<div class="d-flex justify-content-between mb-3" style="padding-left: 50px; padding-right: 50px;">
    <div class="d-flex">
        <form action="" method="get" class="d-flex mr-2">
            <select name="perPage" id="page" class="form-control mr-2" onchange="this.form.submit()">
                <option value="">Products per page</option>
                <?php foreach ([10, 20, 30, 40, 50] as $num): ?>
                    <option value="<?= $num ?>" <?= $perPage === $num ? 'selected' : '' ?>><?= $num ?></option>
                <?php endforeach; ?>
            </select>

            <select name="sort" class="form-control mr-2" onchange="this.form.submit()">
                <option value="" <?= empty($sort) ? 'selected' : '' ?>>Sort by</option>
                <option value="az" <?= strtolower($sort) === 'az' ? 'selected' : '' ?>>A-Z</option>
                <option value="za" <?= strtolower($sort) === 'za' ? 'selected' : '' ?>>Z-A</option>
            </select>
        </form>

        <form action="listUsers.php" method="GET" class="d-flex">
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
                <th scope="col">User Name</th>
                <th scope="col">Email</th>
                <th scope="col">Country</th>
                <th scope="col">Image</th>
                <th scope="col">Status</th>
                <th scope="col">Edit</th>
                <th scope="col">Delete</th>
            </tr>
        </thead>
        <tbody class="table-group-divider">
            <?php if (!is_array($users) || empty($users)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted">No users found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <th scope="row"><?= $user['id'] ?></th>
                        <td><?= $user['name'] ?></td>
                        <td><?= $user['email'] ?></td>
                        <td><?= $user['country'] ?></td>
                        <td>
                            <?php if (!empty($user['profile_img'])): ?>
                                <img src="../uploads/user-profile-img/<?= $user['profile_img'] ?>" alt="User Profile Image" width="50" height="50">
                            <?php else: ?>
                                <span class="text-muted">No image</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" action="">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <input type="hidden" name="current_status" value="<?= $user['status'] ?>">
                                
                                <button type="submit" name="toggle_status" class="btn btn-sm <?= $user['status'] === 'active' ? 'btn-primary' : 'btn-secondary' ?>">
                                    <?= $user['status'] ?>
                                </button>
                            </form>
                        </td>
                        <td><a href="./editUser.php?id=<?= $user['id'] ?>"> Edit </a></td>
                        <td><a href="./deleteUser.php?id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete the user?')"> Delete </a></td>
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