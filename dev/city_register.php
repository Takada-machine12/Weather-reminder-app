<?php
//エラーメッセージ表示処理
ini_set('display_errors', 1);
error_reporting(E_ALL);
//ファイルをインポート
require_once('config.php');
require_once('functions.php');
require_once(__DIR__.'/prefectures.php');
require_once(__DIR__.'/city.php');

//Session宣言
session_start();

//ログインチェック機能
if (!isset($_SESSION['USER'])) {
    header('Location:'.SITE_URL.'/index.php');
    exit;
}

$user = $_SESSION['USER'];

//変数初期化
$error = array();
$complete_message = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //CSRF対策
    setToken();

    if (isset($_GET['complete'])) {
        $complete_message = '地域の登録が完了しました。';
    }

    //変数初期化
    $pref_no = 99;
    $city = $cities[99];
} else {
    //CSRF対策
    checkToken();

    $pref_no = (int)($_POST['pref']) ?? 99; //デフォルトは99
    $city = $cities[$pref_no] ?? 99;

    //「決定」ボタンと「登録」ボタンで処理内容を分ける
    if (isset($_POST['select'])) {
        //「決定」ボタン押下時、都道府県Noで市区町村名を逆引きで取得
        $city = $cities[$pref_no];
    } elseif (isset($_POST['register'])) {
        //「登録」ボタン押下時、DBに情報を登録
        //DB接続
        $pdo = connectDb();
        //SQL
        $sql = 'insert into weather_setting (user_id, prefecture, city, city_id, created_at, updated_at)
                values(:user_id, :prefecture, :city, :city_id, now(), now())';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
                        ':user_id'=>$user['id'],
                        ':prefecture'=>$prefectures[$pref_no],
                        ':city'=>$cities[$pref_no][$_POST['city']],
                        ':city_id'=>$_POST['city']
                    ));
        unset($pdo);
        $complete_message = '地域の登録が完了しました。';
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8" />
        <title>地域登録画面 | <?php echo SERVICE_NAME; ?></title>
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
                        <li class="active"><a href="./city_register.php">地域登録</a></li>
                        <li><a href="./weather_list.php">お天気情報一覧</a></li>
                        <li><a href="./register.php">通知時間設定</a></li>
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
            <h1>地域登録画面</h1>
            <?php if ($complete_message): ?>
                <div class="alert alert-success">
                    <?php echo $complete_message; ?>
                </div>
            <?php endif; ?>
            <div class="alert alert-info">
                お住まいの地域を選んで登録してください。
            </div>
            <form method="POST" class="panel panel-default panel-body">
                <!-- 都道府県 -->
                <div class="form-group <?php if(!empty($error['prefecture'])) {echo "has-error";} ?>">
                    <label>お住まいの地域を設定(都道府県)</label>
                    <?php echo arrayToSelect('pref', $prefectures, $pref_no); ?>
                    <span class="help-block"><?php echo $error['prefecture'] ?? ''; ?></span>
                    <button name="select" class="btn" type="submit">決定</button>
                </div><!-- form-group -->

                <!-- 市区町村 -->
                <div class="form-group <?php if(!empty($error['city'])) {echo "has-error";} ?>">
                    <label>お住まいの地域を設定(市区町村)</label>
                    <?php echo arrayToSelect('city', $city, $pref_no); ?>
                    <span class="help-block"><?php echo $error['city'] ?? ''; ?></span>
                </div><!-- form-group -->

                <div class="form-group">
                    <input type="submit" name="register" class="btn btn-success btn-block" value="登録" />
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