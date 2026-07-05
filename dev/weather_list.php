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
        <title>お天気情報一覧画面 | <?php echo SERVICE_NAME; ?></title>
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
                        <li><a href="./city_register.php">地域登録</a></li>
                        <li class="active"><a href="./weather_list.php">お天気情報一覧</a></li>
                        <li><a href="./register.php">通知時間設定</a></li>
                        <li><a href="./user_edit.php">ユーザー情報設定</a></li>
                        <li><a href="./logout.php">ログアウト</a></li>
                    </ul><!-- ul -->
                </div><!-- container -->
            </div><!-- navbar-inner -->
        </div><!-- navbar-inverse -->

        <div class="container">
            <h1>お天気情報一覧</h1>
           <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" border="1">
                    <tr>
                        <th>地域</th><th>日付</th><th>天気</th><th>最高気温</th><th>最低気温</th><th>降水確率</th><th>風向き</th><th>風速</th><th>予報日</th><th></th>
                    </tr>
                    <//?php foreach($users as $user): ?>
                        <tr>
                            <td><?php echo '例：東京都千代田区'; //xss($user['user_name']); ?></td>
                            <td><?php echo '例：今日'; //xss($user['user_name']); ?></td>
                            <td><?php echo '例：曇り'; //xss($user['user_email']); ?></td>
                            <td><?php echo '例：25℃'; //xss($user['created_at']); ?></td>
                            <td><?php echo '例：17℃'; //xss($user['created_at']); ?></td>
                            <td><?php echo '例：0%'; //xss($user['created_at']); ?></td>
                            <td><?php echo '例：北の風'; //xss($user['created_at']); ?></td>
                            <td><?php echo '例：0.5メートル'; //xss($user['created_at']); ?></td>
                            <td><?php echo '例：今日'; //xss($user['created_at']); ?></td>
                            <td><a href="weather_delete.php?id=<?php echo 'user_id' //$user['id']; ?>" >[削除]</a></td>
                        </tr>
                    <//?php endforeach; ?>
                </table>
            </div>
            <a href="./home.php">戻る</a>

            <hr>
            <footer class="footer">
                <p><?php echo COPYRIGHT; ?></p>
            </footer><!-- footer -->
        </div><!-- container -->
    </body>
</html>