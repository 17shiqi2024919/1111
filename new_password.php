<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['token'])) {
    $token = $_GET['token'];
    $sql = "SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $error = "无效或已过期的重置链接";
    }
    $stmt->close();
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = "两次输入的密码不匹配";
    } elseif (strlen($password) < 8) {
        $error = "密码长度必须至少为8个字符";
    } else {
        $sql = "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ? AND reset_expires > NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $password, $token);
        if ($stmt->execute()) {
            $success = "密码已成功重置,请使用新密码登录";
        } else {
            $error = "密码重置失败,请稍后再试";
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
    <title>设置新密码 - 绝地求生</title>
    <style>
        /* 复制 profile.php 中的样式 */
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>设置新密码</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
                <input type="password" name="password" placeholder="新密码" required>
                <input type="password" name="confirm_password" placeholder="确认新密码" required>
                <button type="submit">重置密码</button>
            </form>
        <?php endif; ?>
        <p><a href="index.html">返回主页</a></p>
    </div>
</body>
</html>
