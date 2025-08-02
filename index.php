<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

try {
    // 检查数据库表是否存在
    $tableExists = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='fonts'")
                      ->fetchColumn();
    
    if (!$tableExists) {
        die('
        <div style="text-align:center;margin-top:50px;font-family:system-ui;">
            <h1 style="color:#333;font-size:24px;margin-bottom:20px;">系统提示</h1>
            <p style="color:#666;font-size:16px;margin-bottom:20px;">数据库未初始化，请先运行初始化脚本</p>
            <a href="admin/init_db.php" style="
                display: inline-block;
                padding: 10px 20px;
                background-color: #2d2d2d;
                color: #fff;
                text-decoration: none;
                border-radius: 4px;
                font-size: 14px;
                transition: background-color 0.3s ease;
                border: none;
                cursor: pointer;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                运行初始化脚本
            </a>
        </div>
        ');
    }

    // 获取所有启用的字体
    $stmt = $pdo->query("SELECT * FROM fonts WHERE status = 1 ORDER BY id DESC");
    $fonts = $stmt->fetchAll();

} catch (PDOException $e) {
    die('<div style="text-align:center;margin-top:50px;font-family:system-ui;">
        <h1>系统提示</h1>
        <p style="color:#666;">数据库连接失败，请检查配置或联系管理员</p>
        <p style="color:#999;font-size:14px;">错误信息：' . htmlspecialchars($e->getMessage()) . '</p>
        </div>');
} catch (Exception $e) {
    die('<div style="text-align:center;margin-top:50px;font-family:system-ui;">
        <h1>系统提示</h1>
        <p style="color:#666;">' . $e->getMessage() . '</p>
        </div>');
}

// 获取网站设置
$settings = [];
try {
    if ($pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='settings'")->fetchColumn()) {
        $query = $pdo->query("SELECT name, value FROM settings");
        while ($row = $query->fetch()) {
            $settings[$row['name']] = $row['value'];
        }
    }
} catch (PDOException $e) {
    // 设置读取失败时使用默认值
    error_log("设置读取失败: " . $e->getMessage());
}

// 使用设置值，如果没有则使用默认值
$site_name = $settings['site_name'] ?? 'FontStory';
$site_description = $settings['description'] ?? '';
$site_keywords = $settings['keywords'] ?? '';
$demo_text = $settings['demo_text'] ?? 'FontStory 字体展示平台';
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($site_name) ?> - 字体展示平台</title>
    <meta name="description" content="<?= htmlspecialchars($site_description) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($site_keywords) ?>">
    <link rel="icon" href="<?= htmlspecialchars($settings['favicon'] ?? '/favicon.ico') ?>">
    <link rel="preload" href="css/styles.css" as="style">
    <link rel="stylesheet" href="css/styles.css">
    <?php
    // 获取所有字体的 CSS
    foreach ($fonts as $font) {
        echo "<link rel=\"stylesheet\" href=\"fonts/{$font['folder_name']}/result.css\">\n";
        
        // 修改文件路径检查
        $fontFile = __DIR__ . "/fonts/{$font['folder_name']}/font." . pathinfo($font['download_url'], PATHINFO_EXTENSION);
        if (file_exists($fontFile)) {
            $webPath = "fonts/{$font['folder_name']}/font." . pathinfo($font['download_url'], PATHINFO_EXTENSION);
            echo "<link rel=\"preload\" href=\"$webPath\" as=\"font\" crossorigin>\n";
        }
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 检查样式是否正确加载
        const styles = getComputedStyle(document.body);
        if (!styles.backgroundColor) {
            console.error('CSS可能未正确加载，尝试重新加载');
            location.reload(true);
        }
    });
    </script>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1><?= htmlspecialchars($site_name) ?></h1>
        </header>

        <div class="text-input-container">
            <input type="text" class="text-input" 
                   placeholder="<?= htmlspecialchars($demo_text) ?>" 
                   value="<?= htmlspecialchars($demo_text) ?>">
        </div>

        <div class="font-size-controls">
            <div class="control-buttons">
                <button type="button" class="size-btn" data-size="small">小</button>
                <button type="button" class="size-btn" data-size="medium">中</button>
                <button type="button" class="size-btn" data-size="large">大</button>
                <button type="button" class="size-btn" data-size="xlarge">超大</button>
                <button type="button" class="size-btn" data-size="jumbo">特大</button>
            </div>
            <div class="theme-controls">
                <button type="button" class="size-btn" data-theme="light">浅色</button>
                <button type="button" class="size-btn" data-theme="dark">深色</button>
            </div>
        </div>

        <?php foreach ($fonts as $font): ?>
        <div class="font-demo">
            <div class="font-info">
                <span><?= htmlspecialchars($font['name']) ?></span> &nbsp;&nbsp; | &nbsp;&nbsp; 
                <span><?= htmlspecialchars($font['display_name']) ?></span>
                <a href="<?= htmlspecialchars($font['download_url']) ?>" target="_blank" 
                   class="font-download-link">下载字体</a>
            </div>
            <div class="font-sample text-large" 
                 style="font-family: '<?= htmlspecialchars($font['font_family']) ?>';">
                <?= htmlspecialchars($demo_text) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <footer>
        <nav>
            <div class="copyright">
                <?= htmlspecialchars($settings['copyright'] ?? '© 2025 FontStory.') ?>
            </div>
            <div class="contact">
                <a href="https://github.com/Racsocc/FontStory" target="_blank" style="color: inherit; text-decoration: none;">
                    <?= htmlspecialchars($settings['footer_info'] ?? 'Github') ?>
                </a>
            </div>
        </nav>
    </footer>
    <script src="js/main.js"></script>
</body>
</html> 