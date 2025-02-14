<?php
include 'includes/header.php';
require_once '../includes/logger.php';

// 分页参数
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 50;
// 限制每页显示数量的范围
$perPage = min(max($perPage, 50), 500);

// 日期筛选
$date = date('Y-m-d');
$type = isset($_GET['type']) ? $_GET['type'] : '';
$search = $_GET['search'] ?? '';

if (isset($_GET['days'])) {
    $days = (int)$_GET['days'];
    $logs = [];
    // 读取指定天数范围内的日志
    for ($i = 0; $i < $days; $i++) {
        $currentDate = date('Y-m-d', strtotime("-$i days"));
        $currentFile = "../logs/$currentDate.log";
        if (file_exists($currentFile)) {
            $lines = file($currentFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $log = json_decode($line, true);
                if ($type === '' || $log['type'] === $type) {
                    $logs[] = $log;
                }
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

    // 倒序排列
    $logs = array_reverse($logs);

    // 分页
    $totalLogs = count($logs);
    $totalPages = ceil($totalLogs / $perPage);
    $logs = array_slice($logs, ($page - 1) * $perPage, $perPage);
} else {
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

    // 读取日志文件
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

    // 倒序排列
    $logs = array_reverse($logs);

    // 分页
    $totalLogs = count($logs);
    $totalPages = ceil($totalLogs / $perPage);
    $logs = array_slice($logs, ($page - 1) * $perPage, $perPage);
}
?>

<h1>系统日志</h1>

<div class="log-stats">
    共 <?= $totalLogs ?> 条日志
    <?php if ($type): ?>
        <span class="separator">|</span>
        类型：<?= $type ?>
    <?php endif; ?>
    <?php if ($search): ?>
        <span class="separator">|</span>
        搜索：<?= htmlspecialchars($search) ?>
    <?php endif; ?>
    <?php if (isset($_GET['days'])): ?>
        <span class="separator">|</span>
        时间范围：<?= $_GET['days'] ?>天
    <?php endif; ?>
</div>

<div class="log-filters">
    <form method="get" class="admin-form">
        <div class="filter-group">
            <div class="type-filters">
                <a href="?days=7&page=<?= $page ?><?= $type ? "&type=$type" : '' ?><?= $search ? "&search=" . urlencode($search) : '' ?>" 
                   class="type-btn <?= isset($_GET['days']) && $_GET['days'] == '7' ? 'active' : '' ?>">7天</a>
                <a href="?days=30&page=<?= $page ?><?= $type ? "&type=$type" : '' ?><?= $search ? "&search=" . urlencode($search) : '' ?>" 
                   class="type-btn <?= isset($_GET['days']) && $_GET['days'] == '30' ? 'active' : '' ?>">30天</a>
                <a href="?days=60&page=<?= $page ?><?= $type ? "&type=$type" : '' ?><?= $search ? "&search=" . urlencode($search) : '' ?>" 
                   class="type-btn <?= isset($_GET['days']) && $_GET['days'] == '60' ? 'active' : '' ?>">60天</a>
                <a href="?days=90&page=<?= $page ?><?= $type ? "&type=$type" : '' ?><?= $search ? "&search=" . urlencode($search) : '' ?>" 
                   class="type-btn <?= isset($_GET['days']) && $_GET['days'] == '90' ? 'active' : '' ?>">90天</a>
                <span class="type-separator">或</span>
                <input type="date" name="date" value="<?= htmlspecialchars($date) ?>"
                       <?= isset($_GET['days']) ? 'style="display:none"' : '' ?>>
            </div>
            <input type="text" name="search" 
                   value="<?= htmlspecialchars($search) ?>" 
                   placeholder="搜索日志..." 
                   class="log-search">
            <div class="type-filters">
                <a href="?<?= isset($_GET['days']) ? "days={$_GET['days']}" : "date=$date" ?>" 
                   class="type-btn <?= $type === '' ? 'active' : '' ?>">全部</a>
                <a href="?<?= isset($_GET['days']) ? "days={$_GET['days']}" : "date=$date" ?>&type=INFO" 
                   class="type-btn <?= $type === 'INFO' ? 'active' : '' ?>">普通</a>
                <a href="?<?= isset($_GET['days']) ? "days={$_GET['days']}" : "date=$date" ?>&type=WARNING" 
                   class="type-btn <?= $type === 'WARNING' ? 'active' : '' ?>">警告</a>
                <a href="?<?= isset($_GET['days']) ? "days={$_GET['days']}" : "date=$date" ?>&type=ERROR" 
                   class="type-btn <?= $type === 'ERROR' ? 'active' : '' ?>">错误</a>
                <a href="?<?= isset($_GET['days']) ? "days={$_GET['days']}" : "date=$date" ?>&type=SECURITY" 
                   class="type-btn <?= $type === 'SECURITY' ? 'active' : '' ?>">安全</a>
                <a href="?<?= isset($_GET['days']) ? "days={$_GET['days']}" : "date=$date" ?>&type=FONT" 
                   class="type-btn <?= $type === 'FONT' ? 'active' : '' ?>">字体</a>
            </div>
            <div class="type-filters">
                <a href="export_logs.php?date=<?= $date ?>&type=<?= $type ?>&search=<?= urlencode($search) ?>" 
                   class="type-btn" target="_blank">导出</a>
            </div>
            <?php if ($type !== '' || $search !== '' || isset($_GET['days'])): ?>
            <div class="type-filters">
                <a href="?date=<?= $date ?>" class="type-btn">
                    <?= isset($_GET['days']) ? '返回单日' : '清除' ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="logs-list">
    <?php if (empty($logs)): ?>
    <div class="empty-state">
        <p>暂无日志记录</p>
    </div>
    <?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>时间</th>
                <th>类型</th>
                <th>用户</th>
                <th>IP</th>
                <th>消息</th>
                <th style="width: 300px">详情</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['timestamp']) ?></td>
                <td>
                    <span class="log-badge <?= strtolower($log['type']) ?>">
                        <?= htmlspecialchars($log['type']) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($log['user']) ?></td>
                <td><?= htmlspecialchars($log['ip']) ?></td>
                <td><?= htmlspecialchars($log['message']) ?></td>
                <td>
                    <?php if (!empty($log['details'])): ?>
                    <div class="details-container">
                        <pre class="log-details"><?= htmlspecialchars(json_encode($log['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                        <button type="button" class="toggle-details">展开</button>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<div class="pagination">
    <?php if ($totalPages > 1): ?>
    <div class="pagination-controls">
        <?php if ($page > 1): ?>
        <a href="?date=<?= $date ?>&type=<?= $type ?>&search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>" 
           class="page-link nav-btn">上一页</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php
            // 只显示当前页附近的页码
            if ($i == 1 || $i == $totalPages || 
                ($i >= $page - 2 && $i <= $page + 2)): 
        ?>
            <?php if ($i != 1 && $i != $page - 2 && $i > 2): ?>
                <span class="page-ellipsis">...</span>
            <?php endif; ?>
            <a href="?date=<?= $date ?>&type=<?= $type ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>" 
               class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php if ($i != $totalPages && $i != $page + 2 && $i < $totalPages - 1): ?>
                <span class="page-ellipsis">...</span>
            <?php endif; ?>
        <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <a href="?date=<?= $date ?>&type=<?= $type ?>&search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>" 
           class="page-link nav-btn">下一页</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="per-page-controls">
        <div class="type-filters">
        <?php
        $perPageOptions = [50, 100, 200, 500];
        foreach ($perPageOptions as $option): ?>
            <a href="?date=<?= $date ?>&type=<?= $type ?>&search=<?= urlencode($search) ?>&page=1&per_page=<?= $option ?>" 
               class="type-btn <?= $perPage == $option ? 'active' : '' ?>"><?= $option ?>条</a>
        <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-details').forEach(toggle => {
        const details = toggle.closest('tr').querySelector('.log-details');
        if (details) {
            toggle.addEventListener('click', function() {
                details.classList.toggle('expanded');
                toggle.textContent = details.classList.contains('expanded') ? '收起' : '展开';
            });
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?> 