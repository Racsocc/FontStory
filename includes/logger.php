<?php
class Logger {
    private static $logPath = __DIR__ . '/../logs/';
    // 不同类型日志的保留天数
    private static $retentionPeriods = [
        'INFO' => 90,       // 普通操作日志：90天
        'WARNING' => 180,   // 警告日志：180天
        'ERROR' => 270,     // 错误日志：270天
        'SECURITY' => 540,  // 安全相关日志：540天
        'FONT' => 30        // 字体状态变更：30天
    ];
    
    public static function log($message, $type = 'INFO', $details = []) {
        $date = date('Y-m-d H:i:s');
        $logFile = self::$logPath . date('Y-m-d') . '.log';
        
        $logData = [
            'timestamp' => $date,
            'type' => $type,
            'message' => $message,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user' => $_SESSION['username'] ?? 'guest',
            'details' => $details
        ];
        
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
        
        $content = json_encode($logData, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents($logFile, $content, FILE_APPEND);
        
        // 清理旧日志
        self::cleanOldLogs();
    }
    
    private static function cleanOldLogs() {
        $files = glob(self::$logPath . '*.log');
        $now = time();
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $lines = explode("\n", trim($content));
            $newLines = [];
            
            foreach ($lines as $line) {
                $log = json_decode($line, true);
                if ($log) {
                    $logTime = strtotime($log['timestamp']);
                    $retentionDays = self::$retentionPeriods[$log['type']] ?? 90;
                    
                    // 如果日志未过期，保留该条日志
                    if ($now - $logTime <= $retentionDays * 24 * 3600) {
                        $newLines[] = $line;
                    }
                }
            }
            
            // 如果文件中还有未过期的日志，更新文件
            if (!empty($newLines)) {
                file_put_contents($file, implode("\n", $newLines) . "\n");
            } else {
                // 如果所有日志都已过期，删除文件
                unlink($file);
            }
        }
    }

    public static function info($message, $user = null) {
        self::log($message, 'INFO', $user);
    }

    public static function error($message, $user = null) {
        self::log($message, 'ERROR', $user);
    }

    public static function warning($message, $user = null) {
        self::log($message, 'WARNING', $user);
    }

    public static function security($message, $user = null) {
        self::log($message, 'SECURITY', $user);
    }

    public static function font($message, $user = null) {
        self::log($message, 'FONT', $user);
    }
} 