<?php
define('SERVICE_NAME','お天気アプリ自動取得');
define('SERVICE_SHORT_NAME','お天気アプリ');
define('COPYRIGHT','&copy; TKD');

if (file_exists(__DIR__.'/config_local.php')) { // 絶対パス
    // ファイルが存在すれば、開発用ファイルを読み込む
    require_once(__DIR__.'/config_local.php');
} else {
    // ファイルが存在しなkれば、ダミー用変数を設定
    //GitHub用のダミー値設定
    define('SITE_URL','サイトURL');
    define('HOST','ホスト名');
    define('USER','ユーザ名');
    define('PASS','パスワード');
    define('DB_NAME','DB名');
    define('HOST_MAIL','xxxx@gmail.com');
    define('SUBJECT','メッセージ');
    define('SUBJECT_UPDATE', '更新された時のメッセージ');
    define('ADMIN_ID', '管理者ログインID');
    define('ADMIN_PASSWORD', '管理者ログインパスワード');
    define('YOUTUBE_API_KEY','YoutubeのAPIキー');
}
?>