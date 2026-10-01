<?php
session_start();
require_once 'db.php';

// 必須登入才能看歷史紀錄
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. 撈取給「圖表」用的資料 (時間必須由舊到新，線圖才符合直覺)
$stmt_chart = $pdo->prepare("SELECT score, test_date FROM ad8_results WHERE user_id = ? ORDER BY test_date ASC");
$stmt_chart->execute([$user_id]);
$chart_data = $stmt_chart->fetchAll();

$chart_labels = [];
$chart_scores = [];
foreach ($chart_data as $row) {
    // 將時間格式化為 年-月-日
    $chart_labels[] = date('Y-m-d', strtotime($row['test_date']));
    $chart_scores[] = $row['score'];
}

// 2. 撈取給「表格」用的資料 (時間由新到舊，方便先看最近的結果)
$stmt_table = $pdo->prepare("SELECT score, test_date FROM ad8_results WHERE user_id = ? ORDER BY test_date DESC");
$stmt_table->execute([$user_id]);
$table_data = $stmt_table->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>AD-8 測驗歷史追蹤 - 失智症照護網</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li class="nav-item"><a class="nav-link" href="events.php">活動與照片</a></li>
            </ul>
            <div class="d-flex text-white align-items-center">
                <span>哈囉，<?php echo htmlspecialchars($_SESSION['user_name']); ?>！</span>
                <a href="logout.php" class="btn btn-sm btn-outline-light ms-3">登出</a>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-3">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-graph-up-arrow text-primary"></i> 個人測驗歷史追蹤</h2>
            <p class="text-muted mb-0">持續記錄 AD-8 篩檢分數，掌握您與家人的認知健康狀態</p>
        </div>
        <div>
            <a href="ad8_test.php" class="btn btn-primary shadow-sm">
                <i class="bi bi-pencil-square"></i> 進行新測驗
            </a>
        </div>
    </div>

    <?php if (empty($table_data)): ?>
        <div class="alert alert-warning text-center fs-5 py-5">
            您目前還沒有任何測驗紀錄喔！請點擊右上方按鈕進行第一次測驗。
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-bold py-3">
                        <i class="bi bi-bar-chart-line-fill text-success"></i> 分數變化趨勢圖
                    </div>
                    <div class="card-body">
                        <canvas id="ad8Chart" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-bold py-3">
                        <i class="bi bi-list-columns-reverse text-info"></i> 測驗明細列表
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover table-striped mb-0 text-center align-middle">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th>測驗日期</th>
                                        <th>總分</th>
                                        <th>狀態</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($table_data as $row): 
                                        $score = $row['score'];
                                        $is_danger = $score >= 2;
                                    ?>
                                    <tr>
                                        <td class="text-muted"><?php echo date('Y/m/d', strtotime($row['test_date'])); ?></td>
                                        <td class="fw-bold fs-5 <?php echo $is_danger ? 'text-danger' : 'text-success'; ?>">
                                            <?php echo $score; ?>
                                        </td>
                                        <td>
                                            <?php if ($is_danger): ?>
                                                <span class="badge bg-danger">建議就醫</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">狀況良好</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // 將 PHP 準備好的陣列轉成 JavaScript 的陣列
    const labels = <?php echo json_encode($chart_labels); ?>;
    const dataPoints = <?php echo json_encode($chart_scores); ?>;

    // 如果有資料才畫圖
    if (labels.length > 0) {
        const ctx = document.getElementById('ad8Chart').getContext('2d');
        const ad8Chart = new Chart(ctx, {
            type: 'line', // 設定為折線圖
            data: {
                labels: labels, // X軸：時間
                datasets: [{
                    label: 'AD-8 總分',
                    data: dataPoints, // Y軸：分數
                    borderColor: '#0d6efd', // 藍色線條
                    backgroundColor: 'rgba(13, 110, 253, 0.1)', // 線條下方微微的填色
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0d6efd',
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    fill: true,
                    tension: 0.3 // 讓線條帶有一點平滑的弧度
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 8, // AD-8 最高就是 8 分
                        ticks: {
                            stepSize: 1
                        },
                        // 在 Y=2 的地方畫一條紅色警告線 (Chart.js 內建 grid 設定)
                        grid: {
                            color: function(context) {
                                if (context.tick.value === 2) {
                                    return 'rgba(220, 53, 69, 0.8)'; // 紅色警戒線
                                }
                                return 'rgba(0, 0, 0, 0.1)'; // 其他一般的灰色線
                            },
                            lineWidth: function(context) {
                                if (context.tick.value === 2) {
                                    return 2;
                                }
                                return 1;
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                if (context.parsed.y >= 2) {
                                    return '⚠️ 分數大於2分，建議尋求專業醫師評估';
                                }
                                return '✅ 狀況良好';
                            }
                        }
                    }
                }
            }
        });
    }
</script>
</body>
</html>