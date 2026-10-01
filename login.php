<?php
// 啟動 Session，這是登入系統最重要的第一步！必須放在檔案最上方
session_start();

require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $password = $_POST['password'];

    if (empty($name) || empty($password)) {
        $message = "請輸入名稱與密碼！";
    } else {
        // 從資料庫尋找這個 Name 的使用者
        $stmt = $pdo->prepare("SELECT * FROM users WHERE name = ?");
        $stmt->execute([$name]);
        $user = $stmt->fetch();

        // 驗證密碼：使用 password_verify 比對使用者輸入的密碼與資料庫裡的雜湊值
        if ($user && password_verify($password, $user['password_hash'])) {
            // 登入成功！將使用者資訊存入 Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            
            // 登入成功後，將畫面轉址到首頁或會員中心 (我們先暫定導向 ad8_test.php)
            header("Location: ad8_test.php");
            exit; // 轉址後務必加上 exit 停止執行後續程式碼
        } else {
            $message = "名稱或密碼錯誤！";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>失智症照護網 - 會員登入</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
    <div class="col-md-6">
        
        <div class="mb-3">
            <a href="index.php" class="text-decoration-none text-secondary">
                &larr; 返回首頁
            </a>
        </div>

        <div class="card shadow">
            <div class="card-header bg-success text-white text-center">
                    <h4>會員登入</h4>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">名稱 (Name )</label>
                            <input type="name" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">密碼</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">登入</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="register.php">還沒有帳號？點此註冊</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>