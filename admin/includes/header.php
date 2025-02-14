<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/logger.php';

// 检查是否已登录
if (!isset($_SESSION['admin_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: login.php');
    exit;
}

// 获取当前页面的文件名
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'FontStory 管理后台' ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h2><a href="/" target="_blank">FontStory</a></h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">控制面板</a>
                <a href="seo.php" class="nav-item <?= $currentPage === 'seo.php' ? 'active' : '' ?>">SEO设置</a>
                <a href="fonts.php" class="nav-item <?= $currentPage === 'fonts.php' ? 'active' : '' ?>">字体管理</a>
                <a href="upload.php" class="nav-item <?= $currentPage === 'upload.php' ? 'active' : '' ?>">字体上传</a>
                <a href="profile.php" class="nav-item <?= $currentPage === 'profile.php' ? 'active' : '' ?>">修改密码</a>
                <a href="logs.php" class="nav-item <?= $currentPage === 'logs.php' ? 'active' : '' ?>">系统日志</a>
                <a href="logout.php" class="nav-item">退出登录</a>
            </nav>
        </div>
        <div class="admin-content"> 