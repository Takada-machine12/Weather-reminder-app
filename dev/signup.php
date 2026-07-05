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
        <title>ユーザー登録 | <?php echo SERVICE_NAME; ?></title>
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
            <h1>ユーザー登録</h1>
            <form method="POST" class="panel panel-default panel-body" action="signup_complete.php">
                <div class="form-group <?php echo !empty($error['user_name']) ? 'has-error':''; ?>">
                    <label>氏名</label>
                    <input type="text" name="user_name" class="form-control" value="<?php echo xss($user_name ?? ''); ?>" placeholder="氏名" />
                    <span class="help-block"><?php echo $error['user_name'] ?? ''; ?></span>
                </div><!-- form-group -->

                <div class="form-group <?php echo !empty($error['user_email']) ? 'has-error':''; ?>">
                    <label>メールアドレス</label>
                    <input type="email" name="user_email" class="form-control" value="<?php echo xss($user_email ?? ''); ?>" placeholder="メールアドレス" />
                    <span class="help-block"><?php echo $error['user_email'] ?? ''; ?></span>
                </div><!-- form-group -->

                <div class="form-group <?php echo !empty($error['user_password']) ? 'has-error':''; ?>">
                    <label>パスワード</label>
                    <input type="password" name="user_password" class="form-control" value="" placeholder="パスワード" />
                    <span class="help-block"><?php echo $error['user_password'] ?? ''; ?></span>
                </div><!-- form-group -->

                <div class="form-group">
                    <input type="submit" class="btn btn-success btn-block" value="アカウント作成" />
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