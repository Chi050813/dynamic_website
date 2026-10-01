<?php
session_start();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>失智症照護網 - 陪伴您與家人</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        /* 自訂樣式讓畫面更好看 */
        .hero-section {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 80px 0;
            border-bottom: 5px solid #2196f3;
        }
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .icon-wrapper {
            font-size: 3rem;
            color: #1976d2;
            margin-bottom: 15px;
        }
        .announcement-box {
            border-left: 5px solid #ff9800;
            background-color: #fff8e1;
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
                <li class="nav-item"><a class="nav-link active" href="index.php">首頁</a></li>
                <li class="nav-item"><a class="nav-link" href="ad8_test.php">AD-8測驗</a></li>
                <li class="nav-item"><a class="nav-link" href="doctors.php">醫療據點</a></li>
                <li class="nav-item"><a class="nav-link" href="articles.php">科普文章</a></li>
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

<header class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold text-primary mb-3">愛在記憶深處，將愛記在腦裡</h1>
        <p class="lead text-secondary mb-4">提供最專業的失智症資訊、極早期篩檢與全台醫療資源整合</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn btn-primary btn-lg px-4 rounded-pill shadow">立即加入會員</a>
        <?php else: ?>
            <a href="ad8_test.php" class="btn btn-primary btn-lg px-4 rounded-pill shadow">開始進行 AD-8 測驗</a>
        <?php endif; ?>
    </div>
</header>

<div class="container mt-5 mb-5">
    
    <div class="row mb-5 justify-content-center">
        <div class="col-lg-10">
            <div class="card announcement-box shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="me-4 text-warning">
                        <i class="bi bi-bell-fill fs-2"></i>
                    </div>
                    <div>
                        <h5 class="card-title fw-bold text-dark mb-1">最新公告</h5>
                        <ul class="mb-0 text-muted ps-3">
                            <li><strong>2026/06/09：</strong> 本週末（6/13）於台北市舉辦「家屬照護技巧工作坊」，歡迎至活動頁面報名！</li>
                            <li><strong>2026/06/01：</strong> 新增中南部 3 家失智症共照中心醫療據點資訊。</li>
                            <li><strong>提醒：</strong> AD-8 量表僅為初步篩檢，若分數異常請務必前往醫療院所由專業醫師診斷。</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5 mb-5">
        <h2 class="text-center fw-bold mb-4">快速服務導覽</h2>
        
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-5 g-4 justify-content-center">
            
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="display-4 text-primary mb-3">
                            <i class="bi bi-clipboard2-pulse"></i>
                        </div>
                        <h4 class="fw-bold fs-5">AD-8 篩檢</h4>
                        <p class="text-muted small">只需幾分鐘，評估自身或家人的認知狀態，及早發現及早治療。</p>
                        <a href="ad8_test.php" class="btn btn-outline-primary w-100 mt-2 rounded-pill">前往測驗</a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="display-4 text-primary mb-3">
                            <i class="bi bi-hospital"></i>
                        </div>
                        <h4 class="fw-bold fs-5">權威醫療據點</h4>
                        <p class="text-muted small">整理全台各區失智症診斷與神經內科權威醫師，方便您就近尋求協助。</p>
                        <a href="doctors.php" class="btn btn-outline-primary w-100 mt-2 rounded-pill">尋找醫院</a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="display-4 text-primary mb-3">
                            <i class="bi bi-book"></i>
                        </div>
                        <h4 class="fw-bold fs-5">失智症科普</h4>
                        <p class="text-muted small">豐富的衛教文章，帶您了解失智症的成因、預防與日常照護技巧。</p>
                        <a href="articles.php" class="btn btn-outline-primary w-100 mt-2 rounded-pill">閱讀文章</a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="display-4 text-info mb-3">
                            <i class="bi bi-robot"></i>
                        </div>
                        <h4 class="fw-bold fs-5">24H AI 照護顧問</h4>
                        <p class="text-muted small">半夜遇到突發狀況不知所措？隨時向本機端 AI 提問，提供即時的安撫建議。</p>
                        <a href="ai_helper.php" class="btn btn-outline-info w-100 mt-2 text-dark fw-bold rounded-pill">立即諮詢 AI</a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center p-4">
                        <div class="display-4 text-primary mb-3">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <h4 class="fw-bold fs-5">活動報名與相簿</h4>
                        <p class="text-muted small">參加實體講座與家屬支持團體，並在這裡重溫活動的溫馨剪影。</p>
                        <a href="events.php" class="btn btn-outline-primary w-100 mt-2 rounded-pill">查看活動</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<footer class="bg-dark text-white text-center py-4 mt-auto">
    <div class="container">
        <p class="mb-0">&copy; 2026 失智症照護網. All Rights Reserved.</p>
        <small class="text-muted">本網站僅提供資訊參考，無法取代專業醫療診斷。</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>