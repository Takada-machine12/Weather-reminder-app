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
if (!isset($_SESSION['ADMIN_USER'])) {
    header('Location:'.SITE_URL.'/index.php');
    exit;
}

//セッション情報を取得
$admin_user = $_SESSION['ADMIN_USER'];
$error = array();
$users = array();
$complete_message = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //CSRF対策
    setToken();

    //GETでのアクセス時は登録されているユーザ情報を表示
    $id = (int)($_GET['id'] ?? 0);

    //DB接続
    $pdo = connectDb();

    //SQL
    $sql = 'select user_name,user_email,user_password from users where id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':id'=>$id));
    $user = $stmt->fetch();

    if (!$user) {
        exit;
    }

    //取得したユーザ情報を変数化
    $user_name = $user['user_name'];
    $user_email = $user['user_email'];
    $user_password = $user['user_password'];

    unset($pdo);
} else {
    //CSRF対策
    checkToken();

    //DB接続
    $pdo = connectDb();

    //ユーザから受け取った情報を変数化
    $id = (int)($_POST['id'] ?? 0);
    $user_name = $_POST['user_name'] ?? '';
    $user_email = $_POST['user_email'] ?? '';
    $user_password = $_POST['user_password'] ?? '';

    //ボタンアクションの判定処理
    if ($_POST['action'] === '退会') {

        try {
            //トランザクション
            $pdo->beginTransaction();
            //SQL
            //usersTBLから削除
            $sql1 = 'delete from users where id = :id';
            $stmt = $pdo->prepare($sql1);
            $stmt->execute(array(':id'=>$id));

            //weather_settingTBLから削除
            $sql2 = 'delete from weather_setting where user_id = :id';
            $stmt = $pdo->prepare($sql2);
            $stmt->execute(array(':id'=>$id));

            //cron_logTBLから削除
            $sql3 = 'delete from cron_log where user_id = :id';
            $stmt = $pdo->prepare($sql3);
            $stmt->execute(array(':id'=>$id));

            //全ての処理が問題なければ実行
            $pdo->commit();

            $complete_message = 'ユーザー情報を削除しました。';

            //一覧画面へ遷移
            header('Location:'.SITE_URL.'/admin_user_list.php');
            unset($pdo);
            exit;
        } catch (Exception $e) {
            //エラーの場合は処理を取り消す
            $pdo->rollBack();

            error_log($e->getMessage());
            exit('システムエラーが発生しました。');
        }
    } elseif ($_POST['action'] === '変更') {
        //ユーザ情報が登録されていない場合は以下を表示
        //ユーザ名のバリデーションチェック
        if (empty($user_name)) {
            $error['user_name'] = 'ユーザー名が登録されていません。';
        } else {
            if (strlen(mb_convert_encoding($user_name, 'SJIS', 'UTF-8')) > 30 ) {
                $error['user_name'] = 'ユーザー名は30文字以内で入力してください。';
            }
        }
        
        //メールアドレスのバリデーションチェック
        if ($user_email === '') {
            $error['user_email'] = 'メールアドレスを入力してください。';
        } elseif (!filter_var($user_email,FILTER_VALIDATE_EMAIL)) {
            $error['user_email'] = '形式が正しくありません。正しい形式のメールアドレスを入力してください。';
        } else {
            if (checkUserEmail($user_email,$id,$pdo)) {
                $error['user_email'] = 'このメールアドレスは既に登録されています。';
            }
        }

        if (empty($error)) {
            if ($user_password === '') {
                //SQL
                $sql = 'update users
                        set
                        user_name = :user_name,
                        user_email = :user_email,
                        updated_at = now()
                        where id = :id
                    ';
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(
                                ':id'=>$id,
                                ':user_name'=>$user_name,
                                ':user_email'=>$user_email,
                            ));
                $complete_message = 'ユーザー情報が変更されました。';
            } else {
                //SQL
                $sql = 'update users
                        set
                        user_name = :user_name,
                        user_email = :user_email,
                        user_password = :user_password,
                        updated_at = now()
                        where id = :id
                    ';
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(
                                ':id'=>$id,
                                ':user_name'=>$user_name,
                                ':user_email'=>$user_email,
                                ':user_password'=>password_hash($user_password,PASSWORD_DEFAULT)
                            ));
                $complete_message = 'ユーザー情報が変更されました。';

                unset($pdo);
            }
        }
    }
}
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
                        <li>
                            <form action="admin_logout.php" method="POST">
                                <input type="hidden" name="token" value="<?php echo xss($_SESSION['sstoken']); ?>">
                                <input type="submit" value="ログアウト" class="btn btn-link navbar-btn">
                            </form>
                        </li>
                    </ul><!-- ul -->
                </div><!-- container -->
            </div><!-- navbar-inner -->
        </div><!-- navbar-inverse -->

        <div class="container">
            <h1>ユーザー情報編集</h1>
            <?php if($complete_message): ?>
                <div class="alert alert-success">
                    <?php echo $complete_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="panel panel-default panel-body">
                <input type="hidden" name="id" value="<?php echo xss($id) ?>" />
                <div class="form-group">
                    <input type="text" name="user_name" class="form-control" value="<?php echo xss($user_name); ?>" />
                    <span class="help-block"><?php echo $error['user_name'] ?? ''; ?></span>
                </div><!-- form-group -->

                <div class="form-group">
                    <input type="text" name="user_email" class="form-control" value="<?php echo xss($user_email); ?>" />
                    <span class="help-block"><?php echo $error['user_email'] ?? ''; ?></span>
                </div><!-- form-group -->

                <div class="form-group">
                    <input type="password" name="user_password" class="form-control" value="" />
                    <span class="help-block"><?php echo $error['user_password'] ?? ''; ?></span>
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