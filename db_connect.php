<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 使用环境变量或配置文件来存储数据库凭证
$servername = "localhost"; // 或者你的数据库服务器地址
$username = "1059892381"; // 你的数据库用户名
$password = "5201314"; // 你的数据库密码
$dbname = "pubg"; // 你的数据库名称

// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// 设置字符集
$conn->set_charset("utf8mb4");

// 移除数据库连接成功的消息
// if ($conn->ping()) {
//     echo "数据库连接成功";
// } else {
//     echo "数据库连接失败: " . $conn->error;
// }
?>
