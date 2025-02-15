<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/logger.php';

try {
    // 清理日志文件
    $logPath = __DIR__ . '/../logs/';
    if (is_dir($logPath)) {
        array_map('unlink', glob($logPath . '*.log'));
    }

    // 清理字体文件夹
    $fontsPath = __DIR__ . '/../fonts/';
    if (is_dir($fontsPath)) {
        // 删除所有子目录和文件
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fontsPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
    }

    // 先删除已存在的表
    $pdo->exec("DROP TABLE IF EXISTS admin_users");
    $pdo->exec("DROP TABLE IF EXISTS fonts");
    $pdo->exec("DROP TABLE IF EXISTS settings");

    // 创建管理员表
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        reset_token TEXT,
        reset_expires DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 创建字体表
    $pdo->exec("CREATE TABLE IF NOT EXISTS fonts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        display_name TEXT NOT NULL,
        font_family TEXT NOT NULL,
        download_url TEXT NOT NULL,
        folder_name TEXT NOT NULL,
        status INTEGER DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 创建设置表
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL,
        value TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 插入默认管理员账号
    $defaultAdmin = [
        'username' => 'fontstory',
        'password' => password_hash('fontstory', PASSWORD_DEFAULT),
        'email' => 'admin@fontstory'
    ];

    $stmt = $pdo->prepare("INSERT OR IGNORE INTO admin_users (username, password, email) VALUES (?, ?, ?)");
    $stmt->execute([$defaultAdmin['username'], $defaultAdmin['password'], $defaultAdmin['email']]);

    // 插入默认设置
    $defaultSettings = [
        'site_name' => 'FontStory',
        'favicon' => '/favicon.ico',
        'keywords' => '字体展示,字体预览,字体下载',
        'description' => 'FontStory 是一个字体展示和下载平台，提供商用字体下载。',
        'copyright' => '© 2025 FontStory.',
        'footer_info' => 'GitHub',
        'demo_text' => 'FontStory 字体展示平台'
    ];

    $stmt = $pdo->prepare("INSERT OR REPLACE INTO settings (name, value) VALUES (?, ?)");
    foreach ($defaultSettings as $name => $value) {
        $stmt->execute([$name, $value]);
    }

    // 记录成功日志
    Logger::info('数据库初始化成功', 'SYSTEM');
    
    die('
    <div style="text-align:center;margin-top:50px;font-family:system-ui;">
        <h1 style="color:#333;font-size:24px;margin-bottom:20px;">初始化成功</h1>
        
        <div style="
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px auto;
            max-width: 400px;
            text-align: left;">
            <p style="color:#666;margin:10px 0;">
                <span style="color:#333;font-weight:500;">管理员账号：</span>fontstory
            </p>
            <p style="color:#666;margin:10px 0;">
                <span style="color:#333;font-weight:500;">管理员密码：</span>fontstory
            </p>
        </div>
        
        <a href="login.php" style="
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
            前往登录
        </a>
    </div>
    ');

} catch (PDOException $e) {
    // 记录错误日志
    Logger::error('数据库初始化失败: ' . $e->getMessage(), 'SYSTEM');
    die("初始化失败: " . $e->getMessage());
} 