<?php
session_start();
require_once 'db.php';

// 權限防護牆
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("<h2 style='color:red; text-align:center; margin-top:50px;'>錯誤：您沒有管理員權限！請勿擅闖。</h2>");
}

$message = '';
$msg_type = 'success';

// 處理表單送出的邏輯
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ==========================================
    // 動作 1：新增活動
    // ==========================================
    if (isset($_POST['action']) && $_POST['action'] === 'add_event') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $event_date = str_replace('T', ' ', $_POST['event_date']) . ':00';
        $capacity = (int)$_POST['capacity'];

        if (!empty($title) && !empty($event_date) && $capacity > 0) {
            $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, capacity) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$title, $description, $event_date, $capacity])) {
                $message = "🎉 活動「{$title}」新增成功！";
            } else {
                $message = "活動新增失敗，請檢查資料格式。";
                $msg_type = 'danger';
            }
        } else {
            $message = "請填寫所有活動必填欄位！";
            $msg_type = 'warning';
        }
    }

    // ==========================================
    // 動作 2：上傳照片
    // ==========================================
    if (isset($_POST['action']) && $_POST['action'] === 'add_photo') {
        $event_id = (int)$_POST['event_id'];

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/photos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_tmp = $_FILES['photo']['tmp_name'];
            $file_name = time() . '_' . basename($_FILES['photo']['name']);
            $target_path = $upload_dir . $file_name;

            if (move_uploaded_file($file_tmp, $target_path)) {
                $stmt = $pdo->prepare("INSERT INTO photos (event_id, file_path) VALUES (?, ?)");
                if ($stmt->execute([$event_id, $target_path])) {
                    $message = "📸 照片上傳成功！已加入相簿牆。";
                } else {
                    $message = "照片上傳成功，但寫入資料庫失敗。";
                    $msg_type = 'danger';
                }
            } else {
                $message = "檔案移動失敗！請檢查資料夾權限。";
                $msg_type = 'danger';
            }
        } else {
            $message = "請選擇照片檔案，或檔案格式不符。";
            $msg_type = 'warning';
        }
    }

    // ==========================================
    // 動作 3：刪除活動
    // ==========================================
    if (isset($_POST['action']) && $_POST['action'] === 'delete_event') {
        $event_id = (int)$_POST['event_id'];
        
        try {
            // 1. 找出該活動的所有照片並刪除實體檔案
            $stmt_photos = $pdo->prepare("SELECT file_path FROM photos WHERE event_id = ?");
            $stmt_photos->execute([$event_id]);
            $photos = $stmt_photos->fetchAll();
            foreach ($photos as $photo) {
                // 確保只刪除本機的檔案，不刪除外部網址(http開頭)的測試圖
                if (strpos($photo['file_path'], 'http') === false && file_exists($photo['file_path'])) {
                    unlink($photo['file_path']);
                }
            }
            
            // 2. 刪除資料庫中的關聯資料 (照片紀錄與報名紀錄)
            $pdo->prepare("DELETE FROM photos WHERE event_id = ?")->execute([$event_id]);
            $pdo->prepare("DELETE FROM registrations WHERE event_id = ?")->execute([$event_id]);
            
            // 3. 最後刪除活動本身
            $stmt_del_event = $pdo->prepare("DELETE FROM events WHERE id = ?");
            if ($stmt_del_event->execute([$event_id])) {
                $message = "🗑️ 活動及相關的所有報名、照片已成功刪除！";
            }
        } catch (Exception $e) {
            $message = "刪除失敗：系統發生錯誤。";
            $msg_type = 'danger';
        }
    }

    // ==========================================
    // 動作 4：刪除照片
    // ==========================================
    if (isset($_POST['action']) && $_POST['action'] === 'delete_photo') {
        $photo_id = (int)$_POST['photo_id'];
        
        // 先找出檔案路徑
        $stmt_find = $pdo->prepare("SELECT file_path FROM photos WHERE id = ?");
        $stmt_find->execute([$photo_id]);
        $photo_to_delete = $stmt_find->fetch();
        
        if ($photo_to_delete) {
            // 刪除實體檔案
            if (strpos($photo_to_delete['file_path'], 'http') === false && file_exists($photo_to_delete['file_path'])) {
                unlink($photo_to_delete['file_path']);
            }
            // 刪除資料庫紀錄
            $stmt_del_photo = $pdo->prepare("DELETE FROM photos WHERE id = ?");
            if ($stmt_del_photo->execute([$photo_id])) {
                $message = "🗑️ 照片已成功刪除！";
            }
        }
    }
}

