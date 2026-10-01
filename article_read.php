<?php
session_start();
require_once 'db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("找不到文章！請回上一頁。");
}

$article_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    die("文章不存在或已刪除！");
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($article['title']); ?> - 失智症照護網</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .article-content {
            line-height: 1.8;
            font-size: 1.1rem;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #1976d2;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-heart-pulse-fill text-danger"></i> 失智症照護網</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">首頁</a></li>
                <li class="nav-item"><a class="nav-link" href="ad8_test.php">AD-8測驗</a></li>
                <li class="nav-item"><a class="nav-link" href="doctors.php">醫療據點</a></li>
                <li class="nav-item"><a class="nav-link active" href="articles.php">科普文章</a></li>
                <li class="nav-item"><a class="nav-link" href="events.php">活動與照片</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <a href="articles.php" class="text-decoration-none text-secondary mb-3 d-inline-block">&larr; 返回文章列表</a>
            
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($article['title']); ?></h1>
                    
                    <div class="text-muted mb-4 border-bottom pb-3">
                        <i class="bi bi-person-circle"></i> 撰文者：<?php echo htmlspecialchars($article['author']); ?> | 
                        <i class="bi bi-clock"></i> 發布日期：<?php echo date('Y-m-d', strtotime($article['created_at'])); ?>
                    </div>
                    
                    <?php if (!empty($article['image_path'])): ?>
                        <div class="text-center mb-4">
                            <img src="<?php echo htmlspecialchars($article['image_path']); ?>" 
                                 class="img-fluid rounded shadow-sm" 
                                 alt="文章附圖" 
                                 style="max-height: 400px; width: 100%; object-fit: cover;"
                                 onerror="this.style.display='none'">
                        </div>
                    <?php endif; ?>
                    
                    <div class="article-content text-dark">
                        <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>