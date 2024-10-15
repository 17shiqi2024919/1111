<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        echo json_encode(["success" => false, "message" => "用户名和密码都是必填的"]);
        exit;
    }

    $sql = "SELECT id, username, password, avatar FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        echo json_encode(["success" => false, "message" => "准备语句失败: " . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if ($password === $row['password']) {
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            session_regenerate_id(true);
            echo json_encode([
                "success" => true,
                "username" => $row['username'],
                "avatarUrl" => "avatars/" . ($row['avatar'] ? $row['avatar'] : 'default.jpg')
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "密码错误"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "用户名不存在"]);
    }

    $stmt->close();
}

$conn->close();
?>
