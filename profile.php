<?php
session_start();
include 'db_connect.php';

// 检查用户是否登录
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.html");
    exit;
}

$user_id = $_SESSION['id'];

// 获取用户信息
$sql = "SELECT username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// 处理表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "无效的邮箱地址";
    } else {
        $update_sql = "UPDATE users SET email = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $new_email, $user_id);
        if ($update_stmt->execute()) {
            $success = "个人资料更新成功";
            $user['email'] = $new_email;
        } else {
            $error = "更新失败,请稍后再试";
        }
        $update_stmt->close();
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>个人资料 - 绝地求生</title>
    <style>
        body {
            font-family: "Microsoft YaHei", Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #000;
            color: #fff;
        }
        .profile-container {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 30px;
            border-radius: 15px;
            width: 300px;
            margin: 100px auto;
            text-align: center;
        }
        .profile-container h2 {
            color: #f39c12;
            margin-bottom: 20px;
        }
        .profile-container form {
            display: flex;
            flex-direction: column;
        }
        .profile-container input {
            margin: 10px 0;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .profile-container button {
            margin-top: 20px;
            padding: 10px;
            background-color: #f39c12;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .error, .success {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
        }
        .error {
            background-color: rgba(231, 76, 60, 0.7);
        }
        .success {
            background-color: rgba(46, 204, 113, 0.7);
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>个人资料</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            <button type="submit">更新资料</button>
        </form>
        <p><a href="index.html">返回主页</a></p>
    </div>
</body>
</html>
