<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/logger.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                Logger::log("管理员登录成功: {$username}", 'SECURITY');
                header('Location: dashboard.php');
                exit;
            }
        }
        Logger::log("登录失败: {$username}", 'WARNING', [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ]);
        $error = '用户名或密码错误';
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $error = '系统错误，请稍后重试';
    }
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>登录 - FontStory</title>
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/admin/css/admin.css">
</head>
<body>
    <div class="login-container">
        <h1>FontStory 管理后台</h1>
        
        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <div class="form-group">
                <label>用户名</label>
                <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>密码</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit">登录</button>
            <a href="reset-password.php" class="form-link">忘记密码？</a>
        </form>
    </div>
</body>
</html> 