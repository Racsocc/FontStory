<?php
session_start();
require_once '../includes/logger.php';

if (isset($_SESSION['username'])) {
    Logger::log("管理员退出登录: {$_SESSION['username']}", 'SECURITY');
}

session_destroy();
header('Location: login.php');
exit; 