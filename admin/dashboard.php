<?php 
include 'includes/header.php';
?>

<h1>控制面板</h1>
<div class="dashboard-stats">
    <div class="stat-card">
        <h3>字体总数</h3>
        <?php
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM fonts");
        $result = $stmt->fetch();
        echo "<p>{$result['count']}</p>";
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 