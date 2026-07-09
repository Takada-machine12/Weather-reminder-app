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
if (isset($_SESSION['USER'])) {
    //既にログイン済み
} elseif (!isset($_SESSION['USER']) && !empty($_COOKIE['WEATHER'])) {
    //DB接続
    $pdo = connectDb();
    //変数設定
    $raw_token = $_COOKIE['WEATHER'];
    $token_hash = hash('sha256',$raw_token);

    //トークンとDBを照合(有効期限も確認)
    $sql = 'select * from auto_login where c_key = :c_key and expire > :expire';
    $stmt = $pdo->prepare($sql);
    $stmt->execute((array(
                    ':c_key'=>$token_hash,
                    ':expire'=>date('Y-m-d H:i:s'))));
    $auto_login_data = $stmt->fetch();

    if ($auto_login_data) {
        //ユーザ情報を取得してセッションにセット
        $login_user = getUserbyUserId($auto_login_data['user_id'], $pdo);

        if (!$login_user) {
            delete_auto_login($raw_token);
            header('Location:'.SITE_URL.'/index.php');
            exit;
        }
        session_regenerate_id(true);
        $_SESSION['USER'] = $login_user;

        unset($pdo);
    } else {
        //トークンはあるけどDBとの照合が失敗した時
        delete_auto_login($raw_token);
        
        header('Location:'.SITE_URL.'/index.php');
        exit;
    }
} else {
    //どちらにも当てはまらなければ、index.phpに遷移
    header('Location:'.SITE_URL.'/index.php');
    exit;
}
//セッション情報を取得
$user = $_SESSION['USER'];

setToken();

$pdo = connectDb();

//SQL(お知らせ情報取得)
$sql1 = 'select news_text from admin_info';
$stmt = $pdo->prepare($sql1);
$stmt->execute();
$admin_news = $stmt->fetch(PDO::FETCH_ASSOC);

//SQL(投稿情報取得)
$sql2 = 'select cron_message, created_at from cron_log where user_id = :user_id order by created_at desc limit 10';
$stmt = $pdo->prepare($sql2);
$stmt->execute(array(':user_id'=>$user['id']));
$cron_message = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8" />
        <title>MENUページ | <?php echo SERVICE_NAME; ?></title>
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
                    <ul class="nav navbar-nav">
                        <li>
                            <form action="logout.php" method="POST">
                                <input type="hidden" name="token" value="<?php echo xss($_SESSION['sstoken']); ?>">
                                <input type="submit" value="ログアウト" class="btn btn-link navbar-btn">
                            </form>
                        </li>
                    </ul><!-- ul -->
                </div><!-- container -->
            </div><!-- navbar-inner -->
        </div><!-- navbar-inverse -->

        <div class="container">
            <h1>MENU</h1>
            <p>
                <h4>ようこそ、<?php echo $_SESSION['USER']['user_name']; ?>さん!</h4>
            </p>
            <div class="panel panel-default">
                <div class="panel-heading">
                    管理者からのお知らせ
                </div>
                <div class="panel-body">
                    <?php if (!empty($admin_news['news_text'])):?>
                        <?php echo nl2br(xss($admin_news['news_text'])); ?>
                    <?php else:?>
                        お知らせはありません。
                    <?php endif;?>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    実行ログ
                </div>
                <div class="panel-body">
                    <?php if ($cron_message):?>
                        <?php foreach($cron_message as $log):?>
                            <?php echo xss($log['cron_message']); ?><br >
                            <?php echo xss($log['created_at']); ?><br >
                            <p>-----------------------------------------</p>
                        <?php endforeach; ?>
                    <?php else:?>
                        実行ログはありません。
                    <?php endif;?>
                </div>
            </div>
            
            <div class="list-group">
                <!-- ここに管理者が書いたお知らせとcron処理の実行ログを表示 -->
                <a href="city_register.php" class="list-group-item">
                    <h4 class="list-group-item-heading">住んでいる地域の登録</h4>
                    <p class="list-group-item-text">自分が住んでいる地域を登録</p>
                </a>
                <a href="weather_list.php" class="list-group-item">
                    <h4 class="list-group-item-heading">登録した地域のお天気情報一覧</h4>
                    <p class="list-group-item-text">登録した地域のお天気情報を一覧で表示(今日、明日、明後日)</p>
                </a>
                <a href="register.php" class="list-group-item">
                    <h4 class="list-group-item-heading">メール通知の設定</h4>
                    <p class="list-group-item-text">通知時間を設定(朝、夜)</p>
                </a>
                <a href="user_edit.php" class="list-group-item">
                    <h4 class="list-group-item-heading">ユーザー情報設定</h4>
                    <p class="list-group-item-text">ニックネームの編集などを設定</p>
                </a>
            </div>
            
            <hr>
            <footer class="footer">
                <p><?php echo COPYRIGHT; ?></p>
            </footer><!-- footer -->
        </textarea><!-- container -->
    </body>
</html>