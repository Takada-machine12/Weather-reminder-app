<?php
//ファイルをインポート
require_once('config.php');
require_once('functions.php');
//Session宣言
session_start();

//自動ログイン情報クリア
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

//CSRF対策
checkToken();

if (!empty($_COOKIE['WEATHER'])) {
    delete_auto_login($_COOKIE['WEATHER']);
}

//セッション削除
$_SESSION = [];

//Cookie無効化
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-86400, COOKIE_PATH);
}
//Session破棄
session_destroy();

header('Location:'.SITE_URL.'/index.php');
?>