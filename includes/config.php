<?php
// SQLite数据库配置
define('DB_FILE', dirname(__DIR__) . '/database/fontstory.db');

// 网站配置
// 自动检测当前域名，如果需要可以通过环境变量覆盖
define('SITE_URL', 
    getenv('SITE_URL') ?: 
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . 
    ($_SERVER['HTTP_HOST'] ?? 'localhost')
);
define('SITE_NAME', 'FontStory');
define('SITE_DESCRIPTION', 'FontStory 是一个字体展示和下载平台，提供商用字体下载。');
// 暂时移除 ADMIN_EMAIL 常量，因为目前没有邮件功能

// 上传配置
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024);  // 10MB
define('ALLOWED_EXTENSIONS', ['ttf', 'otf', 'woff', 'woff2']); 

// 安全配置
define('SECURE_COOKIE', true);  // 仅在 HTTPS 下发送 cookie
define('SESSION_LIFETIME', 7200);  // 会话有效期 2 小时
define('MAX_LOGIN_ATTEMPTS', 5);  // 最大登录尝试次数
define('LOGIN_TIMEOUT', 1800);  // 登录超时时间 30 分钟

// 设置安全 headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// 数据库配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'fontstory');
define('DB_USER', 'root');
define('DB_PASS', ''); 