// 撈取最新資料供畫面顯示
$events_list = $pdo->query("SELECT * FROM events ORDER BY event_date DESC")->fetchAll();
// 撈取照片並關聯活動名稱
$photos_list = $pdo->query("SELECT p.*, e.title as event_title FROM photos p LEFT JOIN events e ON p.event_id = e.id ORDER BY p.uploaded_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>管理員後台 - 失智症照護網</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; }
        .table-responsive { max-height: 400px; overflow-y: auto; }
        .photo-thumb { width: 80px; height: 60px; object-fit: cover; border-radius: 5px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
    <div class="container">
        <a class="navbar-brand fw-bold text-warning" href="admin_dashboard.php">
            <i class="bi bi-gear-fill"></i> 後台管理中心
        </a>
        <div class="d-flex align-items-center">
            <a href="index.php" class="btn btn-sm btn-outline-light me-3">← 返回網站前台</a>
            <span class="text-white me-3">管理員：<?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="logout.php" class="btn btn-sm btn-danger">登出</a>
        </div>
    </div>
</nav>

<div class="container mt-4 mb-5">
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show shadow-sm" role="alert">
            <strong>系統提示：</strong> <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 border-primary border-top border-3">
                <div class="card-header bg-white fw-bold fs-5 text-primary">
                    <i class="bi bi-calendar-plus"></i> 發布新活動
                </div>
                <div class="card-body">
                    <form action="admin_dashboard.php" method="POST">
                        <input type="hidden" name="action" value="add_event">
                        <div class="mb-3">
                            <label class="form-label fw-bold">活動標題 <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="例如：家屬紓壓工作坊" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">活動說明 (支援多行)</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="簡單介紹活動內容..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">舉辦時間 <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="event_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">報名人數上限 <span class="text-danger">*</span></label>
                                <input type="number" name="capacity" class="form-control" min="1" value="20" required>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> 確認發布活動</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100 border-success border-top border-3">
                <div class="card-header bg-white fw-bold fs-5 text-success">
                    <i class="bi bi-image"></i> 上傳活動照片
                </div>
                <div class="card-body">
                    <form action="admin_dashboard.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_photo">
                        <div class="mb-4">
                            <label class="form-label fw-bold">1. 選擇要綁定的活動 <span class="text-danger">*</span></label>
                            <select name="event_id" class="form-select" required>
                                <option value="" selected disabled>請選擇活動...</option>
                                <?php foreach ($events_list as $e): ?>
                                    <option value="<?php echo $e['id']; ?>">
                                        <?php echo htmlspecialchars($e['title']) . " (" . date('Y/m/d', strtotime($e['event_date'])) . ")"; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">照片會出現在相簿牆，並標示屬於此活動。</div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">2. 選擇照片檔案 <span class="text-danger">*</span></label>
                            <input type="file" name="photo" class="form-control" accept="image/jpeg, image/png, image/gif" required>
                            <div class="form-text">支援 JPG, PNG, GIF 格式。</div>
                        </div>
                        <div class="text-end mt-5">
                            <button type="submit" class="btn btn-success"><i class="bi bi-cloud-upload"></i> 開始上傳照片</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 border-danger border-top border-3">
                <div class="card-header bg-white fw-bold fs-5 text-danger">
                    <i class="bi bi-calendar-x"></i> 管理現有活動
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>活動名稱</th>
                                    <th>舉辦日期</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events_list as $event): ?>
                                <tr>
                                    <td class="text-start"><?php echo htmlspecialchars($event['title']); ?></td>
                                    <td class="text-muted small"><?php echo date('Y/m/d', strtotime($event['event_date'])); ?></td>
                                    <td>
                                        <form action="admin_dashboard.php" method="POST" onsubmit="return confirm('警告：刪除活動將會同時刪除該活動的所有「報名名單」與「相簿照片」，確定要刪除嗎？');">
                                            <input type="hidden" name="action" value="delete_event">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="刪除">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100 border-danger border-top border-3">
                <div class="card-header bg-white fw-bold fs-5 text-danger">
                    <i class="bi bi-images"></i> 管理相簿照片
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>照片縮圖</th>
                                    <th>所屬活動</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($photos_list as $photo): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" class="photo-thumb" alt="照片">
                                    </td>
                                    <td class="text-muted small"><?php echo htmlspecialchars($photo['event_title'] ?? '無關聯活動'); ?></td>
                                    <td>
                                        <form action="admin_dashboard.php" method="POST" onsubmit="return confirm('確定要從相簿牆永久刪除這張照片嗎？');">
                                            <input type="hidden" name="action" value="delete_photo">
                                            <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="刪除">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
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

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>