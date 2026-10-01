<?php
// 啟動 Session
session_start();

// 權限檢查：如果沒有登入 (Session 裡沒有 user_id)，就強制跳轉回登入頁面
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$result_message = '';
$total_score = null;

// AD-8 的 8 個問題清單 (使用陣列管理，方便用迴圈產生 HTML)
$questions = [
    1 => "判斷力上的困難（例如：做決定、財務計畫等）",
    2 => "對活動和嗜好失去興趣",
    3 => "重複相同的問題、故事或陳述",
    4 => "在學習如何使用工具、設備和小器具上有困難（例如：電視、微波爐、遙控器）",
    5 => "忘記正確的月份和年份",
    6 => "處理複雜的財務上有困難（例如：報稅、繳費單、銀行作業）",
    7 => "記住約會的時間有困難",
    8 => "有持續的思考和記憶方面的問題"
];

// 如果使用者送出測驗
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total_score = 0;
    
    // 透過迴圈加總 8 題的分數
    for ($i = 1; $i <= 8; $i++) {
        $q_name = 'q' . $i;
        if (isset($_POST[$q_name])) {
            $total_score += (int)$_POST[$q_name];
        }
    }

    // 將測驗結果寫入資料庫
    $stmt = $pdo->prepare("INSERT INTO ad8_results (user_id, score) VALUES (?, ?)");
    if ($stmt->execute([$_SESSION['user_id'], $total_score])) {
        // 根據分數給予不同的回饋
        if ($total_score >= 2) {
            $result_message = "您的得分為 <strong>{$total_score} 分</strong>。根據 AD-8 量表標準，得分大於或等於 2 分，強烈建議您尋求神經內科或精神科醫師進行進一步的專業評估。";
            $alert_class = "alert-danger"; // Bootstrap 的紅色警告樣式
        } else {
            $result_message = "您的得分為 <strong>{$total_score} 分</strong>。目前看來狀況良好，請繼續保持健康的生活作息，並持續關注自身與家人的認知健康。";
            $alert_class = "alert-success"; // Bootstrap 的綠色安全樣式
        }
    } else {
        $result_message = "系統發生錯誤，無法儲存測驗結果。";
        $alert_class = "alert-warning";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>AD-8 極早期失智症篩檢量表</title>
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

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2 class="text-center mb-4">AD-8 極早期失智症篩檢量表</h2>
            
            <div class="alert alert-info">
                <strong>說明：</strong> 請比較患者「以前」與「現在」的改變。如果變化是因為年紀大而自然產生的，請勾「否」。如果有因為認知功能減退而產生的變化，請勾「是」。
            </div>

            <?php if ($total_score !== null): ?>
                <div class="alert <?php echo $alert_class; ?> fs-5">
                    <?php echo $result_message; ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="ad8_test.php" method="POST">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>評估項目 (與過去相比)</th>
                                    <th class="text-center">是,有改變 (1分)</th>
                                    <th class="text-center">否,沒改變 (0分)</th>
                                    <th class="text-center">不知道 (0分)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($questions as $num => $question): ?>
                                <tr>
                                    <td><?php echo $num . ". " . $question; ?></td>
                                    <td class="text-center">
                                        <input type="radio" name="q<?php echo $num; ?>" value="1" required>
                                    </td>
                                    <td class="text-center">
                                        <input type="radio" name="q<?php echo $num; ?>" value="0">
                                    </td>
                                    <td class="text-center">
                                        <input type="radio" name="q<?php echo $num; ?>" value="0">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php
                                    $file=@file("count.txt");
                                    list($today,$date,$num)=explode(",",$file[0]); 
                                    $tdate=date("y-m-d");
                                    if($date=="$tdate"){
                                    $today++; 
                                    $num++; 
                                    $w=fopen("count.txt","w");
                                    fputs($w,$today.",".$tdate.",".$num); 
                                    fclose($w);
                                    }
                                    else{
                                    $today=1; 
                                    $num++; 
                                    $w=fopen("count.txt","w");
                                    fputs($w,$today.",".$tdate.",".$num); 
                                    fclose($w);
                                        }
                                        echo "<br>今日測驗人數: ".$today."<br>";
                                            echo "累積失智測驗人次: ".$num;
                                    ?>
                            </tbody>
                        </table>
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5">送出評估並計算分數</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>