<?php
// 資料庫連線設定
$host = '127.0.0.1';             // 資料庫主機位置 (本機通常是 localhost 或 127.0.0.1)
$db   = 'dementia_care';         // 我們之前命名的資料庫名稱
$user = 'root';                  // 資料庫帳號 (XAMPP 預設通常是 root)
$pass = '';                      // 資料庫密碼 (XAMPP 預設通常是空字串，MAMP 可能是 root)
$charset = 'utf8mb4';            // 編碼設定，支援繁體中文與 Emoji

// 建立資料來源名稱 (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// 設定 PDO 的進階選項
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // 讓錯誤以「異常(Exception)」方式拋出，方便除錯
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // 預設以「關聯陣列(Key-Value)」格式取回資料
    PDO::ATTR_EMULATE_PREPARES   => false,                  // 停用模擬預處理，強制使用真正的預處理，防範 SQL 注入攻擊
];

try {
    // 嘗試建立連線
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // 如果連線失敗，停止執行並顯示錯誤訊息 (正式上線時這行要隱藏，避免暴露主機資訊)
    die("資料庫連線失敗，錯誤訊息: " . $e->getMessage());
}
?>