<?php
include_once dirname(__DIR__) . '/classes/DBConnection.php';
class Products
{
    private $conn;

    public function __construct()
    {
        $db = new DatabaseConnect();
        $this->conn = $db->connect();
    }

    public function listFilteredProducts($sort = '', $search = '', $page = 1, $perPage = 10)
    {
        $userId = $_SESSION['user']['id'];
        $offset = ($page - 1) * $perPage;
        //main query ...will be using this at the end while concatenating with different queries
        $query = "SELECT * FROM products WHERE user_id = ?";
        $types = 'i';
        $values = [$userId];

        // Search 
        if (!empty($search)) {
            $query .= " AND (name LIKE ? OR color LIKE ? OR size LIKE ? OR CAST(price AS CHAR) LIKE ?)";
            $types .= 'ssss';
            $searchTerm = "%$search%";
            array_push($values, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        }
        // Sort 
        switch ($sort) {
            case 'az':
                $query .= " ORDER BY name ASC";
                break;
            case 'za':
                $query .= " ORDER BY name DESC";
                break;
            case 'latest':
                $query .= " ORDER BY created_at DESC";
                break;
            case 'oldest':
                $query .= " ORDER BY created_at ASC";
                break;
            default:
                $query .= " ORDER BY id DESC";
        }
        // Pagination
        $query .= " LIMIT ? OFFSET ?";
        $types .= 'ii';
        array_push($values, $perPage, $offset);

        // Preparing the statement and binding the parameters finally
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $imgStmt = $this->conn->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
            $imgStmt->bind_param('s', $row['id']);
            $imgStmt->execute();
            $imgRes = $imgStmt->get_result();
            $images = [];
            while ($img = $imgRes->fetch_assoc()) {
                $images[] = $img['image_path'];
            }
            $row['images'] = $images;
            $products[] = $row;
        }

        return $products;
    }


    public function addProduct($name, $price, $size, $color, $img)
    {
        $id = uniqid();
        $userId = $_SESSION['user']['id'];
        // Insert product into table product
        $sql = $this->conn->prepare("INSERT INTO `products` (`id`, `user_id`, `name`, `size`, `price`, `color`) VALUES (?, ?, ?, ?, ?, ?)");
        $sql->bind_param('sissds', $id, $userId, $name, $size, $price, $color);
        $sql->execute();
        $sql->close();

        $addImg = $this->addOrUpdateImage($img, $id);
        header('Location:../views/listProduct.php');
        exit();
    }

    public function updateProduct($id, $dataToUpdate, $img, $deleteImages = [])
    {
        //$userId = $_SESSION['user']['id'];
        //update the product
        $sql = $this->conn->prepare("UPDATE `products` SET `name` = ? , `size` = ? , `price` = ?, `color` = ? WHERE `id` = ?");
        $sql->bind_param('ssdss', $dataToUpdate['name'], $dataToUpdate['size'], $dataToUpdate['price'], $dataToUpdate['color'], $id);
        $sql->execute();
        $sql->close();

        // If want to delete the existing images and add new images 
        //Removing files which are present physically in the system 
        if (!empty($deleteImages)) {
        foreach ($deleteImages as $imageId) {
                $stmt = $this->conn->prepare("SELECT * FROM product_images WHERE id = ?");
                $stmt->bind_param('i', $imageId);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();

                $uploadDir = dirname(__DIR__) . '/uploads/product-images/';
                $imgDir = $uploadDir . $result['image_path'];
                
                if (file_exists($imgDir)) {
                    unlink($imgDir);
                }
                $stmt->close();
                // Delete from the database also
                $stmt = $this->conn->prepare("DELETE FROM product_images WHERE id = ?");
                $stmt->bind_param('i', $imageId);
                $stmt->execute();
                $stmt->close();
            }
        }
        //update/adding a new image while editing
        if (!empty($img['name'][0])) {
            $this->addOrUpdateImage($img, $id);
        }
        return "Product updated successfully.";
    }

    public function addOrUpdateImage($img, $id)
    {
        $uploadDir = dirname(__DIR__) . '/uploads/product-images/';
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        foreach ($img['tmp_name'] as $index => $tmpName) {

            $fileName = basename($img['name'][$index]);
            $imgExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($imgExt, $allowedExtensions)) {
                return "Only jpg, jpeg and png images are allowed.";
            }

            $imgName =  uniqid() . '.' . $imgExt;
            $filePath = $uploadDir . $imgName;
            if (!move_uploaded_file($tmpName, $filePath)) {
                return "Error uploading image.";
            }
            $insertImg = $this->conn->prepare("INSERT INTO `product_images` (`product_id`, `image_path`) VALUES (?, ?)");
            $insertImg->bind_param('ss', $id, $imgName);
            $insertImg->execute();
            $insertImg->close();
        }

