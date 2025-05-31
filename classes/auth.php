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

    public function register($name, $email,  $password, $country = NULL, $profile_img = NULL)
    {
        $check = $this->emailExists($email);
        if ($check) {
            return "Email already exists. Please login.";
            exit();
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        if ($profile_img) {
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $imgExt = strtolower(pathinfo($profile_img['name'], PATHINFO_EXTENSION));
            if (!in_array($imgExt, $allowedExtensions)) {
                return "Only JPG or JPEG image is allowed.";
            }
            $uploadDir = dirname(__DIR__) . '/uploads/user-profile-img/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // create folder if not exists
            }
            $imgName = time() . '-' . basename($profile_img['name']);
            $imgPath = 'uploads/user-profile-img/' . $imgName;
            $imgFullPath = $uploadDir . $imgName;
            if (!move_uploaded_file($profile_img['tmp_name'], $imgFullPath)) {
                return "Image upload failed.";
            }
            $sql = $this->conn->prepare("INSERT INTO `users` (`name`, `email`, `country`, `profile_img` , `password`) VALUES (?, ?, ?, ?, ?)");
            $sql->bind_param('sssss', $name, $email, $country, $imgName, $hashedPassword);
        } else {
            $sql = $this->conn->prepare("INSERT INTO `users` (`name`, `email`, `country`, `password`) VALUES (?, ?, ?, ?)");
            $sql->bind_param('ssss', $name, $email, $country, $hashedPassword);
        }
        $sql->execute();
        $sql->close();
        header('Location:../views/login.php?msg=' . urlencode("Registration successful. Please log in."));
        exit;
    }

    public function login($email, $password)
    {
        $check = $this->emailExists($email);
        if (!$check) {
            return "Email is not registered. Kindly signup.";
            exit();
        }
        $user = $this->validateUser($email, $password);
        if ($user) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email']
            ];
            header('Location:../views/adminOrProduct.php');
            exit();
        }
        return "Invalid credentials.";
        exit();
    }

    private function emailExists($email)
    {
        $sql = $this->conn->prepare("SELECT * FROM `users` WHERE `email` = ?");
        $sql->bind_param('s', $email);
        $sql->execute();
        $sql->store_result();

        return $sql->num_rows > 0;
    }

    private function validateUser($email, $password)
    {
        $sql = $this->conn->prepare("SELECT id, email, password FROM `users` WHERE `email` = ?");
        $sql->bind_param('s', $email);
        $sql->execute();

        $sql->store_result();
        if ($sql->num_rows > 0) {
            $sql->bind_result($id, $email, $hashedPassword);
            $sql->fetch();
            if (password_verify($password, $hashedPassword)) {
                return [
                    'id' => $id,
                    'email' => $email
                ];
            }
        }
        return false;
    }
}
