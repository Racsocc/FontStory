<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

$message = '';
$success = false;
$token = $_GET['token'] ?? '';

// 验证令牌
$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die('重置链接无效或已过期');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $message = '两次输入的密码不一致';
    } else if (strlen($password) < 6) {
        $message = '密码长度不能小于6位';
    } else {
        // 更新密码
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admin_users SET 
            password = ?, 
            reset_token = NULL, 
            reset_expires = NULL 
            WHERE id = ?");
        
        if ($stmt->execute([$hash, $user['id']])) {
            $success = true;
            $message = '密码重置成功，请使用新密码登录';
        } else {
            $message = '密码重置失败，请重试';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>重置密码 - FontStory</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="login-container">
        <h1>重置密码</h1>
        
        <?php if ($message): ?>
        <div class="alert <?= $success ? 'alert-success' : 'alert-error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <p class="text-center">
            <a href="login.php" class="btn-primary">返回登录</a>
        </p>
        <?php else: ?>
        <form method="post" class="login-form">
            <div class="form-group">
                <label>新密码</label>
                <input type="password" name="password" required>
                <small>密码长度不能小于6位</small>
            </div>
            <div class="form-group">
                <label>确认密码</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit">重置密码</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html> 