        return "Image uploaded successfully.";
    }

    public function countFilteredProducts($search = '')
    {
        if (!isset($_SESSION['user']['id'])) {
            return "SEssion is not set."; // no user logged in
        }
        $userId = $_SESSION['user']['id'];

        $query = "SELECT COUNT(*) as total FROM products WHERE user_id = ?";
        $types = 'i';
        $values = [$userId];

        if (!empty($search)) {
            $query .= " AND (name LIKE ? OR color LIKE ? OR size LIKE ? OR CAST(price AS CHAR) LIKE ?)";
            $types .= 'ssss';
            $searchTerm = "%$search%";
            array_push($values, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $result = $stmt->get_result();

        $count = 0;
        if ($row = $result->fetch_assoc()) {
            $count = (int)$row['total'];
        }
        return $count;
    }


    public function deleteProduct($id)
    {
        $sql = $this->conn->prepare("DELETE FROM products WHERE `id` = ? ");
        $sql->bind_param('s', $id);
        if ($sql->execute()) {
            header('Location:../views/listProduct.php');
            return "Product deleted successfully.";
            exit();
        }
        return "Can't delete product.";
    }

    // public function search($keyword)
    // {
    //     $search = '%' . $keyword . '%';
    //     $userId = $_SESSION['user']['user_id'];
    //     $sql = $this->conn->prepare("SELECT * FROM products
    //                                             WHERE `user_id` = ? AND (
    //                                             `name` LIKE ? 
    //                                              OR `color` LIKE ?
    //                                              OR `size` LIKE ?
    //                                              OR CAST(`price` AS CHAR) LIKE ?)
    //                                     ");
    //     $sql->bind_param('issss', $userId, $search, $search, $search, $search);
    //     $sql->execute();
    //     $result = $sql->get_result();
    //     $product = [];
    //     while ($row = $result->fetch_assoc()) {
    //         $imgStmt = $this->conn->prepare("SELECT * FROM product_images WHERE product_id = ?");
    //         $imgStmt->bind_param('s', $row['id']);
    //         $imgStmt->execute();
    //         $output = $imgStmt->get_result();
    //         $images = [];
    //         while ($imgRow = $output->fetch_assoc()) {
    //             $images[] = $imgRow['image_path'];
    //         }
    //         $row['images'] = $images;
    //         $product[] = $row;
    //     }
    //     return $product;
    // }

    // public function pagination($page, $perPage)
    // {
    //     $offset = ($page - 1) * $perPage;
    //     $sql = $this->conn->prepare("SELECT * FROM `products` LIMIT ? OFFSET ?");
    //     $sql->bind_param('ii', $perPage, $offset);
    //     $sql->execute();
    //     $result = $sql->get_result();
    //     $product = [];
    //     while ($row = $result->fetch_assoc()) {
    //         $product[] = $row;
    //     }
    //     return $product;
    // }

    // public function sortBy($value)
    // {
    //     switch ($value) {
    //         case 'A-Z':
    //             $orderBy = "ORDER BY `name` ASC";
    //             break;
    //         case 'Z-A':
    //             $orderBy = "ORDER BY `name` DESC";
    //             break;
    //         case 'Latest':
    //             $orderBy = "ORDER BY `created_at` DESC";
    //             break;
    //         case 'Oldest':
    //             $orderBy = "ORDER BY `created_at` ASC";
    //             break;
    //         default:
    //             $orderBy = "ORDER BY `id` ASC";
    //     }

    //     $query = "SELECT * FROM `products` " . $orderBy;
    //     $sql = $this->conn->prepare($query);
    //     $sql->bind_param('s', $value);
    //     $sql->execute();
    //     $result = $sql->get_result();
    //     $products = [];
    //     while ($row = $result->fetch_assoc()) {
    //         $products[] = $row;
    //     }
    //     return $products;
    // }



    //public function listProduct()
    // {
    //     if (!isset($_SESSION['user']['user_id'])) {
    //         return "Session not set.";
    //     }
    //     $result = $this->conn->prepare("SELECT * FROM products WHERE user_id = ?");
    //     $result->bind_param('i', $_SESSION['user']['user_id']);
    //     $result->execute();
    //     $res = $result->get_result();
    //     $result->close();
    //     $products = [];
    //     while ($row = $res->fetch_assoc()) {
    //         $imgStmt = $this->conn->prepare("SELECT * FROM product_images WHERE product_id = ? ");
    //         $imgStmt->bind_param('s', $row['id']);
    //         $imgStmt->execute();
    //         $imageResult = $imgStmt->get_result();
    //         $imgStmt->close();
    //         $images = [];
    //         while ($i = $imageResult->fetch_assoc()) {
    //             $images[] = $i['image_path'];
    //         }
    //         $row['images'] = $images;
    //         $products[] = $row;
    //     }
    //     return $products;
    // }
}
