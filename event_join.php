<?php
session_start();
require_once 'db.php';

// 檢查是否為 POST 請求 (確保是從表單送出的)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: events.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 接收表單資料
$user_id = $_SESSION['user_id'];
$event_id = (int)$_POST['event_id'];
$gender = trim($_POST['gender']);
$attendees_count = (int)$_POST['attendees_count'];
$phone = trim($_POST['phone']);
$notes = trim($_POST['notes']);

// 基礎防呆檢查
if (empty($gender) || empty($phone) || $attendees_count < 1) {
    header("Location: events.php?msg=" . urlencode("請完整填寫報名必填欄位！"));
    exit;
}

try {
    // 檢查是否重複報名
    $check_stmt = $pdo->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ? AND status = 'registered'");
    $check_stmt->execute([$user_id, $event_id]);
    
    if ($check_stmt->fetch()) {
        header("Location: events.php?msg=" . urlencode("您已經報名過此場活動囉！"));
        exit;
    }

    // 檢查剩餘名額 (總上限 - 目前所有已報名的人數總和)
    $event_sql = "SELECT capacity, 
                         (SELECT COALESCE(SUM(attendees_count), 0) FROM registrations WHERE event_id = ? AND status = 'registered') AS joined 
                  FROM events WHERE id = ?";
    $event_stmt = $pdo->prepare($event_sql);
    $event_stmt->execute([$event_id, $event_id]);
    $event_data = $event_stmt->fetch();

    if (!$event_data) {
        header("Location: events.php?msg=" . urlencode("該活動不存在。"));
        exit;
    }
    // 【新增】檢查活動是否已經過期
    // 因為我們 SQL 已經撈出了 events 所有的資料 (包含 event_date)，我們可以直接拿來比對
    // 註：請確保上方 $event_sql 查詢有加上 event_date，例如 "SELECT capacity, event_date, ..."

    // 檢查這次報名的人數，會不會讓總人數超過上限
    if (($event_data['joined'] + $attendees_count) > $event_data['capacity']) {
        $left = $event_data['capacity'] - $event_data['joined'];
        header("Location: events.php?msg=" . urlencode("很抱歉，名額不足！目前僅剩 {$left} 個名額。"));
        exit;
    }

    // 【修改：檢查活動報名是否已過期 (提早一天截止)】
    $deadline_timestamp = strtotime($event_data['event_date'] . ' -1 day');
    if ($deadline_timestamp < time()) {
        header("Location: events.php?msg=" . urlencode("很抱歉，該活動已經超過報名期限！"));
        exit;
    }

    // 將包含表單細節的資料寫入資料庫
    $insert_sql = "INSERT INTO registrations (user_id, event_id, gender, attendees_count, phone, notes) VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $pdo->prepare($insert_sql);
    
    if ($insert_stmt->execute([$user_id, $event_id, $gender, $attendees_count, $phone, $notes])) {
        header("Location: events.php?msg=" . urlencode("報名成功！期待當天與您相見。"));
    } else {
        header("Location: events.php?msg=" . urlencode("報名失敗，請稍後再試。"));
    }
    exit;

} catch (\PDOException $e) {
    header("Location: events.php?msg=" . urlencode("系統錯誤，無法處理報名。"));
    exit;
}