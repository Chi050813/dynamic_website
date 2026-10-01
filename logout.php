<?php
// 啟動 Session
session_start();
// 清除所有 Session 變數
session_unset();
// 銷毀 Session
session_destroy();
// 將使用者導回登入頁
header("Location: login.php");
exit;
?>