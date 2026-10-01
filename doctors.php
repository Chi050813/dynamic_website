<?php
session_start();
require_once 'db.php';

// 撈取資料庫中所有的醫院與醫生資訊
$stmt = $pdo->query("SELECT * FROM hospitals");
$all_hospitals = $stmt->fetchAll();

// 建立一個分類陣列，用來把醫院歸類到不同的區域
$regions = [
    '北部地區 (基北北桃竹宜)' => ['台北市', '新北市', '基隆市', '桃園市', '新竹市', '新竹縣', '宜蘭縣'],
    '中部地區 (中彰投苗)' => ['台中市', '彰化縣', '南投縣', '苗栗縣'],
    '南部地區 (雲嘉嘉南高屏)' => ['雲林縣', '嘉義市', '嘉義縣', '台南市', '高雄市', '屏東縣'],
    '東部與離島地區 (花東澎金馬)' => ['花蓮縣', '台東縣', '澎湖縣', '金門縣', '連江縣']
];

// 初始化一個容器，用來存放各區域的醫院資料
$categorized_hospitals = [
    '北部地區 (基北北桃竹宜)' => [],
    '中部地區 (中彰投苗)' => [],
    '南部地區 (雲嘉嘉南高屏)' => [],
    '東部與離島地區 (花東澎金馬)' => []
];

// 開始分類
foreach ($all_hospitals as $hospital) {
    $city = $hospital['city'];
    $classified = false;

    foreach ($regions as $region_name => $cities_in_region) {
        if (in_array($city, $cities_in_region)) {
            $categorized_hospitals[$region_name][] = $hospital;
            $classified = true;
            break;
        }
    }
    
    // 如果都沒對應到，暫時歸類到東部與離島（或可以自訂一個未分類）
    if (!$classified) {
        $categorized_hospitals['東部與離島地區 (花東澎金馬)'][] = $hospital;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>全台失智症權威醫生與醫院據點</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #1976d2;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">失智症照護網</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">首頁</a></li>
                <li class="nav-item"><a class="nav-link" href="ad8_test.php">AD-8測驗</a></li>
                <li class="nav-item"><a class="nav-link" href="doctors.php">醫療據點</a></li>
                <li class="nav-item"><a class="nav-link" href="articles.php">科普文章</a></li>
                <li class="nav-item"><a class="nav-link" href="events.php">活動與照片</a></li>
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
    <div class="text-center mb-5">
        <h2>全台失智症權威醫生與醫療據點</h2>
        <p class="text-muted">我們為您整理了全台在失智症、神經內科及老年精神醫學領域的權威醫師與看診醫院</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="accordion" id="hospitalAccordion">
                
                <?php 
                $index = 0;
                foreach ($categorized_hospitals as $region_title => $hospitals): 
                    $index++;
                    $collapse_id = "collapse" . $index;
                    $heading_id = "heading" . $index;
                ?>
                <div class="accordion-item shadow-sm mb-2">
                    <h2 class="accordion-header" id="<?php echo $heading_id; ?>">
                        <button class="accordion-button <?php echo $index === 1 ? '' : 'collapsed'; ?> fs-5 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapse_id; ?>" aria-expanded="<?php echo $index === 1 ? 'true' : 'false'; ?>" aria-controls="<?php echo $collapse_id; ?>">
                            <?php echo $region_title; ?> 
                            <span class="badge bg-secondary ms-2"><?php echo count($hospitals); ?> 間據點</span>
                        </button>
                    </h2>
                    
                    <div id="<?php echo $collapse_id; ?>" class="accordion-collapse collapse <?php echo $index === 1 ? 'show' : ''; ?>" aria-labelledby="<?php echo $heading_id; ?>" data-bs-parent="#hospitalAccordion">
                        <div class="accordion-body">
                            
                            <?php if (empty($hospitals)): ?>
                                <p class="text-muted text-center my-3">目前該地區尚無登錄的醫生資訊。</p>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach ($hospitals as $h): ?>
                                    <div class="col-md-6">
                                        <div class="card h-100 border-start border-primary border-3">
                                            <div class="card-body">
                                                <h5 class="card-title text-primary fw-bold"><?php echo htmlspecialchars($h['hospital_name']); ?></h5>
                                                <p class="card-text mb-1">
                                                    <strong>推薦醫師：</strong> 
                                                    <span class="badge bg-info text-dark fs-6"><?php echo htmlspecialchars($h['doctor_name']); ?></span>
                                                </p>
                                                <p class="card-text mb-2 text-muted">
                                                    <strong>醫院地址：</strong> <?php echo htmlspecialchars($h['address']); ?>
                                                </p>
                                                <a href="<?php echo htmlspecialchars($h['map_url']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                    📍 在 Google 地圖上查看
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
            </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>