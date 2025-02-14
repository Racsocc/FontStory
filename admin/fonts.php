<?php 
include 'includes/header.php';
require_once '../includes/logger.php';

// 处理删除请求
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    
    // 获取字体信息
    $stmt = $pdo->prepare("SELECT * FROM fonts WHERE id = ?");
    $stmt->execute([$id]);
    $font = $stmt->fetch();
    
    if ($font) {
        // 删除字体文件和目录
        $fontDir = "../fonts/" . $font['folder_name'];
        if (is_dir($fontDir)) {
            array_map('unlink', glob("$fontDir/*.*"));
            rmdir($fontDir);
        }
        
        // 从数据库删除
        $stmt = $pdo->prepare("DELETE FROM fonts WHERE id = ?");
        if ($stmt->execute([$id])) {
            Logger::log(
                "删除字体：{$font['display_name']}",
                'FONT',
                [
                    'font_id' => $font['id'],
                    'font_name' => $font['name']
                ]
            );
            $message = '字体已删除';
            $success = true;
        }
    }
}

// 处理状态切换
if (isset($_POST['toggle_status'])) {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("SELECT * FROM fonts WHERE id = ?");
    $stmt->execute([$id]);
    $font = $stmt->fetch();

    $stmt = $pdo->prepare("UPDATE fonts SET status = 1 - status WHERE id = ?");
    if ($stmt->execute([$id])) {
        $newStatus = $font['status'] ? '禁用' : '启用';
        Logger::log(
            "字体状态变更：{$font['display_name']} - {$newStatus}",
            'FONT',
            [
                'font_id' => $font['id'],
                'font_name' => $font['name'],
                'old_status' => $font['status'] ? '启用' : '禁用',
                'new_status' => $newStatus
            ]
        );
    }
}

// 获取所有字体
$stmt = $pdo->query("SELECT * FROM fonts ORDER BY id DESC");
$fonts = $stmt->fetchAll();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>字体管理 - FontStory</title>
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/admin/css/admin.css">
    <?php
    // 加载所有字体的 CSS
    foreach ($fonts as $font) {
        echo "<link rel=\"stylesheet\" href=\"/fonts/{$font['folder_name']}/result.css\">\n";
    }
    ?>
</head>

<h1>字体管理</h1>

<div class="page-actions">
    <a href="upload.php" class="btn-primary">上传新字体</a>
</div>

<?php if (isset($message)): ?>
<div class="alert <?= $success ? 'alert-success' : 'alert-error' ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<div class="fonts-list">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>字体名称</th>
                <th>显示名称</th>
                <th>预览</th>
                <th>下载链接</th>
                <th>状态</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fonts as $font): ?>
            <tr>
                <td><?= $font['id'] ?></td>
                <td><?= htmlspecialchars($font['name']) ?></td>
                <td><?= htmlspecialchars($font['display_name']) ?></td>
                <td>
                    <div class="font-preview" style="font-family: '<?= htmlspecialchars($font['font_family']) ?>';">
                        字体预览 ABC
                    </div>
                </td>
                <td>
                    <a href="<?= htmlspecialchars($font['download_url']) ?>" target="_blank" class="link">
                        <?= htmlspecialchars($font['download_url']) ?>
                    </a>
                </td>
                <td>
                    <span class="status-badge <?= $font['status'] ? 'active' : 'inactive' ?>">
                        <?= $font['status'] ? '启用' : '禁用' ?>
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $font['id'] ?>">
                            <button type="submit" name="toggle_status" class="btn-action">
                                <?= $font['status'] ? '禁用' : '启用' ?>
                            </button>
                        </form>
                        <a href="edit_font.php?id=<?= $font['id'] ?>" class="btn-action">编辑</a>
                        <form method="post" style="display: inline;" onsubmit="return confirm('确定要删除这个字体吗？此操作不可恢复！')">
                            <input type="hidden" name="id" value="<?= $font['id'] ?>">
                            <button type="submit" name="delete" class="btn-action btn-danger">删除</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?> 