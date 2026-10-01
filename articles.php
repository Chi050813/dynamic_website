<?php
session_start();
require_once 'db.php';

// 從資料庫撈出所有文章，並依照建立時間由新到舊排序
$stmt = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC");
$articles = $stmt->fetchAll();

// 💡 新增：建立隨機標語陣列 (你可以隨時在這裡新增更多句子)
$slogans = [
    "愛在記憶深處，將愛記在腦裡。",
    "失智不是正常老化，及早發現及早治療。",
    "多一分理解，少一分責備，陪伴是最好的解藥。",
    "活到老、動到老，規律運動與社交是防失智良藥。",
    "忘記了沒關係，我們陪您一起慢慢走。",
    "關心長輩的日常小改變，為大腦健康把關。",
    "地中海飲食搭配健走，幫大腦存健康的本錢。"
];

// 使用 array_rand 隨機抽出一個陣列的索引(Index)，然後取出該句話
$random_slogan = $slogans[array_rand($slogans)];
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>失智症科普專區</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .article-card { transition: transform 0.2s; }
        .article-card:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #1976d2;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-heart-pulse-fill text-danger"></i> 失智症照護網</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">首頁</a></li>
                <li class="nav-item"><a class="nav-link" href="ad8_test.php">AD-8測驗</a></li>
                <li class="nav-item"><a class="nav-link" href="doctors.php">醫療據點</a></li>
                <li class="nav-item"><a class="nav-link active" href="articles.php">科普文章</a></li>
                <li class="nav-item"><a class="nav-link" href="events.php">活動與照片</a></li>
            </ul>
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin_dashboard.php" class="btn btn-sm btn-warning text-dark fw-bold me-3">
                            <i class="bi bi-gear-fill"></i> 後台管理
                        </a>
                    <?php endif; ?>
                    <a href="ad8_history.php" class="text-white text-decoration-none me-3 d-flex align-items-center">
                        <i class="bi bi-person-circle fs-5 me-1"></i> 
                        <span><?php echo htmlspecialchars($_SESSION['user_name']); ?> 的紀錄</span>
                    </a>
                    <a href="logout.php" class="btn btn-sm btn-outline-light">登出</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-sm btn-light text-primary fw-bold">會員登入 / 註冊</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-5 mb-5">
    
    <div class="text-center mb-5">
        <h2 class="fw-bold"><i class="bi bi-book text-primary"></i> 失智症科普專區</h2>
        <p class="text-muted mb-3">提供最新的衛教知識，陪伴您了解失智、預防失智</p>
        
        <span class="badge rounded-pill bg-info text-dark bg-opacity-25 px-4 py-2 fw-normal fs-6 shadow-sm border border-info border-opacity-25">
            <i class="bi bi-chat-quote-fill text-primary me-2"></i><?php echo htmlspecialchars($random_slogan); ?>
        </span>
    </div>

    <div class="row g-4">
        <?php foreach ($articles as $article): ?>
        <div class="col-md-4">
            <div class="card h-100 article-card shadow-sm border-0">
                
                <?php if (!empty($article['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($article['image_path']); ?>" 
                         class="card-img-top" 
                         alt="文章附圖" 
                         style="height: 200px; object-fit: cover;"
                         onerror="this.style.display='none'">
                <?php endif; ?>
                
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title fw-bold text-dark"><?php echo htmlspecialchars($article['title']); ?></h5>
                    <div class="mb-2 text-muted small">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($article['author']); ?> | 
                        <i class="bi bi-clock"></i> <?php echo date('Y-m-d', strtotime($article['created_at'])); ?>
                    </div>
                    <p class="card-text text-muted text-truncate-2 mb-4">
                        <?php echo htmlspecialchars($article['content']); ?>
                    </p>
                    <a href="article_read.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-primary mt-auto rounded-pill">閱讀全文</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>