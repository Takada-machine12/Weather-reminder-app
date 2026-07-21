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
$weather_info = array();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    //CSRF対策
    setToken();

    //DB接続
    $pdo = connectDb();
    //SQL
    $sql = 'select * from weather_setting where user_id = :user_id order by created_at';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':user_id'=>$user['id']));
    $weather_info = $stmt->fetchAll(PDO::FETCH_ASSOC);

    unset($pdo);
}
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
                        <th>地域</th>
                        <th>日付</th>
                        <th>天気</th>
                        <th>最高気温</th>
                        <th>最低気温</th>
                        <th>降水確率(0時-6時)</th>
                        <th>降水確率(6時-12時)</th>
                        <th>降水確率(12時-18時)</th>
                        <th>降水確率(18時-24時)</th>
                        <th>風向き</th>
                        <th>風速</th>
                        <th>予報日</th>
                    </tr>
                    <?php foreach($weather_info as $weather): ?>
                        <tr>
                            <td><?php echo xss($weather['prefecture'].$weather['city']); ?></td>
                            <td><?php echo xss(displayWeatherValue($weather['date_label'])); ?></td>
                            <td><?php echo xss(displayWeatherValue($weather['weather'])); ?></td>
                            <td><?php echo xss(displayTemperature($weather['max_temperature'])); ?></td>
                            <td><?php echo xss(displayTemperature($weather['min_temperature'])); ?></td>
                            <td><?php echo xss(displayWeatherValue($weather['precipitation_t00_06'])); ?></td>
                            <td><?php echo xss(displayWeatherValue($weather['precipitation_t06_12'])); ?></td>
                            <td><?php echo xss(displayWeatherValue($weather['precipitation_t12_18'])); ?></td>
                            <td><?php echo xss(displayWeatherValue($weather['precipitation_t18_24'])); ?></td>
                            <td><?php echo xss(displayWeatherValue($weather['wind_direction'])); ?></td>
                            <td><?php echo xss(displayWeatherValue($weather['wind_speed'])); ?></td>
                            <td><?php echo xss(displayWeatherValue($weather['date'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
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