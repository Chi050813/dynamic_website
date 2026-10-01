<?php
session_start();
require_once 'db.php';

// 撈取活動資訊與報名人數
$sql = "SELECT e.*, 
               (SELECT COALESCE(SUM(r.attendees_count), 0) FROM registrations r WHERE r.event_id = e.id AND r.status = 'registered') AS current_joined 
        FROM events e 
        ORDER BY e.event_date ASC";

$stmt = $pdo->query($sql);
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>活動報名專區 - 失智症照護網</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
    <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-3">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-calendar-event text-primary"></i> 關懷活動與講座報名</h2>
            <p class="text-muted mb-0">參加實體活動，獲取專業照護支援，點燃記憶的溫度</p>
        </div>
        <div>
            <a href="photos.php" class="btn btn-outline-warning text-dark fw-bold">
                <i class="bi bi-images"></i> 觀看活動相簿
            </a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-info alert-dismissible fade show text-center shadow-sm" role="alert">
            <strong>系統提示：</strong> <?php echo htmlspecialchars($_GET['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <?php foreach ($events as $event): 
                $slots_left = $event['capacity'] - $event['current_joined'];
                
                // 【修改核心】計算提早一天的時間戳記
                $deadline_timestamp = strtotime($event['event_date'] . ' -1 day');
                
                // 判斷是否過期 (跟提早一天的時間比對)
                $is_expired = $deadline_timestamp < time();
                
                // 準備給畫面與 JavaScript 用的時間格式
                $display_deadline = date('Y-m-d H:i:s', $deadline_timestamp);
                $js_date = date('Y/m/d H:i:s', $deadline_timestamp);
            ?>
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($event['title']); ?></h4>
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($event['description']); ?></p>
                            
                            <div class="d-flex flex-wrap text-secondary small mb-3">
                                <span class="me-4"><i class="bi bi-clock-fill text-primary"></i> 舉辦時間：<?php echo $event['event_date']; ?></span>
                                <span class="me-4"><i class="bi bi-people-fill text-success"></i> 限額：<?php echo $event['capacity']; ?> 人</span>
                                <span><i class="bi bi-exclamation-circle-fill text-warning"></i> 剩餘名額：<strong><?php echo $slots_left; ?></strong> 名</span>
                            </div>

                            <div class="bg-danger bg-opacity-10 p-2 rounded border border-danger border-opacity-25 d-inline-block">
                                <div class="text-dark small mb-1">
                                    <i class="bi bi-calendar-x text-danger"></i> 報名終止時間：<strong><?php echo $display_deadline; ?></strong>
                                </div>
                                <div class="text-danger fw-bold small">
                                    <i class="bi bi-hourglass-split"></i> 
                                    <?php if ($is_expired): ?>
                                        <span class="text-muted">報名已截止</span>
                                    <?php else: ?>
                                        <span class="countdown-timer" data-deadline="<?php echo $js_date; ?>" id="timer-<?php echo $event['id']; ?>">計算中...</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                        
                        <div class="col-md-4 text-md-end mt-4 mt-md-0">
                            <?php if ($is_expired): ?>
                                <button class="btn btn-secondary btn-lg w-100 disabled">報名已截止</button>
                            <?php elseif ($slots_left <= 0): ?>
                                <button class="btn btn-secondary btn-lg w-100 disabled">名額已滿</button>
                            <?php else: ?>
                                <a href="event_form.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary btn-lg w-100 rounded-pill" id="btn-<?php echo $event['id']; ?>">
                                    <i class="bi bi-pencil-square"></i> 立即線上報名
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const timers = document.querySelectorAll('.countdown-timer');

    timers.forEach(timer => {
        const deadline = new Date(timer.getAttribute('data-deadline')).getTime();
        const eventId = timer.id.split('-')[1]; 
        const btn = document.getElementById('btn-' + eventId);

        const interval = setInterval(function() {
            const now = new Date().getTime();
            const distance = deadline - now;

            if (distance < 0) {
                clearInterval(interval);
                timer.innerHTML = "報名已截止";
                timer.classList.replace('text-danger', 'text-muted');
                
                if (btn) {
                    btn.removeAttribute('href');
                    btn.className = 'btn btn-secondary btn-lg w-100 disabled';
                    btn.innerHTML = '報名已截止';
                }
            } else {
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                timer.innerHTML = `報名剩餘時間：${days} 天 ${hours} 小時 ${minutes} 分 ${seconds} 秒`;
            }
        }, 1000);
    });
</script>
</body>
</html>