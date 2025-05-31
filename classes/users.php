<?php
include_once dirname(__DIR__) . '/classes/DBConnection.php';

class Users
{

    private $conn;

    public function __construct()
    {
        $db = new DatabaseConnect();
        $this->conn = $db->connect();
    }

    public function listUsers($search = '', $sort = '', $perPage = 10, $page = 1)
    {
        $perPage = (int)$perPage;
        $page = (int)$page;
        $offset = ($page - 1) * $perPage;
        $search = $this->conn->real_escape_string($search);

        $sql = "SELECT * FROM users WHERE deleted_at IS NULL";
        if (!empty($search)) {
            $sql .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR country LIKE '%$search%')";
        }

        switch ($sort) {
            case 'az':
                $sql .= " ORDER BY name ASC";
                break;
            case 'za':
                $sql .= " ORDER BY name DESC";
                break;
            default:
                $sql .= " ORDER BY id ASC";
        }

        $sql .= " LIMIT $perPage OFFSET $offset";

        $result = $this->conn->query($sql);
        $users = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        return $users;
    }
    public function getAllUsers($search = '')
    {
        $search = $this->conn->real_escape_string($search);
        $condition = '';
        $keyword = '%' . $search . '%';
        if (!empty($search)) {
            $condition = " AND name LIKE '$keyword' OR email LIKE '$keyword'";
        }

        $sql = "SELECT COUNT(*) as total FROM users WHERE deleted_at IS NULL $condition";
        $result = $this->conn->query($sql);

        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['total'];
        }
        return "Can't get total users.";
    }

    public function deleteUser($id)
    {
        $sqlUser = $this->conn->prepare("UPDATE users SET deleted_at = NOW(), status = ? WHERE id = ?");
        $status = 'inactive';
        $sqlUser->bind_param('si', $status, $id);

        $sqlProduct = $this->conn->prepare("UPDATE products SET deleted_at = NOW() WHERE user_id = ?");
        $sqlProduct ->bind_param('i', $id);

        $sqlProduct_images = $this->conn->prepare("
                                                    UPDATE product_images PI 
                                                    JOIN products P ON PI.product_id = P.id
                                                    JOIN users U ON P.user_id = U.id
                                                    SET PI.deleted_at = NOW()
                                                    WHERE U.id = ? 
                                                ");
        $sqlProduct_images->bind_param('i', $id);   

        $this->conn->begin_transaction(); 
        try {
            $sqlUser->execute();
            $sqlProduct->execute();
            $sqlProduct_images->execute();
            
            $this->conn->commit();
            return "User has been deleted successfully.";
        }
        catch (Exception $error) {
            $this->conn->rollback();
            return "Error deleting use: " . $error->getMessage();
        }
    }

    public function editUser($id, $name, $country = NULL, $profileImg = NULL)
    {
        $userId = (int)$id;
        $name = $this->conn->real_escape_string($name);
        $country = $country ? $this->conn->real_escape_string($country) : NULL;

        if ($profileImg && $profileImg['tmp_name']) {
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $imgExt = strtolower(pathinfo($profileImg['name'], PATHINFO_EXTENSION));

            if (!in_array($imgExt, $allowedExtensions)) {
                return "Only jpg, jpeg and png files are allowed.";
            }

            $uploadDir = dirname(__DIR__) . '/uploads/user-profile-img/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $imgName = time() . '-' . basename($profileImg['name']);
            $imgFullPath = $uploadDir . $imgName;

            if (!move_uploaded_file($profileImg['tmp_name'], $imgFullPath)) {
                return "Can't upload file.";
            }

            $imgName = $this->conn->real_escape_string($imgName);
            $stmt = $this->conn->prepare("UPDATE users SET name = ? , country = ? , profile_img = ? WHERE id = ?");
            $stmt->bind_param('sssi', $name, $country, $imgName, $userId);
        } else {
            $stmt = $this->conn->prepare("UPDATE users SET name = ? , country = ? WHERE id = ?");
            $stmt->bind_param('ssi', $name, $country, $userId);
        }
        $result = $stmt->execute();
        $stmt->close();

        return $result ? true : false;
    }

    public function userById($id)
    {
        $userId = (int)$id;
        $sql = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $sql->bind_param('i', $userId);
        $sql->execute();
        return $sql->get_result()->fetch_assoc();
    }
}
