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
        <title>TOPページ | <?php echo SERVICE_NAME; ?></title>
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
            <div class="row"><!-- 一つのrowの中では合計で12列作れる。 -->
                <div class="col-md-9">
                    <div class="jumbotron">
                        <h1>お天気情報自動取得サービス</h1>
                        <p>あなたが住んでいる地域を登録してお天気情報をゲットしよう！<br />お天気情報を自動で取得して自分のメールに通知してくれるサービスです。</p>
                        <p><a href="./signup.php" class="btn btn-success btn-lg">新規ユーザー登録(無料) &raquo;</a></p>
                    </div><!-- jumbotron -->

                    <div class="row">
                        <div class="col-md-4">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h2 class="panel-title">どんなことに使えるの？</h2>
                                </div><!-- panel-heading -->
                                <div class="panel-body">
                                    <p>お天気APIを利用して自分が登録した地域のお天気情報を取得しメールに通知を送れます。</p>
                                </div><!-- panel-body -->
                            </div><!-- panel panel-default -->
                        </div><!-- col-md-4 -->

                        <div class="col-md-4">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h2 class="panel-title">お金がかかる？</h2>
                                </div><!-- panel-heading -->
                                <div class="panel-body">
                                    <p>無料でご利用いただけます。</p>
                                </div><!-- panel-body -->
                            </div><!-- panel panel-default -->
                        </div><!-- col-md-4 -->

                        <div class="col-md-4">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h2 class="panel-title">登録内容は他の方にも見られるの？</h2>
                                </div><!-- panel-heading -->
                                <div class="panel-body">
                                    <p>登録した内容は他の方には見られません。</p>
                                </div><!-- panel-body -->
                            </div><!-- panel panel-default -->
                        </div><!-- col-md-4 -->
                    </div><!-- row -->
                </div><!-- col-md-9 -->

                <div class="col-md-3">
                    <div class="sidebar-nav panel panel-default">
                        <div class="panel-heading">
                            <h2 class="panel-title">ログイン</h2>
                        </div><!-- panel-heading -->
                        <div class="panel-body">
                            <form method="POST" action="home.php">

                                <div class="form-group <?php echo !empty($error['user_email']) ? 'has-error':''; ?>">
                                    <label>メールアドレス</label>
                                    <input type="email" class="form-control" name="user_email" value="" placeholder="メールアドレス" />
                                    <span class="help-block"><?php echo $error['user_email'] ?? ''; ?></span>
                                </div><!-- form-group -->

                                <div class="form-group <?php echo !empty($error['user_password']) ? 'has-error':''; ?>">
                                    <label>パスワード</label>
                                    <input type="password" class="form-control" name="user_password" placeholder="パスワード" />
                                    <span class="help-block"><?php echo $error['user_password'] ?? ''; ?></span>
                                </div><!-- form-group -->

                                <div class="form-group">
                                    <input type="submit" class="btn btn-primary btn-block" value="ログイン" />
                                </div><!-- form-group -->
                                
                                <div class="form-group">
                                    <label><input type="checkbox" name="auto_login">次回から自動ログイン</label>
                                </div><!-- form-group -->
    
                                <div class="form-group">
                                    <a href="reminder.php">パスワードを忘れた方はこちら。</a>
                                </div><!-- form-group -->
                                <!-- トークンをPOSTで送信 -->
                                <input type="hidden" name="token" value="<//?php echo xss($_SESSION['sstoken']); ?>" />
                            </form>
                        </div><!-- panel-body -->
                    </div><!-- sidebar-nav panel panel-default -->
                    <div class="sidebar-nav panel panel-default">
                        <div class="panel-heading">
                            <h2 class="panel-title">管理者ログイン</h2>
                        </div><!-- panel-heading -->
                        <div class="panel-body">
                            <form method="POST" action="admin_home.php">

                                <div class="form-group <//?php echo !empty($error['admin_account']) ? 'has-error':''; ?>">
                                    <label>管理者アカウント名</label>
                                    <input type="text" class="form-control" name="admin_account" value="<//?php echo xss($admin_account ?? ''); ?>" placeholder="管理者アカウント名" />
                                    <span class="help-block"><?php echo $error['admin_account'] ?? ''; ?></span>
                                </div><!-- form-group -->

                                <div class="form-group <//?php echo !empty($error['admin_password']) ? 'has-error':''; ?>">
                                    <label>管理者パスワード</label>
                                    <input type="password" class="form-control" name="admin_password" placeholder="管理者パスワード" />
                                    <span class="help-block"><?php echo $error['admin_password'] ?? ''; ?></span>
                                </div><!-- form-group -->

                                <div class="form-group">
                                    <input type="submit" class="btn btn-primary btn-block" value="ログイン" />
                                </div><!-- form-group -->
                                <!-- トークンをPOSTで送信 -->
                                <input type="hidden" name="token" value="<//?php echo xss($_SESSION['sstoken']); ?>" />
                            </form>
                        </div>
                    </div><!-- sidebar-nav panel panel-default -->
                </div><!-- col-md-3 -->
            </div><!-- row -->
            <hr>
            <footer class="footer">
                <p><?php echo COPYRIGHT; ?></p>
            </footer><!-- footer -->
        </div><!-- container -->

    </body>
</html>