<?php
//お天気情報の関数
function updateWeatherData ($user_id, $city_id, PDO $pdo) {
    //ファイルをインポート
    require_once('config.php');

    //必要なデータを取得
    $weather_list = 'select prefecture, city from weather_setting where user_id = :user_id and city_id = :city_id limit 1';
    $stmt = $pdo->prepare($weather_list);
    $stmt->execute(array(':user_id'=>$user_id,':city_id'=>$city_id));
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

    //API取得
    //お天気情報取得
    $url = 'https://weather.tsukumijima.net/api/forecast?city='.$city_id.'';
    $json_data = file_get_contents($url);
    //例外処理
    if ($json_data === false) {
        return false;
    }
    //データ取得
    $arr = json_decode($json_data);

    //description取得
    //天気概況文の発表時刻（ISO8601 形式 / 例・2020-09-01T04:52:00+09:00）
    //formatがMySQLのdatetimeでは無いため、形式を変換
    $public_time = date('Y-m-d H:i:s', strtotime($arr->description->publicTime));
    //天気概況文の発表時刻（例・2020/09/01 04:52:00）
    //formatがMySQLのdatetimeでは無いため、形式を変換
    $public_time_formatted = date('Y-m-d H:i:s', strtotime($arr->description->publicTimeFormatted));
    //天気概況文（見出しのみ）
    $head_line_text = $arr->description->headlineText;
    //天気概況文（本文のみ）
    $body_text = $arr->description->bodyText;
    //天気概況文
    $text = $arr->description->text;

    //ループ処理の前にdateを昇順(今日、明日、明後日)で並び替え
    usort($arr->forecasts, function ($a, $b) {
        return strcmp($a->date, $b->date);
    });

    //weather情報取得
    foreach ($arr->forecasts as $forecast) {
        //予報日
        $date = $forecast->date;
        //予報日(YYYY-MM-DD形式)
        $date_label = $forecast->dateLabel;
        //天気（今日、明日、明後日）
        $weather = $forecast->telop;
        //最低気温
        $min_temp = $forecast->temperature->min->celsius;
        //最高気温
        $max_temp = $forecast->temperature->max->celsius;
        //降水確率(0時~6時までの降水確率)
        $chance_of_rain_0006 = $forecast->chanceOfRain->T00_06;
        //降水確率(6時~12時までの降水確率)
        $chance_of_rain_0612 = $forecast->chanceOfRain->T06_12;
        //降水確率(12時~18時までの降水確率)
        $chance_of_rain_1218 = $forecast->chanceOfRain->T12_18;
        //降水確率(18時~24時までの降水確率)
        $chance_of_rain_1824 = $forecast->chanceOfRain->T18_24;
        //風速
        $wind_speed = $forecast->detail->wave;
        //風向き
        $wind_direction = $forecast->detail->wind;

        //SQL
        $weather_update = 'update weather_setting
                                set
                                weather = :weather,
                                min_temperature = :min_temperature,
                                max_temperature = :max_temperature,
                                precipitation_t00_06 = :precipitation_t00_06,
                                precipitation_t06_12 = :precipitation_t06_12,
                                precipitation_t12_18 = :precipitation_t12_18,
                                precipitation_t18_24 = :precipitation_t18_24,
                                wind_speed = :wind_speed,
                                wind_direction = :wind_direction,
                                public_time = :public_time,
                                public_time_formatted = :public_time_formatted,
                                headline = :headline,
                                bodytext = :bodytext,
                                text = :text,
                                date = :date,
                                date_label = :date_label,
                                updated_at = now()
                                where user_id = :user_id
                                and city_id = :city_id
                                and date is NULL
                                ';
        $stmt = $pdo->prepare($weather_update);
        $stmt->execute(array(
                ':weather'=>$weather,
                ':min_temperature'=>$min_temp,
                ':max_temperature'=>$max_temp,
                ':precipitation_t00_06'=>$chance_of_rain_0006,
                ':precipitation_t06_12'=>$chance_of_rain_0612,
                ':precipitation_t12_18'=>$chance_of_rain_1218,
                ':precipitation_t18_24'=>$chance_of_rain_1824,
                ':wind_speed'=>$wind_speed,
                ':wind_direction'=>$wind_direction,
                ':public_time'=>$public_time,
                ':public_time_formatted'=>$public_time_formatted,
                ':headline'=>$head_line_text,
                ':bodytext'=>$body_text,
                ':text'=>$text,
                ':date'=>$date,
                ':date_label'=>$date_label,
                ':user_id'=>$user_id,
                ':city_id'=>$city_id
            ));

        if ($stmt->rowCount() === 0) {
            //date=NULLのレコードがない場合は同日のレコードを更新
            $weather_today = 'update weather_setting
                                set
                                weather = :weather,
                                min_temperature = :min_temperature,
                                max_temperature = :max_temperature,
                                precipitation_t00_06 = :precipitation_t00_06,
                                precipitation_t06_12 = :precipitation_t06_12,
                                precipitation_t12_18 = :precipitation_t12_18,
                                precipitation_t18_24 = :precipitation_t18_24,
                                wind_speed = :wind_speed,
                                wind_direction = :wind_direction,
                                public_time = :public_time,
                                public_time_formatted = :public_time_formatted,
                                headline = :headline,
                                bodytext = :bodytext,
                                text = :text,
                                date = :set_date,
                                date_label = :date_label,
                                updated_at = now()
                                where user_id = :user_id
                                and city_id = :city_id
                                and date = :where_date
                                ';
        $stmt = $pdo->prepare($weather_today);
        $stmt->execute(array(
                ':weather'=>$weather,
                ':min_temperature'=>$min_temp,
                ':max_temperature'=>$max_temp,
                ':precipitation_t00_06'=>$chance_of_rain_0006,
                ':precipitation_t06_12'=>$chance_of_rain_0612,
                ':precipitation_t12_18'=>$chance_of_rain_1218,
                ':precipitation_t18_24'=>$chance_of_rain_1824,
                ':wind_speed'=>$wind_speed,
                ':wind_direction'=>$wind_direction,
                ':public_time'=>$public_time,
                ':public_time_formatted'=>$public_time_formatted,
                ':headline'=>$head_line_text,
                ':bodytext'=>$body_text,
                ':text'=>$text,
                ':set_date'=>$date,
                ':where_date'=>$date,
                ':date_label'=>$date_label,
                ':user_id'=>$user_id,
                ':city_id'=>$city_id
            ));
            if ($stmt->rowCount() === 0) {
                $weather_insert = 'insert into weather_setting 
                                ( 
                                user_id,prefecture,city,city_id,weather,min_temperature,max_temperature,precipitation_t00_06,
                                precipitation_t06_12,precipitation_t12_18, precipitation_t18_24,wind_speed,wind_direction,public_time,
                                public_time_formatted,headline, bodytext,text,date,date_label,created_at,updated_at 
                                ) 
                                values 
                                ( 
                                :user_id,:prefecture,:city,:city_id,:weather,:min_temperature,:max_temperature,:precipitation_t00_06,
                                :precipitation_t06_12,:precipitation_t12_18, :precipitation_t18_24,:wind_speed,:wind_direction,:public_time,
                                :public_time_formatted,:headline, :bodytext,:text,:date,:date_label,now(),now() 
                                )';
                $stmt = $pdo->prepare($weather_insert);
                $stmt->execute(array( 
                                ':user_id'=>$user_id, 
                                ':prefecture'=>$user_info['prefecture'], 
                                ':city'=>$user_info['city'],
                                ':city_id'=>$city_id,
                                ':weather'=>$weather, 
                                ':min_temperature'=>$min_temp, 
                                ':max_temperature'=>$max_temp, 
                                ':precipitation_t00_06'=>$chance_of_rain_0006, 
                                ':precipitation_t06_12'=>$chance_of_rain_0612, 
                                ':precipitation_t12_18'=>$chance_of_rain_1218, 
                                ':precipitation_t18_24'=>$chance_of_rain_1824, 
                                ':wind_speed'=>$wind_speed, 
                                ':wind_direction'=>$wind_direction, 
                                ':public_time'=>$public_time, 
                                ':public_time_formatted'=>$public_time_formatted, 
                                ':headline'=>$head_line_text, 
                                ':bodytext'=>$body_text, 
                                ':text'=>$text, 
                                ':date'=>$date,
                                ':date_label'=>$date_label
                            ));
            }
        }
    }
        
} //関数
?>