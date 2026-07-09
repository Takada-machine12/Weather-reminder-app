<?php
//エラーメッセージ表示処理

use PhpXmlRpc\Helper\Charset;

ini_set('display_errors', 1);
error_reporting(E_ALL);
//ファイルをインポート
require_once('config.php');

//データベース接続
function connectDb() {
    //DB接続(PDO方式)
    //パラメータ設定
    $param = 'mysql:dbname='.DB_NAME.';host='.HOST.';charset=utf8mb4';
    try {
        return new PDO($param, USER, PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //例外をキャッチしやすくするため
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //データ取得時、綺麗な配列で返してくれる
            PDO::ATTR_EMULATE_PREPARES => false, //SQLインジェクション対策(勝手にPHP側で処理をさせないため、文字コードを利用した攻撃を防ぐため)
        ]);
    } catch (PDOException $e) {
        error_log($e -> getMessage());
        http_response_code(500);
        exit('システムエラーが発生しました。');
    }
}
//メールアドレス存在チェック
function checkEmail($user_email, PDO $pdo):bool {
    $sql = 'select * from users where user_email = :user_email limit 1';
    $stmt = $pdo->prepare($sql);

    //データを設定しSQLを実行
    $stmt->execute(array(":user_email"=>$user_email));

    //結果を取得し、変数に格納
    $user = $stmt->fetch();

    return $user ? true : false;
}
//メールアドレスとパスワードからuserを検索する
function getUser($user_email, $pdo) {
    $sql = 'select * from users where user_email = :user_email limit 1';
    $stmt = $pdo->prepare($sql);

    //実値設定とSQL実行
    $stmt->execute(array(':user_email'=>$user_email));

    //結果取得と変数格納
    $user = $stmt->fetch();

    return $user ? $user : false;
}

//管理者アカウント名とパスワードからuserを検索する
function getAdmin($admin_account, $admin_password, $pdo) {
    $sql = 'select * from admin_info where admin_account = :admin_account and binary admin_password = :admin_password limit 1';
    $stmt = $pdo->prepare($sql);

    //実値設定とSQL実行
    $stmt->execute(array(":admin_account"=>$admin_account,":admin_password"=>$admin_password));

    //結果取得と変数格納
    $admin_user = $stmt->fetch();

    return $admin_user ? $admin_user : false;
}

//通知時間の設定
function arrayToSelect($inputName,$srcArray,$selectedIndex="") {
    $temphtml = '<select class="form-control" name="'.$inputName.'">'.PHP_EOL;

    foreach($srcArray as $key=>$val) {
        if ($selectedIndex == $key) {
            $selectedText = 'selected="selected"';
        } else {
            $selectedText = '';
        }
        $temphtml .= '<option value="'.$key.'"'.$selectedText.'>'.$val.'</option>'.PHP_EOL;
    }

    $temphtml .= '</select>'.PHP_EOL;
    return $temphtml;
}

//XSS(クロスサイトスクリプティング)対策
function xss($original_str) {
    return htmlspecialchars($original_str,ENT_QUOTES,"UTF-8");
}

//暗号学的に安全なランダムトークンを生成
function generateToken($bytes = 32) {
    return bin2hex(random_bytes($bytes));
}

//CSRF対策(トークン生成)
function setToken() {
    //Sessionに生成したトークンを保存
    $_SESSION['sstoken'] = generateToken(32);
}

//CSRF対策(生成したトークンをチェック)
function checkToken() {
    $session_token = $_SESSION['sstoken'] ?? '';
    $posted_token = $_POST['token'] ?? '';

    if ($session_token === '' || !hash_equals($session_token,$posted_token)) {
        http_response_code(403);
        echo '不正なアクセスです。';
        exit;
    }
}

//user_idからユーザを検索
function getUserbyUserId($user_id,$pdo) {
    $sql = 'select * from users where id = :user_id limit 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":user_id"=>$user_id));
    $user = $stmt->fetch();

    return $user ? $user : false;
}

//ランダム文字列生成 (英数字)
function makeRandStr($length) {
    $str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
    $r_str = null;
    for ($i = 0; $i < $length; $i++) {
        $r_str .= $str[rand(0, count($str) - 1)];
    }
    return $r_str;
}

//オートログインデリート
function delete_auto_login($c_key) {
    //DBから削除
    //受け取った生のCookieをハッシュ化
    $token_hash = hash('sha256',$c_key);
    $pdo = connectDb();
    $sql = 'delete from auto_login where c_key = :c_key';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':c_key'=>$token_hash));
    unset($pdo);

    //Cookieを削除
    setcookie('WEATHER', '', time()-86400, COOKIE_PATH);
}
?>