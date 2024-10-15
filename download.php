<?php
session_start();

// 检查用户是否登录
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "请先登录";
    exit;
}

// 指定下载文件
$file = 'game.rar';
$filepath = __DIR__ . "/downloads/" . $file;  // 使用绝对路径

// 检查文件是否存在
if (!file_exists($filepath)) {
    echo "文件不存在: " . $filepath;  // 保留这行以便调试
    exit;
}

// 设置适当的头部信息
header('Content-Description: File Transfer');
header('Content-Type: application/x-rar-compressed');
header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));

// 清空输出缓冲
ob_clean();
flush();

// 输出文件内容
readfile($filepath);
exit;
?>
