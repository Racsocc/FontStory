<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

$username = 'fontstory';
$password = 'fontstory'; // 统一使用 fontstory 作为密码
$email = 'admin@' . $username;  // 使用用户名生成一个唯一的邮箱地址

// 对密码进行哈希处理
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $hash, $email])) {
        echo "管理员账号创建成功！\n";
        echo "用户名: $username\n";
        echo "密码: $password\n";
        echo "邮箱: $email\n";
    }
} catch (PDOException $e) {
    echo "创建失败: " . $e->getMessage();
} 