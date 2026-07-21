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
$city_id = (int)$_POST['city_id'];

//DB接続
$pdo = connectDb();
//SQL
$sql_delete = 'delete from weather_setting where user_id = :user_id and city_id = :city_id';
$stmt = $pdo->prepare($sql_delete);
$stmt->execute(array(':user_id'=>$user['id'],':city_id'=>$city_id));

header('Location:'.SITE_URL.'/city_register.php');
exit;
?>