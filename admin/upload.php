<?php 
include 'includes/header.php';
include 'includes/font_processor.php';
require_once '../includes/logger.php';  // 添加 Logger 类引入

$message = '';
$success = false;
// 定义最大上传文件大小
$maxFileSize = 50 * 1024 * 1024; // 50MB

/**
 * 将字节转换为人类可读的格式
 * @param int $bytes 字节数
 * @param int $precision 精确度
 * @return string
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 验证表单数据
        $name = trim($_POST['name']);
        $display_name = trim($_POST['display_name']);
        $download_url = trim($_POST['download_url']);

        // 验证字体文件
        if (!isset($_FILES['font_file']) || $_FILES['font_file']['error'] !== 0) {
            throw new Exception('请选择字体文件');
        }

        $file = $_FILES['font_file'];
        if ($file['size'] > $maxFileSize) {
            throw new Exception('字体文件不能超过 ' . formatBytes($maxFileSize));
        }

        // 检查文件格式
        $allowedTypes = ['zip'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $allowedTypes)) {
            throw new Exception('请先在 chinese-font.netlify.app 对字体进行分包，并上传生成的 ZIP 文件');
        }

        // 处理字体上传
        $result = processUploadedFont($file, $name, $download_url);
        
        // 保存到数据库
        $stmt = $pdo->prepare("INSERT INTO fonts (name, display_name, font_family, download_url, folder_name) 
            VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt->execute([
            $name,
            $display_name,
            $result['font_family'],
            $download_url,
            $result['folder_name']
        ])) {
            $success = true;
            $message = '字体上传成功';
            // 延迟跳转，显示成功消息
            header("refresh:2;url=fonts.php");

            // 记录字体上传日志
            Logger::font("新增字体：{$display_name}", $_SESSION['admin_username']);

            // 记录日志
            Logger::log(
                "上传字体：{$display_name}",
                'FONT',
                [
                    'font_id' => $pdo->lastInsertId(),
                    'name' => $name,
                    'user' => $_SESSION['admin_username'],
                    'ip' => $_SERVER['REMOTE_ADDR']
                ]
            );
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
    }
}
?>

<h1>上传字体</h1>

<?php if ($message): ?>
<div class="alert <?= $success ? 'alert-success' : 'alert-error' ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<div class="upload-form-container">
    <form method="post" enctype="multipart/form-data" class="admin-form">
        <div class="form-group">
            <label>字体名称（英文）</label>
            <input type="text" name="name" pattern="[a-zA-Z0-9-]+" required
                value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            <small>只能包含字母、数字和连字符</small>
        </div>

        <div class="form-group">
            <label>显示名称（中文）</label>
            <input type="text" name="display_name" required
                value="<?= htmlspecialchars($_POST['display_name'] ?? '') ?>">
            <small>例如：思源黑体 Regular</small>
        </div>

        <div class="form-group">
            <label>下载链接</label>
            <input type="url" name="download_url" required
                value="<?= htmlspecialchars($_POST['download_url'] ?? '') ?>">
            <small>字体的官方下载地址</small>
        </div>

        <div class="form-group">
            <label>字体文件</label>
            <input type="file" name="font_file" accept=".zip" required>
            <small>请先在 <a href="https://chinese-font.netlify.app" target="_blank">chinese-font.netlify.app</a> 对字体进行分包，上传生成的ZIP文件（最大 <?= formatBytes($maxFileSize) ?>）</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">上传字体</button>
            <a href="fonts.php" class="btn-secondary">返回列表</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?> 