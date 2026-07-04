create table users (                                           -- ユーザTBL
    id integer not null auto_increment                         -- ユーザID
    ,user_name varchar(30) not null                            -- ニックネーム
    ,user_email varchar(255) not null unique                   -- メールアドレス
    ,user_password varchar(255) not null                       -- パスワード
    ,delivery_hour_am int not null default 99                  -- 通知設定朝
    ,delivery_hour_pm int not null default 99                  -- 通知設定夜
    ,created_at datetime not null default current_timestamp()  -- 作成日時
    ,updated_at datetime null default current_timestamp()      -- 更新日時
    ,primary key(id)                                           -- PK
);

create table weather_setting (                                 -- Weather TBL
    id integer not null auto_increment                         -- weatherID
    ,user_id integer not null                                  -- ユーザID(複数地域を登録できるように重複許可)
    ,prefecture varchar(50) not null                           -- 都道府県
    ,city varchar(100) not null                                -- 市区町村
    ,city_id int not null                                      -- 地域のID
    ,weather varchar(10) null                                  -- 天気(晴れ、曇り、雨、大雨、雷、雪、雹、台風etc...)
    ,min_temperature int null                                  -- 最低気温
    ,max_temperature int null                                  -- 最高気温
    ,precipitation_t00_06 varchar(100) null                    -- 降水確率(0時~6時までの降水確率：6%など)
    ,precipitation_t06_12 varchar(100) null                    -- 降水確率(6時~12時までの降水確率)
    ,precipitation_t12_18 varchar(100) null                    -- 降水確率(12時~18時までの降水確率)
    ,precipitation_t18_24 varchar(100) null                    -- 降水確率(18時~24時までの降水確率)
    ,wind_speed varchar(50) null                               -- 風速(風速0.5メートルなど)
    ,wind_direction varchar(10) null                           -- 風向き(北の風)
    ,public_time datetime null                                 -- 天気概況文の発表時刻(ISO8601 形式 / 例・2020-09-01T04:52:00+09:00)
    ,public_time_formatted datetime null                       -- 天気概況文の発表時刻(例・2020/09/01 04:52:00)
    ,headline varchar(255) null                                -- 天気概況文(見出しのみ)
    ,bodytext varchar(255) null                                -- 天気概況文(本文のみ)
    ,text varchar(255) null                                    -- 天気概況文
    ,date date null                                            -- 予報日(YYYY-MM-DD形式)
    ,date_label varchar(10) null                               -- 予報日(今日、明日、明後日のいずれか)
    ,created_at datetime not null default current_timestamp()  -- 作成日時
    ,updated_at datetime null default current_timestamp()      -- 更新日時
    ,primary key(id)                                           -- PK
    ,foreign key(user_id) references users(id)                 -- CK
);

create table admin_info (                                           -- 管理者TBL
    id int not null auto_increment                                  -- 管理者ID
    ,admin_account varchar(20) not null default '管理者アカウント名'   -- 管理者アカウント名
    ,admin_password varchar(20) not null default '管理者パスワード'    -- 管理者パスワード
    ,news_text varchar(255) null                                    -- お知らせ文
    ,created_at datetime not null default current_timestamp()       -- 作成日時 
    ,updated_at datetime null default current_timestamp()           -- 更新日時
    ,primary key(id)                                                -- PK
);

create table auto_login (                                      -- 自動ログインTBL
    id integer not null auto_increment                         -- auto_loginID
    ,user_id integer not null unique                           -- ユーザID
    ,c_key varchar(255) not null                               -- Cookie
    ,expire datetime not null                                  -- セッション時間
    ,created_at datetime not null default current_timestamp()  -- 作成日時
    ,updated_at datetime null default current_timestamp()      -- 更新日時
    ,primary key(id)                                           -- PK
    ,foreign key(user_id) references users(id)                 -- CK
);

create table cron_log (                                        -- cronlogTBL
    id integer not null auto_increment                         -- cronlogID
    ,user_id integer not null                                  -- userID
    ,cron_message varchar(255) not null                        -- cronメッセージ
    ,created_at datetime not null default current_timestamp()  -- 作成日時
    ,primary key(id)
    ,foreign key(user_id) references users(id)
);