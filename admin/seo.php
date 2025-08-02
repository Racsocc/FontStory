<?php
include 'includes/header.php';
require_once '../includes/db.php';  // 确保数据库连接被引入

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'site_name' => $_POST['site_name'] ?? 'FontStory',
        'favicon' => $_POST['favicon'] ?? '/favicon.ico',
        'keywords' => $_POST['keywords'],
        'description' => $_POST['description'],
        'copyright' => $_POST['copyright'],
        'footer_info' => $_POST['footer_info'],
        'demo_text' => $_POST['demo_text'] ?? 'FontStory 字体展示平台'
    ];
    
    // 保存到数据库
    $stmt = $pdo->prepare("INSERT OR REPLACE INTO settings (name, value) VALUES (?, ?)");
    foreach ($settings as $name => $value) {
        $stmt->execute([$name, $value]);
    }
    
    // 设置一个会话消息
    $_SESSION['settings_updated'] = true;
    
    // 重定向回设置页面
    header('Location: seo.php');
    exit;
}

// 检查是否有保存成功的消息
if (isset($_SESSION['settings_updated'])) {
    $success = true;
    // 清除会话消息
    unset($_SESSION['settings_updated']);
}

// 获取当前设置
$settings = [];
try {
    // 检查表是否存在
    $result = $pdo->query("SELECT tbl_name FROM sqlite_master WHERE type='table' AND tbl_name='settings'");
    if ($result->fetch()) {
        $query = $pdo->query("SELECT name, value FROM settings");
        while ($row = $query->fetch()) {
            $settings[$row['name']] = $row['value'];
        }
    } else {
        echo '<div class="alert alert-warning">请先运行 <a href="init_settings.php">初始化脚本</a> 创建设置表。</div>';
    }
} catch (PDOException $e) {
    echo '<div class="alert alert-error">获取设置失败: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>

<h1>SEO设置</h1>

<form method="post" class="admin-form">
    <div class="form-group">
        <label for="site_name">网站名称</label>
        <input type="text" id="site_name" name="site_name" 
               value="<?= htmlspecialchars($settings['site_name'] ?? 'FontStory') ?>" required>
    </div>
    
    <div class="form-group">
        <label for="favicon">Favicon路径</label>
        <input type="text" id="favicon" name="favicon" 
               value="<?= htmlspecialchars($settings['favicon'] ?? '/favicon.ico') ?>" required>
        <small>网站图标的路径，默认为 /favicon.ico</small>
    </div>
    
    <div class="form-group">
        <label for="keywords">首页关键词</label>
        <input type="text" id="keywords" name="keywords" 
               value="<?= htmlspecialchars($settings['keywords'] ?? '') ?>">
        <small>多个关键词用英文逗号分隔</small>
    </div>
    
    <div class="form-group">
        <label for="description">首页描述</label>
        <input type="text" id="description" name="description" 
               value="<?= htmlspecialchars($settings['description'] ?? '') ?>">
        <small>网站的简短描述，用于搜索引擎展示</small>
    </div>
    
    <div class="form-group">
        <label for="copyright">页脚版权信息</label>
        <input type="text" id="copyright" name="copyright" 
               value="<?= htmlspecialchars($settings['copyright'] ?? '') ?>">
        <small>例如：© 2024 FontStory.</small>
    </div>
    
    <div class="form-group">
        <label for="footer_info">页脚右侧信息</label>
        <input type="text" id="footer_info" name="footer_info" 
               value="<?= htmlspecialchars($settings['footer_info'] ?? '') ?>">
    </div>
    
    <div class="form-group">
        <label for="demo_text">默认字体演示文本</label>
        <input type="text" id="demo_text" name="demo_text" 
               value="<?= htmlspecialchars($settings['demo_text'] ?? 'FontStory 字体展示平台') ?>" required>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn-primary">保存设置</button>
        <?php if (isset($success)): ?>
        <div class="alert alert-success inline-alert" id="saveAlert">设置已保存</div>
        <?php endif; ?>
    </div>
</form>

<script>
// 如果存在成功消息
const alertSuccess = document.querySelector('#saveAlert');
if (alertSuccess) {
    // 3秒后开始淡出
    setTimeout(() => {
        alertSuccess.style.transition = 'opacity 0.5s ease-out';
        alertSuccess.style.opacity = '0';
        
        // 淡出动画结束后移除元素
        setTimeout(() => {
            alertSuccess.remove();
        }, 500);
    }, 3000);
}
</script>

<?php include 'includes/footer.php'; ?> 