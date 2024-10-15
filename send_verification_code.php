<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(["success" => false, "message" => "未登录"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['email'])) {
    echo json_encode(["success" => false, "message" => "缺少邮箱地址"]);
    exit;
}

$email = $input['email'];
$user_id = $_SESSION['id'];

// 验证邮箱是否匹配
$sql = "SELECT email FROM users WHERE id = ? AND email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "邮箱地址不匹配"]);
    exit;
}

// 生成验证码
$verificationCode = rand(100000, 999999);

// 存储验证码
$_SESSION['verification_code'] = $verificationCode;
$_SESSION['verification_code_timestamp'] = time();

// 发送验证码邮件
$to = $email;
$subject = "密码修改验证码";
$message = "您的验证码是: $verificationCode\n\n此验证码将在10分钟后过期。";
$headers = "From: your_email@example.com\r\n";
$headers .= "Reply-To: your_email@example.com\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

if (mail($to, $subject, $message, $headers)) {
    echo json_encode(["success" => true, "message" => "验证码已发送到您的邮箱"]);
} else {
    echo json_encode(["success" => false, "message" => "验证码发送失败，请稍后再试"]);
}

$stmt->close();
$conn->close();
?>
