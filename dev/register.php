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

$user = $_SESSION['USER'];

$error = array();
$complete_message = '';

//通知時間設定
//午前の通知時間
$delivery_hours_am = array(
    "99" => "しない",
    "9" => "9時",
    "12" => "12時",
);
//午後の通知時間
$delivery_hours_pm = array(
    "99" => "しない",
    "18" => "18時",
    "21" => "21時",
);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //CSRF対策
    setToken();

    $delivery_hour_am = $user['delivery_hour_am'];
    $delivery_hour_pm = $user['delivery_hour_pm'];

    if (isset($_GET['complete'])) {
        $complete_message = '通知の登録が完了しました。';
    }
} else {
    //CSRF対策
    checkToken();

    $id = (int)$user['id'];
    $delivery_hour_am = $_POST['delivery_hour_am'];
    $delivery_hour_pm = $_POST['delivery_hour_pm'];

    //DB接続
    $pdo = connectDb();

    //通知時間
    $sql = 'update users
            set
            delivery_hour_am = :delivery_hour_am,
            delivery_hour_pm = :delivery_hour_pm,
            updated_at = now()
            where id = :id
            ';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
                    ':delivery_hour_am'=>$delivery_hour_am,
                    ':delivery_hour_pm'=>$delivery_hour_pm,
                    ':id'=>$id
                    ));
    unset($pdo);
    $complete_message = '通知の登録が完了しました。';

    //Session情報更新
    $user['delivery_hour_am'] = $delivery_hour_am;
    $user['delivery_hour_pm'] = $delivery_hour_pm;
    $_SESSION['USER'] = $user;

    //更新後リダイレクト
    header('Location: register.php?complete=1');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8" />
        <title>通知時間設定画面 | <?php echo SERVICE_NAME; ?></title>
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
            <h1>通知時間設定</h1>
            <?php if ($complete_message): ?>
                <div class="alert alert-success">
                    <?php echo $complete_message; ?>
                </div>
            <?php endif; ?>
            <div class="alert alert-info">
                何時に投稿するか投稿時間を設定してください。
            </div>
            <form method="POST" class="panel panel-default panel-body">
                <!-- 午前の通知時間設定 -->
                <div class="form-group <?php if(!empty($error['delivery_hour_am'])) {echo "has-error";} ?>">
                    <label>午前の通知時間</label>
                    <?php echo arrayToSelect("delivery_hour_am", $delivery_hours_am, $user['delivery_hour_am']); ?>
                    <span class="help-block"><?php echo $error['delivery_hour_am'] ?? ''; ?></span>
                </div><!-- form-group -->

                <!-- 午後の通知時間設定 -->
                <div class="form-group <?php if(!empty($error['delivery_hour_pm'])) {echo "has-error";} ?>">
                    <label>午後の通知時間</label>
                    <?php echo arrayToSelect("delivery_hour_pm", $delivery_hours_pm, $user['delivery_hour_pm']); ?>
                    <span class="help-block"><?php echo $error['delivery_hour_pm'] ?? ''; ?></span>
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