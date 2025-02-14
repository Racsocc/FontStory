<?php
try {
    // 确保数据库目录存在
    $dbDir = dirname(DB_FILE);
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
    }

    $pdo = new PDO("sqlite:" . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // 启用外键约束
    $pdo->exec('PRAGMA foreign_keys = ON');
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

function initDatabase($pdo) {
    // 检查是否需要初始化
    $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='admin_users'");
    if (!$result->fetch()) {
        // 创建管理员用户表
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

        // 插入默认管理员账号
        $hash = password_hash('fontstory', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute(['fontstory', $hash, 'admin@fontstory']);
    }
} 