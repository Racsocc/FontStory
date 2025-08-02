<?php
/**
 * 处理上传的字体文件
 */
function processUploadedFont($file, $name, $download_url) {
    // 创建字体目录
    $folder_name = sanitizeFolderName($name);
    $font_dir = __DIR__ . "/../../fonts/$folder_name";
    
    if (!file_exists($font_dir)) {
        mkdir($font_dir, 0755, true);
    }

    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // 只处理 ZIP 文件
    if ($fileExt !== 'zip') {
        throw new Exception('请先在 chinese-font.netlify.app 对字体进行分包，并上传生成的 ZIP 文件');
    }
    
    // 处理 ZIP 文件
    $zip = new ZipArchive;
    if ($zip->open($file['tmp_name']) === TRUE) {
        // 解压到临时目录
        $temp_dir = sys_get_temp_dir() . '/' . uniqid('font_');
        mkdir($temp_dir);
        $zip->extractTo($temp_dir);
        $zip->close();

        // 检查必要文件
        $resultCssPath = findFile($temp_dir, 'result.css');
        if (!$resultCssPath) {
            throw new Exception('ZIP包中缺少 result.css 文件，请确保使用 chinese-font.netlify.app 生成的ZIP包');
        }

        // 移动所有文件到字体目录
        $sourceDir = dirname($resultCssPath);
        moveDirectory($sourceDir, $font_dir);
        
        // 清理临时目录
        removeDirectory($temp_dir);
    } else {
        throw new Exception('无法打开ZIP文件');
    }

    // 从CSS文件中提取font-family
    $css_content = file_get_contents("$font_dir/result.css");
    preg_match("/font-family:\s*['\"](.*?)['\"]/", $css_content, $matches);
    $font_family = $matches[1] ?? $name;

    return [
        'folder_name' => $folder_name,
        'font_family' => $font_family
    ];
}

/**
 * 安全处理目录名
 */
function sanitizeFolderName($name) {
    return preg_replace('/[^a-zA-Z0-9-]/', '', $name);
}

/**
 * 移动目录及其内容
 */
function moveDirectory($source, $dest) {
    if (is_dir($source)) {
        @mkdir($dest);
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                if (is_dir("$source/$file")) {
                    moveDirectory("$source/$file", "$dest/$file");
                } else {
                    rename("$source/$file", "$dest/$file");
                }
            }
        }
        return true;
    }
    return false;
}

/**
 * 删除目录及其内容
 */
function removeDirectory($dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                if (is_dir("$dir/$file")) {
                    removeDirectory("$dir/$file");
                } else {
                    unlink("$dir/$file");
                }
            }
        }
        rmdir($dir);
        return true;
    }
    return false;
}

/**
 * 递归查找文件
 */
function findFile($dir, $filename) {
    if (is_file("$dir/$filename")) {
        return "$dir/$filename";
    }
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        
        $path = "$dir/$item";
        if (is_dir($path)) {
            $found = findFile($path, $filename);
            if ($found) return $found;
        }
    }
    
    return false;
} 