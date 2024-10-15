<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(["success" => false, "message" => "未登录"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id'];

    if (!isset($_FILES['avatar'])) {
        echo json_encode(["success" => false, "message" => "没有上传文件"]);
        exit;
    }

    $file = $_FILES['avatar'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(["success" => false, "message" => "只允许上传 JPEG, PNG, GIF, WebP 或 BMP 格式的图片"]);
        exit;
    }

    if ($file['size'] > $max_size) {
        echo json_encode(["success" => false, "message" => "文件大小不能超过 5MB"]);
        exit;
    }

    $upload_dir = 'avatars/';
    $filename = uniqid() . '_' . $file['name'];
    $upload_path = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // 更新数据库中的头像信息
        $update_sql = "UPDATE users SET avatar = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $filename, $user_id);

        if ($update_stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "头像上传成功",
                "newAvatarUrl" => $upload_path
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "头像信息更新失败"]);
        }

        $update_stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "头像上传失败"]);
    }
}

$conn->close();
?>
