<?php
include 'includes/header.php';
require_once '../includes/logger.php';

$date = $_GET['date'] ?? date('Y-m-d');
$type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

// 设置响应头
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="logs-' . $date . '.json"');

// 读取日志
$logFile = "../logs/$date.log";
$logs = [];

if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $log = json_decode($line, true);
        if ($type === '' || $log['type'] === $type) {
            $logs[] = $log;
        }
    }
}

// 搜索过滤
if ($search !== '') {
    $logs = array_filter($logs, function($log) use ($search) {
        return strpos(strtolower($log['message']), strtolower($search)) !== false ||
               strpos(strtolower($log['user']), strtolower($search)) !== false ||
               strpos($log['ip'], $search) !== false;
    });
}

// 输出 JSON
echo json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); 