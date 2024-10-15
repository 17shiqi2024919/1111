<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(["success" => false, "message" => "未登录"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['currentPassword']) || !isset($input['email']) || !isset($input['verificationCode']) || !isset($input['newPassword'])) {
    echo json_encode(["success" => false, "message" => "缺少必要参数"]);
    exit;
}

$user_id = $_SESSION['id'];
$current_password = $input['currentPassword'];
$email = $input['email'];
$verification_code = $input['verificationCode'];
$new_password = $input['newPassword'];

// 验证当前密码
$sql = "SELECT password FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $current_password !== $user['password']) {
    echo json_encode(["success" => false, "message" => "当前密码错误"]);
    exit;
}

// 验证邮箱
$email_sql = "SELECT email FROM users WHERE id = ? AND email = ?";
$email_stmt = $conn->prepare($email_sql);
$email_stmt->bind_param("is", $user_id, $email);
$email_stmt->execute();
$email_result = $email_stmt->get_result();

if ($email_result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "邮箱地址不匹配"]);
    exit;
}

// 验证验证码
if (!isset($_SESSION['verification_code']) || $_SESSION['verification_code'] != $verification_code) {
    echo json_encode(["success" => false, "message" => "验证码错误"]);
    exit;
}

// 检查验证码是否过期（10分钟内有效）
if (time() - $_SESSION['verification_code_timestamp'] > 600) {
    echo json_encode(["success" => false, "message" => "验证码已过期"]);
    exit;
}

// 更新密码
$update_sql = "UPDATE users SET password = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("si", $new_password, $user_id);

if ($update_stmt->execute()) {
    // 清除验证码
    unset($_SESSION['verification_code']);
    unset($_SESSION['verification_code_timestamp']);
    
    echo json_encode(["success" => true, "message" => "密码修改成功"]);
} else {
    echo json_encode(["success" => false, "message" => "密码修改失败，请稍后再试"]);
}

$update_stmt->close();
$conn->close();
?>
