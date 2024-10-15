<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "无效的邮箱地址";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $token = bin2hex(random_bytes(50));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $update_sql = "UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssi", $token, $expires, $user['id']);
            $update_stmt->execute();
            
            // 在实际应用中,这里应该发送一封包含重置链接的邮件
            $reset_link = "http://yourdomain.com/new_password.php?token=$token";
            $success = "密码重置链接已发送到您的邮箱 (模拟): $reset_link";
        } else {
            $error = "未找到与该邮箱关联的账户";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>重置密码 - 绝地求生</title>
    <style>
        /* 复制 profile.php 中的样式 */
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>重置密码</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="email" name="email" placeholder="输入您的邮箱地址" required>
            <button type="submit">发送重置链接</button>
        </form>
        <p><a href="index.html">返回主页</a></p>
    </div>
</body>
</html>
