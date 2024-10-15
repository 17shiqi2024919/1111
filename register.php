<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $avatar = $_POST['avatar'];

    if (empty($username) || empty($password) || empty($email) || empty($avatar)) {
        echo "所有字段都是必填的";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@.+\./', $email)) {
        echo "无效的邮箱地址";
        exit;
    }

    if (strlen($password) < 8) {
        echo "密码长度必须至少为8个字符";
        exit;
    }

    // 验证头像选择
    $allowed_avatar = 'touxiang.jpg';
    if ($avatar !== $allowed_avatar) {
        echo "无效的头像选择";
        exit;
    }

    $check_sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        echo "用户名或邮箱已存在";
        exit;
    }
    $check_stmt->close();

    $sql = "INSERT INTO users (username, password, email, avatar) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $password, $email, $avatar);

    if ($stmt->execute()) {
        echo "注册成功";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
