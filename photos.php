<?php
session_start();
require_once 'db.php';

// 使用 JOIN 語法，把照片跟它所屬的活動名稱一起撈出來
$sql = "SELECT p.*, e.title AS event_title 
        FROM photos p 
        JOIN events e ON p.event_id = e.id 
        ORDER BY p.uploaded_at DESC";

$stmt = $pdo->query($sql);
$photos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>溫馨活動相簿牆 - 失智症照護網</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        /* 照片縮圖的游標與 hover 動畫效果 */
        .gallery-img {
            height: 250px;
            width: 100%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease, filter 0.3s ease;
            border-radius: 10px;
        }
        .gallery-img:hover {
            transform: scale(1.05);
            filter: brightness(1.1);
        }
        .img-container {
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
                <li class="nav-item"><a class="nav-link" href="articles.php">科普文章</a></li>
                <li class="nav-item"><a class="nav-link active" href="events.php">活動與照片</a></li>
            </ul>
            <div class="d-flex text-white align-items-center">
                <?php if (isset($_SESSION['user_name'])): ?>
                    <span>哈囉，<?php echo htmlspecialchars($_SESSION['user_name']); ?>！</span>
                    <a href="logout.php" class="btn btn-sm btn-outline-light ms-3">登出</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-sm btn-light text-primary fw-bold">會員登入 / 註冊</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-5 mb-5">
    
    <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-3">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-images text-warning"></i> 溫馨活動相簿牆</h2>
            <p class="text-muted mb-0">回顧過去活動的感動瞬間，點擊照片即可放大觀賞</p>
        </div>
        <div>
            <a href="events.php" class="btn btn-outline-primary">
                <i class="bi bi-calendar-check"></i> 前往活動報名
            </a>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($photos as $photo): ?>
        <div class="col-md-4 col-lg-4">
            
            <div class="img-container position-relative">
                <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" 
                     class="gallery-img w-100" 
                     alt="活動照片"
                     data-bs-toggle="modal" 
                     data-bs-target="#photoModal<?php echo $photo['id']; ?>">
                     
                <span class="badge bg-dark bg-opacity-75 position-absolute bottom-0 start-0 m-2 p-2">
                    <?php echo htmlspecialchars($photo['event_title']); ?>
                </span>
            </div>

            <div class="modal fade" id="photoModal<?php echo $photo['id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content bg-transparent border-0">
                        <div class="modal-header border-0 pb-0">
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center pt-0">
                            <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" class="img-fluid rounded shadow-lg" alt="活動大圖">
                            <h5 class="text-white mt-3 fw-bold"><?php echo htmlspecialchars($photo['event_title']); ?></h5>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        <?php endforeach; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>