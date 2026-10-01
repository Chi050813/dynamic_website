<?php
// 引入剛剛寫好的資料庫連線設定
require_once 'db.php';

$message = ''; // 用來儲存要顯示給使用者看的提示訊息

// 檢查使用者是否有按下「註冊」按鈕 (是否有 POST 資料過來)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 簡單的後端驗證，確保欄位不是空的
    if (empty($name) || empty($email) || empty($password)) {
        $message = "所有欄位皆為必填！";
    } else {
        // 1. 檢查這個 Email 是否已經被註冊過了
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $message = "這個 Email 已經被註冊過了！";
        } else {
            // 2. 密碼加密 (資安重點！絕對不能明碼存入資料庫)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // 3. 寫入資料庫
            $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
            
            if ($insert_stmt->execute([$name, $email, $password_hash])) {
                $message = "註冊成功！可以前去登入。";
            } else {
                $message = "註冊失敗，請稍後再試。";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>失智症照護網 - 會員註冊</title>
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
                    <h4>會員註冊</h4>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <form action="register.php" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">姓名/暱稱</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">電子郵件 (Email)</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">密碼</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">確認註冊</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="login.php">已經有帳號了？點此登入</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>