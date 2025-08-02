<?php
/**
 * 解析 result.css 文件中的 font-family
 */
function parseFontFamily($cssFile) {
    if (!file_exists($cssFile)) {
        return null;
    }
    
    $content = file_get_contents($cssFile);
    if (preg_match('/font-family:\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
        return $matches[1];
    }
    
    return null;
}

/**
 * 生成字体CSS
 */
function generateFontCSS($fontFile, $outputDir) {
    // 确保输出目录存在
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    // 获取字体格式
    $ext = strtolower(pathinfo($fontFile, PATHINFO_EXTENSION));
    $format = [
        'ttf' => 'truetype',
        'otf' => 'opentype',
        'woff' => 'woff',
        'woff2' => 'woff2'
    ][$ext] ?? 'truetype';

    // 生成字体名称（使用文件夹名称作为字体名称）
    $fontName = basename($outputDir);
    
    // 生成 result.css 内容
    $css = "@font-face {\n";
    $css .= "    font-family: '$fontName';\n";
    $css .= "    src: url('font.$ext') format('$format');\n";
    $css .= "    font-display: swap;\n";
    $css .= "}\n";

    // 保存 CSS 文件
    file_put_contents("$outputDir/result.css", $css);
    
    return $fontName;
}

/**
 * 自动处理上传的字体文件
 */
function processUploadedFont($file, $displayName, $downloadUrl) {
    // 创建字体目录
    $folderName = sanitizeFolderName($displayName);
    $fontDir = "../fonts/$folderName";
    
    // 处理字体文件
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        throw new Exception('不支持的字体格式');
    }
    
    // 创建目录并移动文件
    if (!is_dir($fontDir)) {
        mkdir($fontDir, 0755, true);
    }
    
    $targetFile = "$fontDir/font.$ext";
    if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
        throw new Exception('文件上传失败');
    }
    
    // 生成 CSS
    $fontFamily = generateFontCSS($targetFile, $fontDir);
    
    return [
        'folder_name' => $folderName,
        'font_family' => $fontFamily
    ];
}

/**
 * 清理文件夹名称
 */
function sanitizeFolderName($name) {
    // 移除非法字符，只保留字母、数字和连字符
    $name = preg_replace('/[^a-zA-Z0-9-]/', '', $name);
    return strtolower($name);
}

/**
 * 格式化文件大小
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
}

/**
 * 记录错误日志
 */
function logError($message, $context = []) {
    $logFile = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    $logMessage = "[$timestamp] $message $contextStr\n";
    
    error_log($logMessage, 3, $logFile);
}

/**
 * 全局错误处理
 */
function handleError($errno, $errstr, $errfile, $errline) {
    logError("PHP Error [$errno]: $errstr in $errfile on line $errline");
    return false;
}

// 设置错误处理器
set_error_handler('handleError');

/**
 * 验证字体文件
 */
function validateFontFile($file) {
    // 检查文件大小
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        throw new Exception('文件大小超过限制：' . formatBytes(UPLOAD_MAX_SIZE));
    }
    
    // 检查文件类型
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        throw new Exception('不支持的文件格式：' . $ext);
    }
    
    // 检查文件完整性
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new Exception('文件上传失败或被篡改');
    }
    
    // 检查文件头部标识
    $handle = fopen($file['tmp_name'], 'rb');
    $header = fread($handle, 4);
    fclose($handle);
    
    $validHeaders = [
        'ttf' => ["\x00\x01\x00\x00", "\x74\x72\x75\x65"],
        'otf' => ["\x4F\x54\x54\x4F"],
        'woff' => ["\x77\x4F\x46\x46"],
        'woff2' => ["\x77\x4F\x46\x32"]
    ];
    
    if (!isset($validHeaders[$ext]) || !in_array($header, $validHeaders[$ext])) {
        throw new Exception('无效的字体文件');
    }
    
    return true;
}

// XSS防护
function xssClean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// CSRF令牌生成
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF验证
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

// 文件上传安全检查
function validateUploadedFile($file) {
    $allowedTypes = ['zip'];
    $maxSize = 50 * 1024 * 1024; // 50MB
    
    if ($file['size'] > $maxSize) {
        throw new Exception('文件大小超过限制');
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) {
        throw new Exception('不支持的文件类型');
    }
    
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new Exception('非法的文件上传');
    }
    
    return true;
} 