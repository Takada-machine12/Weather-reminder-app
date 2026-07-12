<?php
//エラーメッセージ表示処理
ini_set('display_errors', 1);
error_reporting(E_ALL);
//ファイルをインポート
require_once('config.php');
require_once('functions.php');
//Session宣言
session_start();

//ログインチェック機能
if (!isset($_SESSION['USER'])) {
    header('Location:'.SITE_URL.'/index.php');
    exit;
}

$user = $_SESSION['USER'];

$id = (int)($_POST['id'] ?? 0);

if ($user['id'] !== $id) {
    echo '<html><head><meta charset="utf-8"></head><body>不正なアクセスです。</body></html>';
    exit;
}

//DB接続
$pdo = connectDb();

try {
    //トランザクション
    $pdo->beginTransaction();
    //SQL
    //usersTBLから削除
    $sql1 = 'delete from users where id = :id';
    $stmt = $pdo->prepare($sql1);
    $stmt->execute(array(':id'=>$id));

    //weather_settingTBLから削除
    $sql2 = 'delete from weather_setting where user_id = :id';
    $stmt = $pdo->prepare($sql2);
    $stmt->execute(array(':id'=>$id));

    //cron_logTBLから削除
    $sql3 = 'delete from cron_log where user_id = :id';
    $stmt = $pdo->prepare($sql3);
    $stmt->execute(array(':id'=>$id));

    //全ての処理が問題なければ実行
    $pdo->commit();

    $complete_message = 'ユーザー情報を削除しました。';

    unset($pdo);
    $_SESSION = [];

    //Cookie無効化
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-86400, COOKIE_PATH);
    }
    //Session破棄
    session_destroy();
    header('Location:'.SITE_URL.'/user_delete_complete.php');
    exit;
} catch (Exception $e) {
    //エラーの場合は処理を取り消す
    $pdo->rollBack();

    error_log($e->getMessage());
    exit('システムエラーが発生しました。');
}
?>