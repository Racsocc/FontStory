<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    
    // 检查邮箱是否存在
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        // 生成重置令牌
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // 保存重置令牌
        $stmt = $pdo->prepare("UPDATE admin_users SET 
            reset_token = ?, 
            reset_expires = ? 
            WHERE id = ?");
        
        if ($stmt->execute([$token, $expires, $user['id']])) {
            // 发送重置邮件
            $reset_link = SITE_URL . "/admin/new-password.php?token=" . $token;
            $to = $user['email'];
            $subject = "FontStory - 重置密码";
            $message = "请点击以下链接重置您的密码：\n\n$reset_link\n\n链接有效期为1小时。";
            
            if (mail($to, $subject, $message)) {
                $success = true;
                $message = '重置链接已发送到您的邮箱，请查收';
            } else {
                $message = '邮件发送失败，请重试';
            }
        }
    } else {
        $message = '该邮箱未注册';
    }
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>找回密码 - FontStory</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="login-container">
        <h1>找回密码</h1>
        
        <?php if ($message): ?>
        <div class="alert <?= $success ? 'alert-success' : 'alert-error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <div class="form-group">
                <label>邮箱地址</label>
                <input type="email" name="email" required>
            </div>
            <button type="submit">发送重置链接</button>
            <a href="login.php" class="form-link">返回登录</a>
        </form>
    </div>
</body>
</html> 