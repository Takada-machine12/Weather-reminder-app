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
if (!isset($_SESSION['USER'])) {
    header('Location:'.SITE_URL.'/index.php');
    exit;
}

//セッション情報を取得
$user = $_SESSION['USER'];
$error = array();
$complete_message = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //CSRF対策
    setToken();
    //必要情報の変数化
    $user_name = $user['user_name'] ?? '';
    $user_email = $user['user_email'] ?? '';
    $user_password = $user['user_password'] ?? '';

    if (isset($_GET['complete'])) {
        $complete_message = 'ユーザー情報が変更されました。';
    }
} else {
    //CSRF対策
    checkToken();
    
    //必要情報の変数化
    $id = (int)$user['id'];
    $user_name = $_POST['user_name'] ?? '';
    $user_email = $_POST['user_email'] ?? '';
    $user_password = $_POST['user_password'] ?? '';
    $action = $_POST['action'];

    //エラー処理のための変数化
    $error = array();

    //DB接続
    $pdo = connectDb();

    //ボタンアクションの判定処理
    if ($action === '変更') {
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
                $complete_message = 'ユーザー情報を変更しました。';
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
                $complete_message = 'ユーザー情報を変更しました。';
            }
            unset($pdo);
            //Session情報を更新
            $user['user_name'] = $user_name;
            $user['user_email'] = $user_email;
            $_SESSION['USER'] = $user;

            //管理者にメール通知
            $to = HOST_MAIL;
            $subject = SUBJECT_UPDATE;
            $message = '氏名:'.$user['user_name'].PHP_EOL;
            $message .= 'メールアドレス:'.$user['user_email'];
            $header = $user['user_email'];
            mb_language("Japanese");
            mb_internal_encoding("UTF-8");
            mb_send_mail($to,$subject,$message,$header);

            //更新後リダイレクト
            header('Location: user_edit.php?complete=1');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8" />
        <title>ユーザー情報設定画面 | <?php echo SERVICE_NAME; ?></title>
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
                        <li><a href="./register.php">通知時間設定</a></li>
                        <li class="active"><a href="./user_edit.php">ユーザー情報設定</a></li>
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
            <h1>ユーザー情報編集</h1>
            <?php if($complete_message): ?>
                <div class="alert alert-success">
                    <?php echo $complete_message; ?>
                </div>
            <?php endif; ?>
            <!-- 更新フォーム -->
            <form method="POST" class="panel panel-default panel-body">
                <div class="form-group <?php if(!empty($error['user_name'])) {echo "has-error";} ?>">
                    <label>氏名</label>
                    <input type="text" name="user_name" class="form-control" value="<?php echo xss($user_name); ?>" />
                    <span class="help-block"><?php echo $error['user_name'] ?? ''; ?></span>
                </div><!-- form-group -->

                <div class="form-group <?php if(!empty($error['user_email'])) {echo "has-error";} ?>">
                    <label>メールアドレス</label>
                    <input type="text" name="user_email" class="form-control" value="<?php echo xss($user_email); ?>" />
                    <span class="help-block"><?php echo $error['user_email'] ?? ''; ?></span>
                </div><!-- form-group -->

                <div class="form-group <?php if(!empty($error['user_password'])) {echo "has-error";} ?>">
                    <label>パスワード</label>
                    <input type="password" name="user_password" class="form-control" value="" />
                    <span class="help-block"><?php echo $error['user_password'] ?? ''; ?></span>
                </div><!-- form-group -->

                <div class="form-group">
                    <input type="submit" name="action" class="btn btn-primary btn-block" value="変更" />
                </div><!-- form-group -->

                <!-- トークンをPOSTで送信 -->
                <input type="hidden" name="token" value="<?php echo xss($_SESSION['sstoken']); ?>" />
            </form>

            <!-- 退会フォーム -->
            <form action="user_delete.php" method="POST">
                <input type="hidden" name="id" value="<?php echo xss($user['id']); ?>">
                <input type="hidden" name="token" value="<?php echo xss($_SESSION['sstoken']); ?>" />
                <input type="submit" name="action" class="btn btn-danger btn-block" value="退会" onclick="return confirm('本当に退会しますか？')">
            </form><br >

            <a href="./home.php">戻る</a>

            <hr>
            <footer class="footer">
                <p><?php echo COPYRIGHT; ?></p>
            </footer><!-- footer -->
        </div><!-- container -->
    </body>
</html>