<?php 
include 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $display_name = $_POST['display_name'];
    $font_family = $_POST['font_family'];
    $download_url = $_POST['download_url'];
    
    $stmt = $pdo->prepare("UPDATE fonts SET 
        name = ?, 
        display_name = ?, 
        font_family = ?, 
        download_url = ? 
        WHERE id = ?");
    
    if ($stmt->execute([$name, $display_name, $font_family, $download_url, $id])) {
        header('Location: fonts.php');
        exit;
    }
}

// 获取字体信息
$stmt = $pdo->prepare("SELECT * FROM fonts WHERE id = ?");
$stmt->execute([$id]);
$font = $stmt->fetch();

if (!$font) {
    die('字体不存在');
}
?>

<h1>编辑字体</h1>

<div class="edit-form-container">
    <form method="post" class="admin-form">
        <div class="form-group">
            <label>字体名称（英文）</label>
            <input type="text" name="name" value="<?= htmlspecialchars($font['name']) ?>" required>
        </div>

        <div class="form-group">
            <label>显示名称（中文）</label>
            <input type="text" name="display_name" value="<?= htmlspecialchars($font['display_name']) ?>" required>
        </div>

        <div class="form-group">
            <label>Font Family</label>
            <input type="text" name="font_family" value="<?= htmlspecialchars($font['font_family']) ?>" required>
        </div>

        <div class="form-group">
            <label>下载链接</label>
            <input type="url" name="download_url" value="<?= htmlspecialchars($font['download_url']) ?>" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">保存修改</button>
            <a href="fonts.php" class="btn-secondary">返回列表</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?> 