<?php
//エラーメッセージ表示処理
ini_set('display_errors', 1);
error_reporting(E_ALL);
//ファイルをインポート
require_once('config.php');
require_once('functions.php');

//Session宣言
session_start();

$pdo = connectDb();
$user_email = '';
$error = array();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //CSRF対策
    setToken();
} else {
    //CSRF対策
    checkToken();

    $user_email = $_POST['user_email'];

    //バリデーションチェック
    //メールアドレス
    if ($user_email === '') {
        $error['user_email'] = 'メールアドレスを入力してください。';
    } elseif (!filter_var($user_email,FILTER_VALIDATE_EMAIL)) {
        $error['user_email'] = '形式が正しくありません。正しい形式のメールアドレスを入力してください。';
    } else {
        if (!checkEmail($user_email,$pdo)) {
            $error['user_email'] = 'このメールアドレスは登録されていません。';
        }
    }

    //エラーが無い場合
    if (empty($error)) {
        //ランダム文字列生成
        $str_rand = makeRandStr(8);
        
        //SQL
        $sql_update = 'update users set user_password = :user_password, updated_at = now() where user_email = :user_email';
        $stmt = $pdo->prepare($sql_update);
        $stmt->execute(array(':user_password'=>password_hash($str_rand,PASSWORD_DEFAULT), ':user_email'=>$user_email));
        
        //メール送信準備
        mb_language('japanese');
        mb_internal_encoding('UTF-8');

        $mail_title = '【お天気アプリ】パスワード再設定メール';
        $mail_body = 'パスワードリセットの要求があったため、パスワードを一時的に以下のものに変更しました。'.PHP_EOL;
        $mail_body.= 'パスワード:'.$str_rand.PHP_EOL.PHP_EOL;
        $mail_body.= 'セキュリティ向上のため、ログイン後にご自身でパスワードを変更してください。'.PHP_EOL;
        $mail_body.= SITE_URL;

        //メール送信
        if (mb_send_mail($user_email, $mail_title, $mail_body)) {
            //メール送信完了画面に遷移
            header('Location:'.SITE_URL.'/reminder_complete.php');
            exit;
        } else {
            $error['mail'] = 'メール送信に失敗しました。';
        }
    }
}
unset($pdo);
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8" />
        <title>パスワードリセット画面 | <?php echo SERVICE_NAME; ?></title>
        <meta name="description" content="登録した地域のお天気情報を自動で取得、通知できるシステム。自動投稿システム" />
        <meta name="keywords" content="自動通知" />
        <link href="../css/bootstrap.min.css" rel="stylesheet">
        <script src="//code.jquery.com/jquery.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <link href="../css/auto.css" rel="stylesheet">
    </head>

    <body id="main">
        <div class="nav navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <a class="navbar-brand" href="<?php echo SITE_URL; ?>"><?php echo SERVICE_SHORT_NAME; ?></a>
                </div><!-- container -->
            </div><!-- navbar-inner -->
        </div><!-- navbar-inverse -->

        <div class="container">
            <h1>パスワードをお忘れの方</h1>
            <form method="POST" class="panel panel-default panel-body">
                <div class="form-group <?php echo !empty($error['user_email']) ? 'has-error':''; ?>">
                    <label>メールアドレス</label>
                    <input type="text" name="user_email" class="form-control" value="<?php echo xss($user_email); ?>" />
                    <span class="help-block"><?php echo $error['user_email'] ?? ''; ?></span>
                </div><!-- form-group -->

                <div class="form-group <?php echo !empty($error['mail']) ? 'has-error':''; ?>">
                    <input type="submit" class="btn btn-primary btn-block" value="パスワードをリセットする" />
                    <span class="help-block"><?php echo $error['mail'] ?? ''; ?></span>
                </div><!-- form-group -->
                <!-- トークンをPOSTで送信 -->
                <input type="hidden" name="token" value="<?php echo xss($_SESSION['sstoken']); ?>" />
            </form>
            <a href="./index.php">戻る</a>

            <hr>
            <footer class="footer">
                <p><?php echo COPYRIGHT; ?></p>
            </footer><!-- footer -->
        </div><!-- container -->
    </body>
</html>