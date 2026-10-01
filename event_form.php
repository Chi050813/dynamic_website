<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['event_id'])) {
    header("Location: events.php?msg=" . urlencode("無效的活動連結"));
    exit;
}

$event_id = (int)$_GET['event_id'];

// 撈取這場活動的資訊來顯示在畫面上
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    header("Location: events.php?msg=" . urlencode("找不到該活動"));
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>填寫報名資料 - <?php echo htmlspecialchars($event['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <a href="events.php" class="text-decoration-none text-secondary mb-3 d-inline-block">&larr; 返回活動列表</a>
            
            <div class="card shadow">
                <div class="card-header bg-primary text-white p-4">
                    <h4 class="mb-1">填寫報名資料</h4>
                    <small>您正在報名：<strong><?php echo htmlspecialchars($event['title']); ?></strong></small>
                </div>
                <div class="card-body p-4">
                    
                    <form action="event_join.php" method="POST">
                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">報名人姓名</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" disabled>
                            <div class="form-text">系統已帶入您的會員名稱。</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="gender" class="form-label fw-bold">性別 <span class="text-danger">*</span></label>
                                <select class="form-select" name="gender" id="gender" required>
                                    <option value="" selected disabled>請選擇</option>
                                    <option value="男">男</option>
                                    <option value="女">女</option>
                                    <option value="其他">其他</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="attendees_count" class="form-label fw-bold">參加人數 <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="attendees_count" id="attendees_count" min="1" max="5" value="1" required>
                                <div class="form-text">包含您自己，最多填寫 5 人</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label fw-bold">聯絡電話 <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phone" id="phone" placeholder="例如：0912-345-678" required>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label fw-bold">備註留言</label>
                            <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="如有特殊需求（如：輪椅座位、飲食禁忌），請在此說明..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">確認送出報名</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>