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
        <title>お知らせ登録ページ | <?php echo SERVICE_NAME; ?></title>
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
                        <li><a href="./admin_user_news.php">お知らせ登録</a></li>
                        <li class="active"><a href="./admin_user_list.php">ユーザー登録一覧</a></li>
                        <li><a href="./admin_logout.php">ログアウト</a></li>
                    </ul><!-- ul -->
                </div><!-- container -->
            </div><!-- navbar-inner -->
        </div><!-- navbar-inverse -->

        <div class="container">
            <h1>ユーザー情報編集</h1>
            <//?php if($complete_msg): ?>
                <div class="alert alert-success">
                    <?php echo '$complete_msg'; ?>
                </div>
            <//?php endif; ?>

            <form method="POST" class="panel panel-default panel-body">
                <input type="hidden" name="id" value="<?php echo '$id' ?>" />
                <div class="form-group">
                    <input type="text" name="user_name" class="form-control" value="<?php echo xss('$user_name'); ?>" />
                    <span class="help-block"><?php echo 'エラー表示'; //$error['user_name'] ?? ''; ?></span>
                </div><!-- form-group -->

                <div class="form-group">
                    <input type="text" name="user_email" class="form-control" value="<?php echo xss('$user_email'); ?>" />
                    <span class="help-block"><?php echo 'エラー表示'; //$error['user_email'] ?? ''; ?></span>
                </div><!-- form-group -->

                <div class="form-group">
                    <input type="password" name="user_password" class="form-control" value="" />
                    <span class="help-block"><?php echo 'エラー表示'; //$error['user_password'] ?? ''; ?></span>
                </div><!-- form-group -->

                <div class="form-group">
                    <input type="submit" name="action" class="btn btn-primary btn-block" value="変更" />
                </div><!-- form-group -->

                <div class="form-group">
                    <input type="submit" name="action" class="btn btn-danger btn-block" value="退会" onclick="return confirm('本当に退会しますか？')" />
                </div><!-- form-group -->
                <!-- トークンをPOSTで送信 -->
                <input type="hidden" name="token" value="<?php echo xss($_SESSION['sstoken']); ?>" />
            </form>
            <a href="./admin_user_list.php">戻る</a>

            <hr>
            <footer class="footer">
                <p><?php echo COPYRIGHT; ?></p>
            </footer><!-- footer -->
        </div><!-- container -->
    </body>
</html>