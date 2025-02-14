<?php 
include 'includes/header.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // 获取当前用户信息
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $user = $stmt->fetch();
    
    if (!password_verify($current_password, $user['password'])) {
        $message = '当前密码不正确';
    } else if ($new_password !== $confirm_password) {
        $message = '两次输入的新密码不一致';
    } else if (strlen($new_password) < 6) {
        $message = '新密码长度不能小于6位';
    } else {
        // 更新密码
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
        
        if ($stmt->execute([$hash, $_SESSION['admin_id']])) {
            $success = true;
            $message = '密码修改成功';
        } else {
            $message = '密码修改失败，请重试';
        }
    }
}
?>

<h1>修改密码</h1>

<?php if ($message): ?>
<div class="alert <?= $success ? 'alert-success' : 'alert-error' ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<div class="profile-form-container">
    <form method="post" class="admin-form">
        <div class="form-group">
            <label>当前密码</label>
            <input type="password" name="current_password" required>
        </div>

        <div class="form-group">
            <label>新密码</label>
            <input type="password" name="new_password" required>
            <small>密码长度不能小于6位</small>
        </div>

        <div class="form-group">
            <label>确认新密码</label>
            <input type="password" name="confirm_password" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">修改密码</button>
            <a href="dashboard.php" class="btn-secondary">返回控制台</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?> 