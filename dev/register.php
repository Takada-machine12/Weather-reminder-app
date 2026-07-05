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
        <title>ブログの設定画面 | <?php echo SERVICE_NAME; ?></title>
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
                        <li><a href="./weather_list.php">お天気情報一覧</a></li>
                        <li class="active"><a href="./register.php">通知時間設定</a></li>
                        <li><a href="./user_edit.php">ユーザー情報設定</a></li>
                        <li><a href="./logout.php">ログアウト</a></li>
                    </ul><!-- ul -->
                </div><!-- container -->
            </div><!-- navbar-inner -->
        </div><!-- navbar-inverse -->

        <div class="container">
            <h1>設定</h1>
            <//?php if ($complete_msg): ?>
                <div class="alert alert-success">
                    <?php echo '$complete_msg'; ?>
                </div>
            <//?php endif; ?>
            <div class="alert alert-info">
                何時に投稿するか投稿時間を設定してください。
            </div>
            <form method="POST" class="panel panel-default panel-body" action="register.php">
                <div class="form-group <//?php if(!empty($error['delivery_hour'])) {echo "has-error";} ?>">
                    <label>メール通知</label>
                    <?php echo '選択'; //arrayToSelect("delivery_hour", '$delivery_hours_array', $user['delivery_hour']); ?>
                    <span class="help-block"><?php echo 'エラー表示'; //$error['delivery_hour'] ?? ''; ?></span>
                </div><!-- form-group -->

                <div class="form-group">
                    <input type="submit" class="btn btn-success btn-block" value="登録" />
                </div><!-- form-group -->
                <!-- トークンをPOSTで送信 -->
                <input type="hidden" name="token" value="<?php echo xss($_SESSION['sstoken'] ?? ''); ?>" />
            </form>
            <a href="./home.php">戻る</a>
            <hr>
            <footer class="footer">
                <p><?php echo COPYRIGHT; ?></p>
            </footer><!-- footer -->
        </div><!-- container -->
    </body>
</html>