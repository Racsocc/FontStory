<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// 检查设置表内容
$query = $pdo->query("SELECT * FROM settings");
$settings = $query->fetchAll();

echo "<pre>";
print_r($settings);
echo "</pre>"; 