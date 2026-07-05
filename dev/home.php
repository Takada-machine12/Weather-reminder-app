<?php
//エラーメッセージ表示処理
ini_set('display_errors', 1);
error_reporting(E_ALL);
//ファイルをインポート
require_once('config.php');
require_once('functions.php');
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
                        <li class="active"><a href="./logout.php">ログアウト</a></li>
                    </ul><!-- ul -->
                </div><!-- container -->
            </div><!-- navbar-inner -->
        </div><!-- navbar-inverse -->

        <div class="container">
            <h1>MENU</h1>
            <p>
                <h4>ようこそ、<?php echo 'ユーザ'; //$_SESSION['USER']['user_name']; ?>さん!</h4>
            </p>
            <div class="panel panel-default">
                <div class="panel-heading">
                    管理者からのお知らせ
                </div>
                <div class="panel-body">
                    <?php echo 'お知らせ'; //nl2br(xss($admin_news['news_text'])); ?>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    実行ログ
                </div>
                <div class="panel-body">
                    <//?php foreach($cron_message as $log):?>
                        <?php echo 'メッセージ'; //xss($log['cron_message']); ?><br >
                        <?php echo 'メッセージ'; //xss($log['created_at']); ?><br >
                        <p>-----------------------------------------</p>
                    <//?php endforeach; ?>
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