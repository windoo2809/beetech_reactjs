-- 顧客向けシステム トリガ定義ファイル
 -- ファイル作成日 2021/05/03
 -- ファイル作成者 UM 山口
 -- ----------------------

 -- トリガー： 顧客情報（新規） 更新日：21/05/03
DROP TRIGGER IF EXISTS ins_customer;

CREATE TRIGGER ins_customer AFTER INSERT ON customer FOR EACH ROW
INSERT INTO  cu_customer ( core_id, customer_name, customer_name_kana, construction_number_require_flag , customer_system_use_flag, create_system_type, create_user_id, status )
VALUES ( 
      new.id                                        -- 基幹システム連携ID
    , new.customer_name                             -- 顧客会社名
    , new.customer_name_kana                        -- 顧客会社名：カナ
    , new.construction_number_require_flag          -- 工事番号必須フラグ
    , new.customer_system_use_flag                  -- 顧客システム利用有無
    , 1                                             -- 固定値：基幹システム
    , 0                                             -- 固定値：システム自動連携
    , ISNULL( new.delete_timestamp )                -- ステータス
);

 -- トリガー： 顧客情報（更新） 更新日：21/05/03
DROP TRIGGER IF EXISTS upd_customer;

CREATE TRIGGER upd_customer AFTER UPDATE ON customer FOR EACH ROW
BEGIN
 -- すでに連携済みのデータが存在する場合は更新を行う
IF EXISTS ( SELECT 1 FROM cu_customer WHERE core_id = new.id ) THEN
  UPDATE cu_customer
    SET 
     customer_name                    = new.customer_name                     -- 顧客会社名
    ,customer_name_kana               = new.customer_name_kana                -- 顧客会社名：カナ
    ,construction_number_require_flag = new.construction_number_require_flag  -- 工事番号必須フラグ
    ,customer_system_use_flag         = new.customer_system_use_flag          -- 顧客システム利用有無
    ,update_system_type               = 1                                     -- 固定値：基幹システム
    ,update_user_id                   = 0                                     -- 固定値：システム自動連携
    ,status                           = ISNULL( new.delete_timestamp )        -- ステータス
  WHERE core_id = new.id
  ;
 -- 連携済みのデータが存在しない場合は登録を行う
ELSE
  INSERT INTO  cu_customer ( 
       core_id, 
       customer_name, 
       customer_name_kana, 
       construction_number_require_flag , 
       customer_system_use_flag, 
       create_system_type, 
       create_user_id, 
       status )
  VALUES ( 
        new.id                                        -- 基幹システム連携ID
      , new.customer_name                             -- 顧客会社名
      , new.customer_name_kana                        -- 顧客会社名：カナ
      , new.construction_number_require_flag          -- 工事番号必須フラグ
      , new.customer_system_use_flag                  -- 顧客システム利用有無
      , 1                                             -- 固定値：基幹システム
      , 0                                             -- 固定値：システム自動連携
      , ISNULL( new.delete_timestamp )                -- ステータス
  );
END IF;
END;


 -- トリガー： 顧客支店情報（登録） 更新日：21/05/04
DROP TRIGGER IF EXISTS ins_customer_branch;


CREATE TRIGGER ins_customer_branch AFTER INSERT ON customer_branch FOR EACH ROW
BEGIN

DECLARE _customer_id int;

SELECT customer_id 
INTO _customer_id
FROM cu_customer
WHERE core_id = new.customer_id; 

INSERT INTO cu_customer_branch ( 
    customer_id
  , core_id
  , customer_branch_name
  , customer_branch_name_kana
  , zip
  , prefecture
  , city
  , address
  , create_system_type
  , create_user_id
  , status 
)
VALUES (
      _customer_id                                -- 顧客ID
    , new.id                                        -- 基幹システム連携ID
    , new.customer_branch_name                      -- 顧客支店名
    , new.customer_branch_name_kana                 -- 顧客支店名：カナ
    , new.customer_branch_zip_code                  -- 顧客支店所在地：郵便番号
    , new.customer_branch_prefecture                -- 顧客支店所在地：都道府県コード
    , new.customer_branch_city                      -- 顧客支店所在地：市区町村
    , new.customer_branch_address                   -- 顧客支店所在地：番地（町名以降）
    , 1                                             -- 固定値：基幹システム
    , 0                                             -- 固定値：システム自動連携
    , ISNULL( new.delete_timestamp )                -- ステータス
);

END;

 -- トリガー： 顧客支店情報（更新） 更新日：21/06/09
DROP TRIGGER IF EXISTS upd_customer_branch;

CREATE TRIGGER upd_customer_branch AFTER UPDATE ON customer_branch FOR EACH ROW
BEGIN

DECLARE _customer_id int;

 -- すでに連携済みのデータが存在する場合は更新を行う
IF EXISTS ( SELECT 1 FROM cu_customer_branch WHERE core_id = new.id ) THEN
  UPDATE cu_customer_branch
    SET 
     customer_branch_name             = new.customer_branch_name              -- 顧客支店名
    ,customer_branch_name_kana        = new.customer_branch_name_kana         -- 顧客支店名：カナ
    ,zip                              = new.customer_branch_zip_code          -- 顧客支店所在地：郵便番号
    ,prefecture                       = new.customer_branch_prefecture        -- 顧客支店所在地：都道府県コード
    ,city                             = new.customer_branch_city              -- 顧客支店所在地：市区町村
    ,address                          = new.customer_branch_address           -- 顧客支店所在地：番地（町名以降）
    ,update_system_type               = 1                                     -- 固定値：基幹システム
    ,update_user_id                   = 0                                     -- 固定値：システム自動連携
    ,status                           = ISNULL( new.delete_timestamp )        -- ステータス
  WHERE core_id = new.id
  ;
 -- 連携済みのデータが存在しない場合は登録を行う
ELSE

  SELECT customer_id 
  INTO _customer_id
  FROM cu_customer
  WHERE core_id = new.customer_id; 

  INSERT INTO cu_customer_branch ( 
    customer_id
  , core_id
  , customer_branch_name
  , customer_branch_name_kana
  , zip
  , prefecture
  , city
  , address
  , create_system_type
  , create_user_id
  , status 
  )
  VALUES (
      _customer_id                                -- 顧客ID
    , new.id                                        -- 基幹システム連携ID
    , new.customer_branch_name                      -- 顧客支店名
    , new.customer_branch_name_kana                 -- 顧客支店名：カナ
    , new.customer_branch_zip_code                  -- 顧客支店所在地：郵便番号
    , new.customer_branch_prefecture                -- 顧客支店所在地：都道府県コード
    , new.customer_branch_city                      -- 顧客支店所在地：市区町村
    , new.customer_branch_address                   -- 顧客支店所在地：番地（町名以降）
    , 1                                             -- 固定値：基幹システム
    , 0                                             -- 固定値：システム自動連携
    , ISNULL( new.delete_timestamp )                -- ステータス
  );
END IF;
END;

 -- トリガー： 顧客担当者情報（登録） 更新日：21/06/10
DROP TRIGGER IF EXISTS ins_customer_user;

CREATE TRIGGER ins_customer_user AFTER INSERT ON customer_user FOR EACH ROW
BEGIN

  DECLARE _customer_id, _customer_branch_id int;

  -- 基幹システムで登録した場合
  IF new.lastupdate_user_id <> 1 THEN  -- 一時的な退避処理（システム種別追加後に修正）

    SELECT customer_id,  customer_branch_id
    INTO  _customer_id, _customer_branch_id
    FROM  cu_customer_branch
    WHERE core_id = new.customer_branch_id;

    INSERT INTO cu_customer_user (
       customer_id
     , customer_branch_id
     , core_id
     , customer_user_name
     , customer_user_name_kana
     , customer_user_division_name
     , customer_user_email
     , customer_user_tel
     , customer_reminder_sms_flag
     , create_system_type
     , create_user_id
     , status
    )
    VALUES (
       _customer_id                                  -- 顧客会社ID
     , _customer_branch_id                           -- 顧客支店ID
     , new.id                                        -- 基幹システム連携ID
     , new.customer_user_name                        -- 顧客担当者名
     , new.customer_user_name_kana                   -- 顧客担当者名：カナ
     , new.customer_user_division_name               -- 顧客担当者部署名
     , new.customer_user_email                       -- 顧客担当者：メールアドレス
     , new.customer_user_tel                         -- 顧客担当者：携帯電話番号
     , new.customer_reminder_sms_flag                -- 顧客担当者：SMSリマインド送付有無
     , 1                                             -- 固定値：基幹システム
     , 0                                             -- 固定値：システム自動連携
     , ISNULL( new.delete_timestamp )                -- ステータス
    );

  END IF;
END;

 -- トリガー： 顧客担当者情報（更新） 更新日：21/06/10
DROP TRIGGER IF EXISTS upd_customer_user;

CREATE TRIGGER upd_customer_user AFTER UPDATE ON customer_user FOR EACH ROW
BEGIN

  DECLARE _customer_id, _customer_branch_id int;

  -- 基幹システムで登録した場合
  IF new.lastupdate_user_id <> 1 THEN  -- 一時的な退避処理（システム種別追加後に修正）

    -- すでに連携済みのデータが存在する場合は更新を行う
    IF EXISTS ( SELECT 1 FROM cu_customer_user WHERE core_id = new.id ) THEN
      UPDATE cu_customer_user
      SET 
         customer_user_name             = new.customer_user_name               -- 顧客担当者名
        ,customer_user_name_kana        = new.customer_user_name_kana          -- 顧客担当者名：カナ
        ,customer_user_division_name    = new.customer_user_division_name      -- 顧客担当者部署名
        ,customer_user_email            = new.customer_user_email              -- 顧客担当者：メールアドレス
        ,customer_user_tel              = new.customer_user_tel                -- 顧客担当者：携帯電話番号
        ,customer_reminder_sms_flag     = new.customer_reminder_sms_flag       -- 顧客担当者：SMSリマインド送付有無
        ,update_system_type             = 1                                    -- 固定値：基幹システム
        ,update_user_id                 = 0                                    -- 固定値：システム自動連携
        ,status                         = ISNULL( new.delete_timestamp )       -- ステータス
      WHERE core_id = new.id
      ;

  -- 連携済みのデータが存在しない場合は登録を行う
    ELSE

      SELECT customer_id,  customer_branch_id
      INTO  _customer_id, _customer_branch_id
      FROM  cu_customer_branch
      WHERE core_id = new.customer_branch_id;

      INSERT INTO cu_customer_user (
        customer_id
      , customer_branch_id
      , core_id
      , customer_user_name
      , customer_user_name_kana
      , customer_user_division_name
      , customer_user_email
      , customer_user_tel
      , customer_reminder_sms_flag
      , create_system_type
      , create_user_id
      , status
      )
      VALUES (
        _customer_id                                  -- 顧客会社ID
      , _customer_branch_id                           -- 顧客支店ID
      , new.id                                        -- 基幹システム連携ID
      , new.customer_user_name                        -- 顧客担当者名
      , new.customer_user_name_kana                   -- 顧客担当者名：カナ
      , new.customer_user_division_name               -- 顧客担当者部署名
      , new.customer_user_email                       -- 顧客担当者：メールアドレス
      , new.customer_user_tel                         -- 顧客担当者：携帯電話番号
      , new.customer_reminder_sms_flag                -- 顧客担当者：SMSリマインド送付有無
      , 1                                             -- 固定値：基幹システム
      , 0                                             -- 固定値：システム自動連携
      , ISNULL( new.delete_timestamp )                -- ステータス
      );
    END IF;
  END IF;
END;


 -- トリガー： 駐車場情報（登録） 更新日：21/05/03
DROP TRIGGER IF EXISTS ins_parking;

CREATE TRIGGER ins_parking AFTER INSERT ON parking FOR EACH ROW
INSERT INTO cu_parking ( core_id, parking_name, parking_name_kana, latitude , longitude , create_system_type, create_user_id, status  )
VALUES (
      new.id                                    -- 基幹システム連携ID
    , new.parking_name                          -- 駐車場名
    , new.parking_name_kana                     -- 駐車場名：カナ
    , new.latitude                              -- 緯度
    , new.longitude                             -- 経度
    , 1                                         -- 固定値：基幹システム
    , 0                                         -- 固定値：システム自動連携
    , ISNULL( new.delete_timestamp )            -- ステータス
);


 -- トリガー： 駐車場情報（更新） 更新日：21/05/03
DROP TRIGGER IF EXISTS upd_parking;

CREATE TRIGGER upd_parking AFTER UPDATE ON parking FOR EACH ROW
BEGIN
 -- すでに連携済みのデータが存在する場合は更新を行う
IF EXISTS ( SELECT 1 FROM cu_parking WHERE core_id = new.id ) THEN
  UPDATE cu_parking
    SET 
     parking_name             = new.parking_name                     -- 駐車場名
    ,parking_name_kana        = new.parking_name_kana                -- 駐車場名：カナ
    ,latitude                 = new.latitude                         -- 緯度
    ,longitude                = new.longitude                        -- 経度
    ,update_system_type       = 1                                    -- 固定値：基幹システム
    ,update_user_id           = 0                                    -- 固定値：システム自動連携
    ,status                   = ISNULL( new.delete_timestamp )       -- ステータス
  WHERE core_id = new.id
  ;
 -- 連携済みのデータが存在しない場合は登録を行う
ELSE
  INSERT INTO cu_parking ( core_id, parking_name, parking_name_kana, latitude , longitude , create_system_type, create_user_id, status  )
  VALUES (
      new.id                                    -- 基幹システム連携ID
    , new.parking_name                          -- 駐車場名
    , new.parking_name_kana                     -- 駐車場名：カナ
    , new.latitude                              -- 緯度
    , new.longitude                             -- 経度
    , 1                                         -- 固定値：基幹システム
    , 0                                         -- 固定値：システム自動連携
    , ISNULL( new.delete_timestamp )            -- ステータス
  );
  END IF;
END;


 -- トリガー： ランドマーク支店（登録） 更新日：21/05/03
DROP TRIGGER IF EXISTS ins_user_branch;

CREATE TRIGGER ins_user_branch AFTER INSERT ON user_branch FOR EACH ROW
INSERT 
INTO cu_branch( 
    core_id
    , branch_name
    , prefecture
    , city
    , address
    , tel
    , fax
    , zip_code
    , bank_account
    , create_system_type
    , create_user_id
    , status
)
VALUES (
      new.id                                    -- 基幹システム連携ID
    , new.user_branch_name                      -- 支店名
    , new.user_branch_prefecture                -- 都道府県コード
    , new.user_branch_city                      -- 市区町村
    , new.user_branch_address                   -- 番地（町名以降）
    , new.user_branch_tel                       -- 電話番号
    , new.user_branch_fax                       -- FAX
    , new.user_branch_zip_code                  -- 郵便番号
    , new.user_branch_deposit_bank_account      -- 敷金口座名
    , 1                                         -- 固定値：基幹システム
    , 0                                         -- 固定値：システム自動連携
    , ISNULL( new.delete_timestamp )            -- ステータス
);

 -- トリガー： ランドマーク支店（更新） 更新日：21/05/03
DROP TRIGGER IF EXISTS upd_user_branch;

CREATE TRIGGER upd_user_branch AFTER UPDATE ON user_branch FOR EACH ROW
BEGIN
 -- すでに連携済みのデータが存在する場合は更新を行う
IF EXISTS ( SELECT 1 FROM cu_branch WHERE core_id = new.id ) THEN
  UPDATE cu_branch
    SET 
     branch_name              = new.user_branch_name                   -- 支店名
    ,prefecture               = new.user_branch_prefecture             -- 都道府県コード
    ,city                     = new.user_branch_city                   -- 市区町村
    ,address                  = new.user_branch_address                -- 番地（町名以降）
    ,tel                      = new.user_branch_tel                    -- 電話番号
    ,fax                      = new.user_branch_fax                    -- FAX
    ,zip_code                 = new.user_branch_zip_code               -- 郵便番号
    ,bank_account             = new.user_branch_deposit_bank_account   -- 敷金口座名    
    ,update_system_type       = 1                                      -- 固定値：基幹システム
    ,update_user_id           = 0                                      -- 固定値：システム自動連携
    ,status                   = ISNULL( new.delete_timestamp )         -- ステータス
  WHERE core_id = new.id
  ;
 -- 連携済みのデータが存在しない場合は登録を行う
ELSE
  INSERT 
  INTO cu_branch( 
      core_id
    , branch_name
    , prefecture
    , city
    , address
    , tel
    , fax
    , zip_code
    , bank_account
    , create_system_type
    , create_user_id
    , status
  )
  VALUES (
      new.id                                    -- 基幹システム連携ID
    , new.user_branch_name                      -- 支店名
    , new.user_branch_prefecture                -- 都道府県コード
    , new.user_branch_city                      -- 市区町村
    , new.user_branch_address                   -- 番地（町名以降）
    , new.user_branch_tel                       -- 電話番号
    , new.user_branch_fax                       -- FAX
    , new.user_branch_zip_code                  -- 郵便番号
    , new.user_branch_deposit_bank_account      -- 敷金口座名
    , 1                                         -- 固定値：基幹システム
    , 0                                         -- 固定値：システム自動連携
    , ISNULL( new.delete_timestamp )            -- ステータス 
  );
END IF;
END;

 -- トリガー： 工事情報（登録） 更新日：21/06/10
DROP TRIGGER IF EXISTS ins_project;

CREATE TRIGGER ins_project AFTER INSERT ON project FOR EACH ROW
BEGIN

  DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id INT;

  -- 顧客向けシステムから登録した場合は処理を終了する
  IF new.register_type = 0 THEN 

    -- 顧客情報の取得
    SELECT 
      ccu.customer_id,ccu.customer_branch_id, ccu.customer_user_id
    INTO _customer_id, _customer_branch_id, _customer_user_id
    FROM cu_customer_user ccu
    WHERE ccu.core_id = new.customer_user_id;

    -- ランドマーク支店情報の取得
    SELECT branch_id
    INTO _branch_id
    FROM cu_branch
    WHERE core_id = new.user_branch_id;

    INSERT INTO cu_project( 
        core_id                                   -- 基幹システム連携ID
      , customer_id                               -- 顧客会社ID
      , customer_branch_id                        -- 顧客支店ID
      , customer_user_id                          -- 顧客担当者ID
      , branch_id                                 -- 支店ID
      , construction_number                       -- 工事番号
      , site_name                                 -- 現場名／邸名
      , site_name_kana                            -- 現場名／邸名：カナ
      , site_prefecture                           -- 都道府県コード
      , site_city                                 -- 市区町村名
      , site_address                              -- 番地（町名以降）
      , latitude                                  -- 緯度
      , longitude                                 -- 経度
      , create_system_type                        -- 固定値：基幹システム
      , create_user_id                            -- 固定値：システム自動連携
      , status                                    -- ステータス
    ) 
    VALUES (
      new.id                                    -- 基幹システム連携ID
    , _customer_id                              -- 顧客会社ID
    , _customer_branch_id                       -- 顧客支店ID
    , _customer_user_id                         -- 顧客担当者ID
    , _branch_id                                -- 支店ID
    , new.construction_number                   -- 工事番号
    , new.site_name                             -- 現場名／邸名
    , new.site_name_kana                        -- 現場名／邸名：カナ
    , new.site_prefecture                       -- 都道府県コード
    , new.site_city                             -- 市区町村名
    , new.site_address                          -- 番地（町名以降）
    , new.latitude                              -- 緯度
    , new.longitude                             -- 経度
    , 1                                         -- 固定値：基幹システム
    , 0                                         -- 固定値：システム自動連携
    , ISNULL( new.delete_timestamp )            -- ステータス 
    );

  END IF;
END;

 -- ★課題
 -- 住所コード、市区町村コード、市区町村名、工事開始日、工事終了日 ⇒ 基幹側へ保持
 -- 駐車場調査依頼書の電話番号 ⇒ 顧客向けシステムに要追加
 -- 元請下請送付先種別  ⇒ 顧客向けシステムに要追加
 -- 下請顧客会社ID、下請顧客支店ID、下請顧客担当者ID ⇒ こちらは受取らない前提



-- トリガー： 工事情報（更新） 更新日：21/06/09
DROP TRIGGER IF EXISTS upd_project;

CREATE TRIGGER upd_project AFTER UPDATE ON project FOR EACH ROW
BEGIN

  DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id INT;

  -- 基幹システムからの更新の場合
  IF new.register_type = 0 THEN
  
    -- 顧客情報の取得
    SELECT ccu.customer_id,ccu.customer_branch_id, ccu.customer_user_id
    INTO _customer_id, _customer_branch_id, _customer_user_id
    FROM cu_customer_user ccu
     INNER JOIN customer_user cu on ccu.core_id = cu.id
    WHERE cu.customer_user_natural_id = new.customer_user_natural_id;

    -- ランドマーク支店情報の取得
    SELECT branch_id
    INTO _branch_id
    FROM cu_branch
    WHERE core_id = new.user_branch_id;

    -- すでに連携済みのデータが存在する場合は更新を行う
    IF EXISTS ( SELECT 1 FROM cu_project WHERE core_id = new.id ) THEN
  
      UPDATE cu_project
        SET 
         customer_id              = _customer_id                           -- 顧客会社ID
        ,customer_branch_id       = _customer_branch_id                    -- 顧客支店ID
        ,customer_user_id         = _customer_user_id                      -- 顧客担当者ID
        ,branch_id                = _branch_id                             -- 支店ID
        ,construction_number      = new.construction_number                -- 工事番号
        ,site_name                = new.site_name                          -- 現場名／邸名
        ,site_name_kana           = new.site_name_kana                     -- 現場名／邸名：カナ
        ,site_prefecture          = new.site_prefecture                    -- 都道府県コード 
        ,site_city                = new.site_city                          -- 市区町村名 
        ,site_address             = new.site_address                       -- 番地（町名以降） 
        ,latitude                 = new.latitude                           -- 緯度 
        ,longitude                = new.longitude                          -- 経度 
        ,update_system_type       = 1                                      -- 固定値：基幹システム
        ,update_user_id           = 0                                      -- 固定値：システム自動連携
        ,status                   = ISNULL( new.delete_timestamp )         -- ステータス
      WHERE core_id = new.id
      ;

    -- 連携済みのデータが存在しない場合は登録を行う
    ELSE
  
      INSERT INTO cu_project( 
        core_id
      , customer_id
      , customer_branch_id
      , customer_user_id
      , branch_id
      , construction_number
      , site_name
      , site_name_kana
      , site_prefecture
      , site_city
      , site_address
      , latitude
      , longitude
      , create_system_type
      , create_user_id
      , status
      ) 
      VALUES (
        new.id                                    -- 基幹システム連携ID
      , _customer_id                              -- 顧客会社ID
      , _customer_branch_id                       -- 顧客支店ID
      , _customer_user_id                         -- 顧客担当者ID
      , _branch_id                                -- 支店ID
      , new.construction_number                   -- 工事番号
      , new.site_name                             -- 現場名／邸名
      , new.site_name_kana                        -- 現場名／邸名：カナ
      , new.site_prefecture                       -- 都道府県コード
      , new.site_city                             -- 市区町村名
      , new.site_address                          -- 番地（町名以降）
      , new.latitude                              -- 緯度
      , new.longitude                             -- 経度
      , 1                                         -- 固定値：基幹システム
      , 0                                         -- 固定値：システム自動連携
      , ISNULL( new.delete_timestamp )            -- ステータス 
      );
    END IF;
  END IF;
END;

 -- トリガー： 依頼（登録） 更新日：21/06/09
DROP TRIGGER IF EXISTS ins_request;

CREATE TRIGGER ins_request AFTER INSERT ON request FOR EACH ROW
BEGIN

  DECLARE _project_id int;
  DECLARE 
    _subcontract_name
  , _subcontract_kana
  , _subcontract_branch_name
  , _subcontract_branch_kana
  , _subcontract_user_division_name
  , _subcontract_user_name
  , _subcontract_user_kana varchar(255);
  DECLARE _subcontract_user_email varchar(2048);
  DECLARE 
    _subcontract_user_tel
  , _subcontract_user_fax varchar(13);
  DECLARE _subcontract_reminder_sms_flag BOOL;

  -- 基幹システムからの更新の場合
  IF new.register_type = 0 THEN

    -- 工事情報の取得
    SELECT  project_id
    INTO   _project_id
    FROM   cu_project
    WHERE  core_id = new.project_id;
 
    -- 下請情報の取得
    SELECT  
      c.customer_name
     ,c.customer_name_kana
     ,cb.customer_branch_name
     ,cb.customer_branch_name_kana
     ,cu.customer_user_division_name
     ,cu.customer_user_name
     ,cu.customer_user_name_kana
     ,cu.customer_user_email
     ,cu.customer_user_tel
     ,cb.customer_branch_fax
     ,cu.customer_reminder_sms_flag
    INTO   
       _subcontract_name
     , _subcontract_kana
     , _subcontract_branch_name
     , _subcontract_branch_kana
     , _subcontract_user_division_name
     , _subcontract_user_name
     , _subcontract_user_kana
     , _subcontract_user_email
     , _subcontract_user_tel
     , _subcontract_user_fax
     , _subcontract_reminder_sms_flag
    FROM   project p
       INNER JOIN customer c ON p.subcontract_customer_id = c.id
       INNER JOIN customer_branch cb ON p.subcontract_customer_branch_id = cb.id
       INNER JOIN customer_user cu ON p.subcontract_customer_user_id = cu.id
    WHERE  p.id = new.project_id;

    -- 見積依頼（登録）
    INSERT INTO cu_request( 
      project_id
    , core_id
    , request_date
    , estimate_deadline
    , request_type
    , want_start_date
    , want_end_date
    , car_qty
    , light_truck_qty
    , truck_qty
    , other_car_qty
    , other_car_detail
    , want_guide_type
    , cc_email
    , response_request_date
    , customer_other_request
    , request_other_deadline
    , request_other_start_date
    , request_other_end_date
    , request_other_qty
    , request_status
    , subcontract_want_guide_type
    , subcontract_name
    , subcontract_kana
    , subcontract_branch_name
    , subcontract_branch_kana
    , subcontract_user_division_name
    , subcontract_user_name
    , subcontract_user_kana
    , subcontract_user_email
    , subcontract_user_tel
    , subcontract_user_fax
    , subcontract_reminder_sms_flag
    , create_system_type
    , create_user_id
    , status
    ) 
    VALUES (
      _project_id                                   -- 工事ID
    , new.id                                        -- 基幹システム連携ID
    , new.request_date                              -- 依頼受付日
    , new.estimate_deadline                         -- 見積提出期限
    , new.request_type                              -- 依頼種別
    , new.want_start_date                           -- 利用期間：開始
    , new.want_end_date                             -- 利用期間：終了
    , new.car_qty                                   -- 台数：乗用車（軽自動車・ハイエース等）
    , new.light_truck_qty                           -- 台数：軽トラック
    , new.truck_qty                                 -- 台数：2ｔトラック
    , new.other_car_qty                             -- 台数：その他（計）
    , new.other_car_detail                          -- その他詳細
    , new.want_guide_type                           -- 案内方法
    , new.cc_email                                  -- 顧客が指定するCCメールアドレス
    , new.estimate_deadline                         -- 顧客からの要望日
    , new.customer_other_request                    -- 顧客からの要望など
    , new.request_other_deadline                    -- 着手期限日
    , new.request_other_start_date                  -- 契約開始日
    , new.request_other_end_date                    -- 契約終了日
    , new.request_other_qty                         -- 個数
    , new.survey_status                             -- 見積依頼ステータス／調査ステータス
    , new.want_guide_type_subcontract               -- 案内方法：下請用
    , _subcontract_name                             -- 下請顧客会社名
    , _subcontract_kana                             -- 下請顧客会社名：カナ
    , _subcontract_branch_name                      -- 下請顧客支店名
    , _subcontract_branch_kana                      -- 下請顧客支店名：カナ
    , _subcontract_user_division_name               -- 下請顧客部署名
    , _subcontract_user_name                        -- 下請顧客担当者名
    , _subcontract_user_kana                        -- 下請顧客担当者名：カナ
    , _subcontract_user_email                       -- 下請顧客担当者メールアドレス
    , _subcontract_user_tel                         -- 下請顧客担当者携帯番号
    , _subcontract_user_fax                         -- 下請顧客担当者FAX番号
    , _subcontract_reminder_sms_flag                -- 下請顧客担当者SMSリマインド有無
    , 1                                             -- 固定値：基幹システム
    , 0                                             -- 固定値：システム自動連携
    , ISNULL( new.delete_timestamp )                -- ステータス 
    );
  END IF;
END;

-- トリガー： 見積依頼（更新） 更新日：21/06/13
DROP TRIGGER IF EXISTS upd_request;

CREATE TRIGGER upd_request AFTER UPDATE ON request FOR EACH ROW
BEGIN

  DECLARE _project_id int;
  DECLARE 
    _subcontract_name
  , _subcontract_kana
  , _subcontract_branch_name
  , _subcontract_branch_kana
  , _subcontract_user_division_name
  , _subcontract_user_name
  , _subcontract_user_kana varchar(255);
  DECLARE _subcontract_user_email varchar(2048);
  DECLARE 
    _subcontract_user_tel
  , _subcontract_user_fax varchar(13);
  DECLARE _subcontract_reminder_sms_flag BOOL;

  -- 基幹システムからの更新の場合
  IF new.register_type = 0 THEN
  
    -- 工事情報の取得
    SELECT  project_id
    INTO   _project_id
    FROM   cu_project
    WHERE  core_id = new.project_id;
  
    -- 下請情報の取得
    SELECT  
      c.customer_name
     ,c.customer_name_kana
     ,cb.customer_branch_name
     ,cb.customer_branch_name_kana
     ,cu.customer_user_division_name
     ,cu.customer_user_name
     ,cu.customer_user_name_kana
     ,cu.customer_user_email
     ,cu.customer_user_tel
     ,cb.customer_branch_fax
     ,cu.customer_reminder_sms_flag
    INTO   
       _subcontract_name
     , _subcontract_kana
     , _subcontract_branch_name
     , _subcontract_branch_kana
     , _subcontract_user_division_name
     , _subcontract_user_name
     
     , _subcontract_user_kana
     , _subcontract_user_email
     , _subcontract_user_tel
     , _subcontract_user_fax
     , _subcontract_reminder_sms_flag
    FROM   project p
       INNER JOIN customer c ON p.subcontract_customer_id = c.id
       INNER JOIN customer_branch cb ON p.subcontract_customer_branch_id = cb.id
       INNER JOIN customer_user cu ON p.subcontract_customer_user_id = cu.id
    WHERE  p.id = new.project_id;

    -- すでに連携済みのデータが存在する場合は更新を行う
    IF EXISTS ( SELECT 1 FROM cu_request WHERE core_id = new.id ) THEN

      UPDATE cu_request
      SET 
        request_date                       = new.request_date                       -- 依頼受付日
      , estimate_deadline                  = new.estimate_deadline                  -- 見積提出期限
      , request_type                       = new.request_type                       -- 依頼種別
      , want_start_date                    = new.want_start_date                    -- 利用期間：開始
      , want_end_date                      = new.want_end_date                      -- 利用期間：終了
      , car_qty                            = new.car_qty                            -- 台数：乗用車（軽自動車・ハイエース等）
      , light_truck_qty                    = new.light_truck_qty                    -- 台数：軽トラック
      , truck_qty                          = new.truck_qty                          -- 台数：2ｔトラック
      , other_car_qty                      = new.other_car_qty                      -- 台数：その他（計）
      , other_car_detail                   = new.other_car_detail                   -- その他詳細
      , want_guide_type                    = new.want_guide_type                    -- 案内方法
      , cc_email                           = new.cc_email                           -- 顧客が指定するCCメールアドレス
      , response_request_date              = new.estimate_deadline                  -- 顧客からの要望日
      , customer_other_request             = new.customer_other_request             -- 顧客からの要望など
      , request_other_deadline             = new.request_other_deadline             -- 着手期限日
      , request_other_start_date           = new.request_other_start_date           -- 契約開始日
      , request_other_end_date             = new.request_other_end_date             -- 契約終了日
      , request_other_qty                  = new.request_other_qty                  -- 個数
      , request_status                     = new.survey_status                      -- 見積依頼ステータス／調査ステータス
      , subcontract_want_guide_type        = new.want_guide_type_subcontract        -- 案内方法：下請用
      , subcontract_name                   = _subcontract_name                      -- 下請顧客会社名
      , subcontract_kana                   = _subcontract_kana                      -- 下請顧客会社名：カナ
      , subcontract_branch_name            = _subcontract_branch_name               -- 下請顧客支店名
      , subcontract_branch_kana            = _subcontract_branch_kana               -- 下請顧客支店名：カナ
      , subcontract_user_division_name     = _subcontract_user_division_name        -- 下請顧客部署名
      , subcontract_user_name              = _subcontract_user_name                 -- 下請顧客担当者名
      , subcontract_user_kana              = _subcontract_user_kana                 -- 下請顧客担当者名：カナ
      , subcontract_user_email             = _subcontract_user_email                -- 下請顧客担当者メールアドレス
      , subcontract_user_tel               = _subcontract_user_tel                  -- 下請顧客担当者携帯番号
      , subcontract_user_fax               = _subcontract_user_fax                  -- 下請顧客担当者FAX番号
      , subcontract_reminder_sms_flag      = _subcontract_reminder_sms_flag         -- 下請顧客担当者SMSリマインド有無
      ,update_system_type                  = 1                                      -- 固定値：基幹システム
      ,update_user_id                      = 0                                      -- 固定値：システム自動連携
      ,status                               = ISNULL( new.delete_timestamp )        -- ステータス
      WHERE core_id = new.id
      ;
 
    -- 連携済みのデータが存在しない場合は登録を行う
    ELSE

      -- 見積依頼（登録）
      INSERT INTO cu_request( 
        project_id
      , core_id
      , request_date
      , estimate_deadline
      , request_type
      , want_start_date
      , want_end_date
      , car_qty
      , light_truck_qty
      , truck_qty
      , other_car_qty
      , other_car_detail
      , want_guide_type
      , cc_email
      , response_request_date
      , customer_other_request
      , request_other_deadline
      , request_other_start_date
      , request_other_end_date
      , request_other_qty
      , request_status
      , subcontract_want_guide_type
      , subcontract_name
      , subcontract_kana
      , subcontract_branch_name
      , subcontract_branch_kana
      , subcontract_user_division_name
      , subcontract_user_name
      , subcontract_user_kana
      , subcontract_user_email
      , subcontract_user_tel
      , subcontract_user_fax
      , subcontract_reminder_sms_flag
      , create_system_type
      , create_user_id
      , status
      ) 
      VALUES (
        _project_id                                   -- 工事ID
      , new.id                                        -- 基幹システム連携ID
      , new.request_date                              -- 依頼受付日
      , new.estimate_deadline                         -- 見積提出期限
      , new.request_type                              -- 依頼種別
      , new.want_start_date                           -- 利用期間：開始
      , new.want_end_date                             -- 利用期間：終了
      , new.car_qty                                   -- 台数：乗用車（軽自動車・ハイエース等）
      , new.light_truck_qty                           -- 台数：軽トラック
      , new.truck_qty                                 -- 台数：2ｔトラック
      , new.other_car_qty                             -- 台数：その他（計）
      , new.other_car_detail                          -- その他詳細
      , new.want_guide_type                           -- 案内方法
      , new.cc_email                                  -- 顧客が指定するCCメールアドレス
      , new.estimate_deadline                         -- 顧客からの要望日
      , new.customer_other_request                    -- 顧客からの要望など
      , new.request_other_deadline                    -- 着手期限日
      , new.request_other_start_date                  -- 契約開始日
      , new.request_other_end_date                    -- 契約終了日
      , new.request_other_qty                         -- 個数
      , new.survey_status                             -- 見積依頼ステータス／調査ステータス
      , new.want_guide_type_subcontract               -- 案内方法：下請用
      , _subcontract_name                             -- 下請顧客会社名
      , _subcontract_kana                             -- 下請顧客会社名：カナ
      , _subcontract_branch_name                      -- 下請顧客支店名
      , _subcontract_branch_kana                      -- 下請顧客支店名：カナ
      , _subcontract_user_division_name               -- 下請顧客部署名
      , _subcontract_user_name                        -- 下請顧客担当者名
      , _subcontract_user_kana                        -- 下請顧客担当者名：カナ
      , _subcontract_user_email                       -- 下請顧客担当者メールアドレス
      , _subcontract_user_tel                         -- 下請顧客担当者携帯番号
      , _subcontract_user_fax                         -- 下請顧客担当者FAX番号
      , _subcontract_reminder_sms_flag                -- 下請顧客担当者SMSリマインド有無
      , 1                                             -- 固定値：基幹システム
      , 0                                             -- 固定値：システム自動連携
      , ISNULL( new.delete_timestamp )                -- ステータス 
      );
    END IF;
  END IF;
END;

 -- トリガー： 見積（登録） 更新日：21/06/10
DROP TRIGGER IF EXISTS ins_estimate;

CREATE TRIGGER ins_estimate AFTER INSERT ON estimate FOR EACH ROW
BEGIN

 DECLARE _request_id, _project_id, _parking_id, _branch_id, _estimate_id int;
 DECLARE _request_status smallint;
 DECLARE _parking_name, _parking_name_kana varchar(255);
 
 -- 見積依頼情報の取得
 SELECT
   request_id
 , project_id
 , request_status
 INTO
   _request_id
 , _project_id
 , _request_status
 FROM cu_request
 WHERE core_id = new.request_id;

 -- 調査見積送付済みの場合のみ、見積データを作成する
 -- 駐車場以外の見積の場合を後日検討
 IF _request_status = 3 THEN
 
  -- 駐車場情報の取得
   SELECT
        parking_id
      , parking_name
      , parking_name_kana
   INTO
        _parking_id
      , _parking_name
      , _parking_name_kana
   FROM cu_parking
   WHERE core_id = new.parking_id;

  -- 支店情報の取得
   SELECT
      branch_id
   INTO
     _branch_id
   FROM cu_branch
   WHERE core_id = new.user_branch_id;
   
  -- 見積データの作成
  INSERT INTO cu_estimate( 
      core_id
    , request_id
    , project_id
    , parking_id
    , branch_id
    , estimate_status
    , estimate_expire_date
    , estimate_cancel_check_flag
    , estimate_cancel_check_flag
    , survey_parking_name
    , survey_capacity_qty
    , survey_site_distance_minute
    , survey_site_distance_meter
    , survey_tax_in_flag
    , survey_total_amt
    , create_system_type
    , create_user_id
    , status
  ) 
  VALUES (
      new.id                                        -- 基幹システム連携ID
    , _request_id                                   -- 見積依頼ID
    , _project_id                                   -- 工事ID
    , _parking_id                                   -- 駐車場ID
    , _branch_id                                    -- 支店ID
    , case                                          -- 見積ステータス
        when new.estimate_status = 3 then 1           -- 3:調査見積送付済み → 1: 受注待ち
        when new.estimate_status = 4 then 4           -- 4:受注 → 4:受注
        when new.estimate_status = 5 then 5           -- 5:確定見積送付済 → 5:確定見積送付済         
        when new.estimate_status = 7 then 7           -- 7:キャンセル → 7:キャンセル
        when new.estimate_status = 8 then 8           -- 8:失注（手動） → 8:キャンセル
        when new.estimate_status = 9 then 9           -- 9:失注（自動） → 9:キャンセル                                 
        else 0                                        -- 0:未作成 
      end
    , new.estimate_expire_date                      -- 見積有効期限日
    , new.estimate_cancel_check_flag                -- 見積キャンセル確認フラグ
    , new.estimate_cancel_check_date                -- 見積キャンセル確認日
    , new.survey_parking_name                       -- 調査見積駐車場情報：駐車場名
    , new.survey_capacity_qty                       -- 調査見積見積情報：見積台数
    , new.survey_site_distance_minute               -- 調査見積見積情報：現場距離（分）
    , new.survey_site_distance_meter                -- 調査見積見積情報：現場距離（メートル）
    , new.survey_tax_in_flag                        -- 調査見積見積金額：税込みフラグ
    , new.survey_total_amt                          -- 調査見積見積金額：見積合計
    , 1                                             -- 固定値：基幹システム
    , 0                                             -- 固定値：システム自動連携
    , ISNULL( new.delete_timestamp )                -- ステータス 
  );
   
  -- 契約データの作成
  -- 契約データの作成前でキャンセルの場合は契約データは作成しない
  IF new.estimate_status = 5 THEN
  
    -- 見積IDの取得
    SELECT estimate_id
    INTO   _estimate_id
    FROM   cu_estimate
    WHERE  core_id = new.id;
    
    INSERT INTO cu_contract( 
      core_id
    , project_id
    , estimate_id
    , parking_id
    , branch_id
    , contract_status
    , parking_name
    , parking_name_kana
    , quote_capacity_qty
    , quote_subtotal_amt
    , quote_tax_amt
    , quote_total_amt
    , purchase_order_upload_date
    , purchase_order_register_type
    , purchase_order_check_flag
    , order_schedule_date
    , order_process_date
    , quote_available_start_date
    , quote_available_end_date
    , extension_type
    , create_system_type
    , create_user_id
    , status
    )
    VALUES (
      new.id                                        -- 基幹システム連携ID
    , _project_id                                   -- 工事ID
    , _estimate_id                                  -- 見積ID
    , _parking_id                                   -- 駐車場ID
    , _branch_id                                    -- 支店ID
    , 2                                             -- 契約ステータス  2:契約書準備中
    , _parking_name                                 -- 駐車場名
    , _parking_name_kana                            -- 駐車場名：カナ
    , new.quote_capacity_qty                        -- 確定見積台数
    , new.quote_subtotal_amt                        -- 確定見積：見積小計
    , new.quote_tax_amt                             -- 確定見積：税額
    , new.quote_total_amt                           -- 確定見積：合計額
    , new.purchase_order_upload_date                -- 発注書アップロード日
    , new.purchase_order_register_type              -- 発注書アップロードシステム
    , new.purchase_order_check_flag                 -- 発注書確認フラグ
    , new.order_schedule_date                       -- 受注予定日
    , new.order_process_date                        -- 受注処理日
    , new.quote_available_start_date                -- 契約開始日
    , new.quote_available_end_date                  -- 契約終了日
    , 0                                           -- 契約延長区分
    , 1                                           -- 固定値：基幹システム
    , 0                                           -- 固定値：システム自動連携
    , ISNULL( new.delete_timestamp )              -- ステータス 
    );

  END IF; 
 END IF;
END;

-- トリガー： 見積依頼（更新） 更新日：21/06/10
DROP TRIGGER IF EXISTS upd_estimate;

CREATE TRIGGER upd_estimate AFTER UPDATE ON estimate FOR EACH ROW
BEGIN

 DECLARE _request_id, _project_id, _parking_id, _branch_id, _estimate_id int;
 DECLARE _request_status smallint;
 DECLARE _parking_name, _parking_name_kana varchar(255);

  IF new.lastupdate_user_id <> 1 THEN

 -- 見積依頼情報の取得
 SELECT
   request_id
 , project_id
 , request_status
 INTO
   _request_id
 , _project_id
 , _request_status
 FROM cu_request
 WHERE core_id = new.request_id;
 
 -- 駐車場情報の取得
 SELECT
      parking_id
    , parking_name
    , parking_name_kana
 INTO
      _parking_id
    , _parking_name
    , _parking_name_kana
 FROM cu_parking
 WHERE core_id = new.parking_id;

 -- 支店情報の取得
 SELECT
    branch_id
 INTO
   _branch_id
 FROM cu_branch
 WHERE core_id = new.user_branch_id;
 
 -- 見積IDの取得
 SELECT estimate_id
 INTO   _estimate_id
 FROM   cu_estimate
 WHERE  core_id = new.id;

   -- すでに連携済みのデータが存在する場合は更新を行う
 IF EXISTS ( SELECT 1 FROM cu_estimate WHERE core_id = new.id ) THEN

   -- 見積データを更新
   UPDATE cu_estimate
   SET
      request_id                  = _request_id
    , project_id                  = _project_id
    , parking_id                  = _parking_id
    , branch_id                   = _branch_id
    , estimate_status             = 
       case                                          -- 見積ステータス
        when new.estimate_status = 3 then 1           -- 3:調査見積送付済み → 1: 受注待ち
        when new.estimate_status = 4 then 4           -- 4:受注 → 4:受注
        when new.estimate_status = 5 then 5           -- 5:確定見積送付済 → 5:確定見積送付済         
        when new.estimate_status = 7 then 7           -- 7:キャンセル  → 7:キャンセル
        when new.estimate_status = 8 then 8           -- 8:失注（手動）→ 8:キャンセル
        when new.estimate_status = 9 then 9           -- 9:失注（自動）→ 9:キャンセル                          
        else estimate_status                          -- 変更なし 
       end
    , estimate_expire_date        = new.estimate_expire_date  
    , estimate_cancel_check_flag  = new.estimate_cancel_check_flag
    , estimate_cancel_check_flag  = new.estimate_cancel_check_date
    , survey_parking_name         = new.survey_parking_name
    , survey_capacity_qty         = new.survey_capacity_qty 
    , survey_site_distance_minute = new.survey_site_distance_minute
    , survey_site_distance_meter  = new.survey_site_distance_meter 
    , survey_tax_in_flag          = new.survey_tax_in_flag
    , survey_total_amt            = new.survey_total_amt
    , create_system_type          = 1
    , create_user_id              = 0
    , status                      = ISNULL( new.delete_timestamp )
   WHERE core_id = new;

   -- 契約データが存在する場合  
   IF EXISTS ( SELECT 1 FROM cu_contract WHERE core_id = new.id ) THEN

     -- 契約データを更新
     UPDATE cu_contract
     SET
       project_id                      = _project_id
     , estimate_id                     = _estimate_id
     , parking_id                      = _parking_id
     , branch_id                       = _branch_id
     , contract_status                 =
        case
          when new.estimate_status = 7 then 7
          else contract_status
        end
     , parking_name                    = _parking_name
     , parking_name_kana               = _parking_name_kana
     , quote_capacity_qty              = new.quote_capacity_qty
     , quote_subtotal_amt              = new.quote_subtotal_amt
     , quote_tax_amt                   = new.quote_tax_amt
     , quote_total_amt                 = new.quote_total_amt  
     , purchase_order_upload_date      = new.purchase_order_upload_date
     , purchase_order_register_type    = new.purchase_order_register_type
     , purchase_order_check_flag       = new.purchase_order_check_flag
     , order_schedule_date             = new.order_schedule_date
     , order_process_date              = new.order_process_date 
     , quote_available_start_date      = new.quote_available_start_date  
     , quote_available_end_date        = new.quote_available_end_date  
     , extension_type                  = extension_type
     , create_system_type              = 1
     , create_user_id                  = 0
     , status                          = ISNULL( new.delete_timestamp )
     WHERE core_id = new.id;

   -- 契約データが存在しない場合 
   ELSE

     -- 契約データの作成
     -- 契約データの作成前でキャンセルの場合は契約データは作成しない
     IF new.estimate_status = 5 THEN

      INSERT INTO cu_contract( 
        core_id
      , project_id
      , estimate_id
      , parking_id
      , branch_id
      , contract_status
      , parking_name
      , parking_name_kana
      , quote_capacity_qty
      , quote_subtotal_amt
      , quote_tax_amt
      , quote_total_amt
      , purchase_order_upload_date
      , purchase_order_register_type
      , purchase_order_check_flag
      , order_schedule_date
      , order_process_date
      , quote_available_start_date
      , quote_available_end_date
      , extension_type
      , create_system_type
      , create_user_id
      , status
      )
      VALUES (
        new.id                                        -- 基幹システム連携ID
      , _project_id                                   -- 工事ID
      , _estimate_id                                  -- 見積ID
      , _parking_id                                   -- 駐車場ID
      , _branch_id                                    -- 支店ID
      , 2                                             -- 契約ステータス  2:契約書準備中
      , _parking_name                                 -- 駐車場名
      , _parking_name_kana                            -- 駐車場名：カナ
      , new.quote_capacity_qty                        -- 確定見積台数
      , new.quote_subtotal_amt                        -- 確定見積：見積小計
      , new.quote_tax_amt                             -- 確定見積：税額
      , new.quote_total_amt                           -- 確定見積：合計額
      , new.purchase_order_upload_date                -- 発注書アップロード日
      , new.purchase_order_register_type              -- 発注書アップロードシステム
      , new.purchase_order_check_flag                 -- 発注書確認フラグ
      , new.order_schedule_date                       -- 受注予定日
      , new.order_process_date                        -- 受注処理日
      , new.quote_available_start_date                -- 契約開始日
      , new.quote_available_end_date                  -- 契約終了日
      , 0                                           -- 契約延長区分
      , 1                                           -- 固定値：基幹システム
      , 0                                           -- 固定値：システム自動連携
      , ISNULL( new.delete_timestamp )              -- ステータス 
      );

     END IF; 
   END IF;
 ELSE
 
   -- 調査見積送付済みの場合のみ、見積データを作成する
   -- 駐車場以外の見積の場合を後日検討
   IF _request_status = 3 THEN

   -- 見積データの作成
   INSERT INTO cu_estimate( 
      core_id
    , request_id
    , project_id
    , parking_id
    , branch_id
    , estimate_status
    , estimate_expire_date
    , estimate_cancel_check_flag
    , estimate_cancel_check_flag
    , survey_parking_name
    , survey_capacity_qty
    , survey_site_distance_minute
    , survey_site_distance_meter
    , survey_tax_in_flag
    , survey_total_amt
    , create_system_type
    , create_user_id
    , status
   ) 
   VALUES (
      new.id                                        -- 基幹システム連携ID
    , _request_id                                   -- 見積依頼ID
    , _project_id                                   -- 工事ID
    , _parking_id                                   -- 駐車場ID
    , _branch_id                                    -- 支店ID
    , case                                          -- 見積ステータス
        when new.estimate_status = 3 then 1           -- 3:調査見積送付済み → 1: 受注待ち
        when new.estimate_status = 4 then 4           -- 4:受注 → 4:受注
        when new.estimate_status = 5 then 5           -- 5:確定見積送付済 → 5:確定見積送付済         
        when new.estimate_status = 7 then 7           -- 7:キャンセル → 7:キャンセル                          
        else 0                                        -- 0:未作成 
      end
    , new.estimate_expire_date                      -- 見積有効期限日
    , new.estimate_cancel_check_flag                -- 見積キャンセル確認フラグ
    , new.estimate_cancel_check_date                -- 見積キャンセル確認日
    , new.survey_parking_name                       -- 調査見積駐車場情報：駐車場名
    , new.survey_capacity_qty                       -- 調査見積見積情報：見積台数
    , new.survey_site_distance_minute               -- 調査見積見積情報：現場距離（分）
    , new.survey_site_distance_meter                -- 調査見積見積情報：現場距離（メートル）
    , new.survey_tax_in_flag                        -- 調査見積見積金額：税込みフラグ
    , new.survey_total_amt                          -- 調査見積見積金額：見積合計
    , 1                                             -- 固定値：基幹システム
    , 0                                             -- 固定値：システム自動連携
    , ISNULL( new.delete_timestamp )                -- ステータス 
   );
   
   -- 契約データの作成
   -- 契約データの作成前でキャンセルの場合は契約データは作成しない
   IF new.estimate_status = 5 THEN

    INSERT INTO cu_contract( 
      core_id
    , project_id
    , estimate_id
    , parking_id
    , branch_id
    , contract_status
    , parking_name
    , parking_name_kana
    , quote_capacity_qty
    , quote_subtotal_amt
    , quote_tax_amt
    , quote_total_amt
    , purchase_order_upload_date
    , purchase_order_register_type
    , purchase_order_check_flag
    , order_schedule_date
    , order_process_date
    , quote_available_start_date
    , quote_available_end_date
    , extension_type
    , create_system_type
    , create_user_id
    , status
    )
    VALUES (
      new.id                                        -- 基幹システム連携ID
    , _project_id                                   -- 工事ID
    , _estimate_id                                  -- 見積ID
    , _parking_id                                   -- 駐車場ID
    , _branch_id                                    -- 支店ID
    , 2                                             -- 契約ステータス  2:契約書準備中
    , _parking_name                                 -- 駐車場名
    , _parking_name_kana                            -- 駐車場名：カナ
    , new.quote_capacity_qty                        -- 確定見積台数
    , new.quote_subtotal_amt                        -- 確定見積：見積小計
    , new.quote_tax_amt                             -- 確定見積：税額
    , new.quote_total_amt                           -- 確定見積：合計額
    , new.purchase_order_upload_date                -- 発注書アップロード日
    , new.purchase_order_register_type              -- 発注書アップロードシステム
    , new.purchase_order_check_flag                 -- 発注書確認フラグ
    , new.order_schedule_date                       -- 受注予定日
    , new.order_process_date                        -- 受注処理日
    , new.quote_available_start_date                -- 契約開始日
    , new.quote_available_end_date                  -- 契約終了日
    , 0                                           -- 契約延長区分
    , 1                                           -- 固定値：基幹システム
    , 0                                           -- 固定値：システム自動連携
    , ISNULL( new.delete_timestamp )              -- ステータス 
    );

       END IF;
     END IF; 
   END IF;
 END IF;
END;

 -- トリガー： 売掛金（登録） 更新日：21/05/04
DROP TRIGGER IF EXISTS ins_accounts_receivable;

-- ★課題 基幹システム 請求書との紐づけ
CREATE TRIGGER ins_accounts_receivable AFTER INSERT ON accounts_receivable FOR EACH ROW
BEGIN

DECLARE _project_id, _contract_id, _customer_id, _customer_branch_id, _customer_user_id, _parking_id int;

-- 見積情報および工事情報の取得


SELECT 
   c.project_id
  ,c.contract_id
  ,p.customer_id
  ,p.customer_branch_id
  ,p.customer_user_id
  ,c.parking_id
INTO
   _project_id
  ,_contract_id
  ,_customer_id
  ,_customer_branch_id
  ,_customer_user_id
  ,_parking_id
FROM cu_contract c
  INNER JOIN cu_project p ON p.project_id = c.project_id
WHERE core_id = new.estimate_id;

INSERT INTO cu_invoice( 
      core_id
    , project_id
    , contract_id
    , customer_id
    , customer_branch_id
    , customer_user_id
    , parking_id
    , invoice_amt
    , invoice_closing_date
    , payment_deadline
    , receivable_collect_total_amt
    , receivable_collect_finish_date
    , payment_status
    , reminder
    , create_system_type
    , create_user_id
    , status
) 
VALUES (
      new.id                                        -- 基幹システム連携ID
    , _project_id                                   -- 工事ID
    , _contract_id                                  -- 契約ID
    , _customer_id                                  -- 顧客会社ID
    , _customer_branch_id                           -- 顧客支店ID
    , _customer_user_id                             -- 顧客担当者ID
    , _parking_id                                   -- 駐車場ID
    , new.invoice_amt                               -- 請求金額
    , new.invoice_closing_date                      -- 請求書発行日
    , new.payment_deadline                          -- 支払期限日
    , new.receivable_collect_total_amt              -- 入金済金額
    , new.receivable_collect_finish_date            -- 支払完了日
    , new.accounts_receivable_status                -- 支払ステータス
    , new.payment_urge_flag                         -- 督促フラグ
    , 1                                             -- 固定値：基幹システム
    , 0                                             -- 固定値：システム自動連携
    , ISNULL( new.delete_timestamp )                -- ステータス 
    );

END;

 -- トリガー： 売掛金（更新） 更新日：21/05/04
DROP TRIGGER IF EXISTS upd_accounts_receivable;

CREATE TRIGGER upd_accounts_receivable AFTER UPDATE ON accounts_receivable FOR EACH ROW
BEGIN

 DECLARE _project_id, _contract_id, _customer_id, _customer_branch_id, _customer_user_id, _parking_id int;

 -- 見積情報および工事情報の取得
 SELECT 
   c.project_id
  ,c.contract_id
  ,p.customer_id
  ,p.customer_branch_id
  ,p.customer_user_id
  ,c.parking_id
 INTO
   _project_id
  ,_contract_id
  ,_customer_id
  ,_customer_branch_id
  ,_customer_user_id
  ,_parking_id
 FROM cu_contract c
  INNER JOIN cu_project p ON p.project_id = c.project_id
 WHERE core_id = new.estimate_id;

 -- すでに連携済みのデータが存在する場合は更新を行う
 IF EXISTS ( SELECT 1 FROM cu_estimate WHERE core_id = new.id ) THEN

  UPDATE cu_invoice
  SET
      project_id                       = _project_id
    , contract_id                      = _contract_id
    , customer_id                      = _customer_id
    , customer_branch_id               = _customer_branch_id
    , customer_user_id                 = _customer_user_id
    , parking_id                       = _parking_id
    , invoice_amt                      = new.invoice_amt
    , invoice_closing_date             = new.invoice_closing_date
    , payment_deadline                 = new.payment_deadline
    , receivable_collect_total_amt     = new.receivable_collect_total_amt
    , receivable_collect_finish_date   = new.receivable_collect_finish_date
    , payment_status                   = new.accounts_receivable_status
    , reminder                         = new.payment_urge_flag
    , create_system_type               = 1
    , create_user_id                   = 0
    , status                           = ISNULL( new.delete_timestamp )
  WHERE core_id = new.id;
  
 ELSE
  INSERT INTO cu_invoice( 
      core_id
    , project_id
    , contract_id
    , customer_id
    , customer_branch_id
    , customer_user_id
    , parking_id
    , invoice_amt
    , invoice_closing_date
    , payment_deadline
    , receivable_collect_total_amt
    , receivable_collect_finish_date
    , payment_status
    , reminder
    , create_system_type
    , create_user_id
    , status
 ) 
 VALUES (
      new.id                                        -- 基幹システム連携ID
    , _project_id                                   -- 工事ID
    , _contract_id                                  -- 契約ID
    , _customer_id                                  -- 顧客会社ID
    , _customer_branch_id                           -- 顧客支店ID
    , _customer_user_id                             -- 顧客担当者ID
    , _parking_id                                   -- 駐車場ID
    , new.invoice_amt                               -- 請求金額
    , new.invoice_closing_date                      -- 請求書発行日
    , new.payment_deadline                          -- 支払期限日
    , new.receivable_collect_total_amt              -- 入金済金額
    , new.receivable_collect_finish_date            -- 支払完了日
    , new.accounts_receivable_status                -- 支払ステータス
    , new.payment_urge_flag                         -- 督促フラグ
    , 1                                             -- 固定値：基幹システム
    , 0                                             -- 固定値：システム自動連携
    , ISNULL( new.delete_timestamp )                -- ステータス 
    );
 END IF;
END;


-- トリガー： ファイル（登録） 更新日：21/04/30
DROP TRIGGER IF EXISTS ins_cu_file;

CREATE TRIGGER ins_cu_file BEFORE INSERT ON cu_file FOR EACH ROW
BEGIN

DECLARE _project_id, _request_id, _estimate_id, _contract_id, _invoice_id int;

 -- 工事, メッセージ
 IF ( new.file_type = 1 OR new.file_type = 7 ) THEN

   SELECT project_id
   INTO _project_id
   FROM cu_project
   WHERE 
     case new.create_system_type
       when 1 then core_id            -- 基幹システム
       when 2 then project_id         -- 顧客向けシステム
       else project_id
     end =  new.ref_id;

 -- 見積依頼
 ELSEIF new.file_type = 2  THEN
  
  -- 見積依頼の取得
  SELECT project_id, request_id
  INTO _project_id, _request_id
  FROM cu_request
  WHERE 
     case new.create_system_type
       when 1 then core_id            -- 基幹システム
       when 2 then request_id         -- 顧客向けシステム
       else request_id
     end =  new.ref_id;
  
 -- 見積  ／ 顧客向けシステム
 ELSEIF  new.file_type = 3  THEN
  
  -- 見積の取得
  SELECT project_id, request_id, estimate_id
  INTO _project_id ,_request_id, _estimate_id
  FROM cu_estimate
  WHERE 
     case new.create_system_type
       when 1 then core_id            -- 基幹システム
       when 2 then estimate_id        -- 顧客向けシステム
       else estimate_id
     end =  new.ref_id;

 -- 発注、契約  ／ 顧客向けシステム
 ELSEIF ( new.file_type = 4 OR new.file_type = 5  )  THEN

  -- 契約の取得
  SELECT project_id, estimate_id, contract_id
  INTO _project_id ,_estimate_id, _contract_id
  FROM cu_contract
  WHERE 
     case new.create_system_type
       when 1 then core_id            -- 基幹システム
       when 2 then contract_id        -- 顧客向けシステム
       else contract_id
     end =  new.ref_id;

  -- 見積の取得
  SELECT request_id
  INTO _request_id
  FROM cu_estimate
  WHERE estimate_id = _estimate_id;

 -- 請求  ／ 顧客向けシステム
 ELSEIF  new.file_type = 6   THEN

  -- 請求の取得
  -- ★課題 請求のみ基幹との連携方法見直し
  SELECT project_id, contract_id, invoice_id
  INTO _project_id , _contract_id, _invoice_id
  FROM cu_invoice
  WHERE 
     case new.create_system_type
       when 1 then core_id            -- 基幹システム
       when 2 then invoice_id        -- 顧客向けシステム
       else invoice_id
     end =  new.ref_id;

  -- 契約の取得
  SELECT estimate_id
  INTO  _estimate_id
  FROM cu_contract
  WHERE contract_id = _contract_id;
 
   -- 見積の取得
  SELECT request_id
  INTO _request_id
  FROM cu_estimate
  WHERE estimate_id = _estimate_id;
 
 END IF;

 SET  new.project_id  = _project_id
    , new.request_id  = _request_id
    , new.estimate_id = _estimate_id
    , new.contract_id = _contract_id
    , new.invoice_id  = _invoice_id;

END;

-- トリガー： メッセージ（更新） 更新日：21/04/30
DROP TRIGGER IF EXISTS upd_cu_message;

CREATE TRIGGER upd_cu_message AFTER UPDATE ON cu_message FOR EACH ROW
INSERT INTO cu_message_history
  ( message_id, project_id, body, file_id, edit, already_read, create_date, create_user_id, create_system_type, status )
VALUES (  
  old.message_id, 
  old.project_id, 
  old.body,
  old.file_id, 
  old.edit, 
  old.already_read, 
  old.update_date, 
  old.update_user_id, 
  old.update_system_type, 
  old.status
 );


-- トリガー： 工事情報(登録) 更新日：21/06/09
DROP TRIGGER IF EXISTS ins_cu_project;

CREATE TRIGGER ins_cu_project  BEFORE INSERT ON cu_project FOR EACH ROW
BEGIN

  DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id INT;
  DECLARE _customer_natural_id, _customer_branch_natural_id,  _customer_user_natural_id, _branch_natural_id, _project_natural_id  VARCHAR(255);

  -- 顧客システムによる更新の場合
  IF new.create_system_type = 2 THEN

    -- 顧客、顧客支店、顧客担当者の取得
    SELECT 
      c.customer_id, 
      c.customer_natural_id,
      c.customer_branch_id,
      c.customer_branch_natural_id,
      c.id,
      c.customer_user_natural_id
    INTO 
      _customer_id, 
      _customer_natural_id,
      _customer_branch_id,
      _customer_branch_natural_id,
      _customer_user_id,
      _customer_user_natural_id
    FROM customer_user c
      INNER JOIN cu_customer_user cc ON cc.core_id = c.id
    WHERE cc.customer_user_id = new.customer_user_id;
  
    -- 支店情報を取得
    SELECT 
      id,
      user_branch_natural_id
    INTO 
      _branch_id,
      _branch_natural_id
    FROM user_branch ub
      INNER JOIN cu_branch cb ON cb.core_id = ub.id
      INNER JOIN cu_branch_area a ON a.branch_id = cb.branch_id
    WHERE a.prefecture = new.site_prefecture;

    -- 工事IDの生成
    SELECT CONCAT(substr(current_date,3,2), 'C',  LPAD( count(*)+1, 7, 0))
    INTO _project_natural_id 
    FROM project 
    WHERE substr(current_date,3,2) = substr(project_natural_id,1,2);
  
    -- 工事情報の登録
    INSERT INTO project (
      project_natural_id,
      customer_id,
      customer_natural_id,
      customer_branch_id,
      customer_branch_natural_id,
      customer_user_id,
      customer_user_natural_id,
      lastupdate_user_id,
      user_branch_id,
      user_branch_natural_id,
      create_timestamp,
      update_timestamp,
      ulid,
      construction_number,
      site_name,
      site_name_kana,
      site_prefecture,
      site_city,
      site_address,
      register_type,
      latitude,
      longitude
    )
    VALUES
    (
      _project_natural_id,                                                   -- 工事ID
      _customer_id,                                                          -- 顧客マスターテーブルID
      _customer_natural_id,                                                  -- 顧客ID
      _customer_branch_id,                                                   -- 顧客支店マスターテーブルID
      _customer_branch_natural_id,                                           -- 顧客支店ID
      _customer_user_id,                                                     -- 顧客担当者マスターテーブルID
      _customer_user_natural_id,                                             -- 顧客担当者ID
      1,                                                                     -- システム利用者テーブルID：最終更新者
      _branch_id,                                                            -- ランドマーク支店マスタテーブルID
      _branch_natural_id,                                                    -- ランドマーク支店ID
      CURRENT_TIMESTAMP,                                                     -- レコード作成日
      CURRENT_TIMESTAMP,                                                     -- レコード更新日
      getULID(),                                                             -- ULID
      new.construction_number,                                               -- 工事番号
      new.site_name,                                                         -- 現場名／邸名
      new.site_name_kana,                                                    -- 現場名／邸名：カナ
      new.site_prefecture,                                                   -- 現場所在地：都道府県コード
      new.site_city,                                                         -- 現場所在地：市区町村
      new.site_address,                                                      -- 現場所在地：番地（町名以降）
      1,                                                                     -- データ登録者種別
      new.latitude,                                                          -- 緯度
      new.longitude                                                          -- 経度
    );
  
    -- 連携IDを取得
    SET NEW.core_id = LAST_INSERT_ID();
  END IF;
END;


-- トリガー： 工事情報(更新) 更新日：21/06/09
DROP TRIGGER IF EXISTS upd_cu_project;

CREATE TRIGGER upd_cu_project  BEFORE INSERT ON cu_project FOR EACH ROW
BEGIN

  -- 顧客システムによる更新の場合
  IF new.update_system_type = 2 THEN

    UPDATE project 
    SET
        update_timestamp = CURRENT_TIMESTAMP
      , construction_number = new.construction_number
      , site_name = new.site_name
      , site_name_kana = new.site_name_kana
      , site_prefecture = new.site_prefecture
      , site_city = new.site_city
      , site_address = new.site_address
      , latitude = new.latitude
      , longitude = new.longitude
      , register_type = 1
      , lastupdate_user_id = 1
    WHERE
      id = new.core_id;

  END IF;
END;

-- トリガー： 見積依頼情報(登録) 更新日：21/06/10
DROP TRIGGER IF EXISTS ins_cu_request;

CREATE TRIGGER ins_cu_request  BEFORE INSERT ON cu_request FOR EACH ROW
BEGIN

  DECLARE _customer_id, _customer_branch_id, _customer_user_id, _branch_id, _project_id, _request_cnt INT;
  DECLARE _customer_natural_id, _customer_branch_natural_id,  _customer_user_natural_id, _branch_natural_id, _project_natural_id, _request_natural_id  VARCHAR(255);

  -- 顧客システムによる更新の場合
  IF new.create_system_type = 2 THEN
  
    -- 見積依頼件数の取得（ID生成用）
    SELECT COUNT(*)
    INTO _request_cnt
    FROM cu_request
    WHERE project_id = new.project_id 
      AND request_type = new.request_type;
    
    -- 工事情報の取得
    SELECT 
      p.id,
      p.project_natural_id
    INTO
      _project_id,
      _project_natural_id
    FROM project p
      INNER JOIN cu_project cp ON cp.core_id = p.id 
    WHERE cp.project_id = new.project_id;

    -- 依頼IDの作成  
    SET _request_natural_id = CONCAT( _project_natural_id, '-C', new.request_type, LPAD( _request_cnt,3, '0' ));

    -- 顧客、顧客支店、顧客担当者の取得
    SELECT 
      c.customer_id, 
      c.customer_natural_id,
      c.customer_branch_id,
      c.customer_branch_natural_id,
      c.id,
      c.customer_user_natural_id
    INTO 
      _customer_id, 
      _customer_natural_id,
      _customer_branch_id,
      _customer_branch_natural_id,
      _customer_user_id,
      _customer_user_natural_id
    FROM customer_user c
      INNER JOIN cu_customer_user cc ON cc.core_id = c.id
      INNER JOIN cu_project cp ON cp.customer_user_id = cc.customer_user_id
    WHERE cp.project_id = new.project_id;

    -- 支店情報を取得
    SELECT 
      id,
      user_branch_natural_id
    INTO 
      _branch_id,
      _branch_natural_id
    FROM user_branch ub
      INNER JOIN cu_branch cb ON cb.core_id = ub.id
      INNER JOIN cu_branch_area a ON a.branch_id = cb.branch_id
      INNER JOIN cu_project p ON a.prefecture = p.site_prefecture
    WHERE p.project_id = new.project_id;
  
    -- 調査依頼を作成
    INSERT INTO request( 
        create_timestamp
      , update_timestamp
      , ulid
      , request_natural_id
      , project_natural_id
      , customer_id
      , customer_natural_id
      , customer_branch_id
      , customer_branch_natural_id
      , customer_user_id
      , customer_user_natural_id
      , user_branch_natural_id
      , request_date
      , estimate_deadline
      , request_type
      , want_start_date
      , want_end_date
      , car_qty
      , light_truck_qty
      , truck_qty
      , other_car_qty
      , other_car_detail
      , request_other_deadline
      , request_other_start_date
      , request_other_end_date
      , request_other_qty
      , want_guide_type
      , cc_email
      , customer_other_request
      , survey_status
      , register_type
      , request_cancel_check_flag
      , lastupdate_user_id
      , project_id
      , user_branch_id
    ) VALUES (
        CURRENT_TIMESTAMP                                        -- レコード作成日
      , CURRENT_TIMESTAMP                                        -- レコード更新日
      , getULID()                                                -- ULID
      , _request_natural_id                                      -- 依頼ID
      , _project_natural_id                                      -- 工事ID
      , _customer_id                                             -- 顧客会社マスターテーブルID
      , _customer_natural_id                                     -- 顧客会社ID
      , _customer_branch_id                                      -- 顧客支店マスターテーブルID
      , _customer_branch_natural_id                              -- 顧客支店ID
      , _customer_user_id                                        -- 顧客担当者マスターテーブルID
      , _customer_user_natural_id                                -- 顧客担当者ID
      , _branch_natural_id                                       -- ランドマーク支店ID
      , new.request_date                                         -- 依頼受付日
      , IFNULL( new.response_request_date, getCalcBusinessDay( CURRENT_DATE, 4  ))  -- 見積提出期限／顧客希望が無い場合は4営業日後
      , new.request_type                                         -- 依頼種別
      , new.want_start_date                                      -- 利用期間：開始
      , new.want_end_date                                        -- 利用期間：終了
      , new.car_qty                                              -- 台数：乗用車（軽自動車・ハイエース等） 
      , new.light_truck_qty                                      -- 台数：軽トラック
      , new.truck_qty                                            -- 台数：2ｔトラック
      , new.other_car_qty                                        -- 台数：その他（計）
      , new.other_car_detail                                     -- その他詳細
      , new.request_other_deadline                               -- 着手期限
      , new.request_other_start_date                             -- 契約開始日
      , new.request_other_end_date                               -- 契約終了日
      , new.request_other_qty                                    -- 個数
      , new.want_guide_type                                      -- 案内方法
      , new.cc_email                                             -- 顧客が指定するCCメールアドレス
      , new.customer_other_request                               -- 顧客からの要望等
      , 0                                                        -- 調査ステータス （0:未調査）
      , 1                                                        -- データ登録ユーザー種別（1:顧客登録データ)
      , 0                                                        -- 依頼キャンセル確認フラグ（0:未確認）
      , 1                                                        -- システム利用者テーブルID：最終更新者
      , _project_id                                              -- 工事マスターテーブルID
      , _branch_id                                               -- ランドマーク支店マスターテーブルID
    );

    -- 連携IDを取得
    SET NEW.core_id = LAST_INSERT_ID();

    -- 依頼種別その他の場合の後続処理

  END IF;
END;

-- トリガー： 見積依頼情報(更新) 更新日：21/06/09
DROP TRIGGER IF EXISTS upd_cu_request;

CREATE TRIGGER upd_cu_request  AFTER UPDATE ON cu_request FOR EACH ROW
BEGIN

  -- 顧客システムによる更新の場合
  IF new.update_system_type = 2 THEN
  
    -- 見積依頼ステータスが未調査、Web調査、現地調査までを更新可能とする
    IF old.request_status IN ( 0, 1, 2 )  THEN
  
      UPDATE request
        SET 
          update_timestamp                = CURRENT_TIMESTAMP    -- レコード更新日
        , want_start_date                 = new.want_start_date  -- 利用期間：開始
        , want_end_date                   = new.want_end_date    -- 利用期間：終了
        , car_qty                         = new.car_qty
        , light_truck_qty                 = new.light_truck_qty
        , truck_qty                       = new.truck_qty
        , other_car_qty                   = new.other_car_qty
        , other_car_detail                = new.other_car_detail
        , request_other_deadline          = new.request_other_deadline
        , request_other_start_date        = new.request_other_start_date
        , request_other_end_date          = new.request_other_end_date
        , request_other_qty               = new.request_other_qty
        , want_guide_type                 = new.want_guide_type
        , cc_email                        = new.cc_email
        , customer_other_request          = new.customer_other_request
        , register_type                   = 1
        , request_cancel_date             =
           CASE
             WHEN old.request_status IN ( 0, 1, 2 ) AND new.request_status = 9 THEN CURRENT_DATE
             ELSE NULL
           END                                                 -- 調査途中終了となった場合、キャンセル日付を入れる
        , lastupdate_user_id             = 1
       WHERE id = new.core_id;

    END IF;
  END IF;
END;


-- トリガー： 見積依頼対象駐車場（登録） 更新日：21/06/09
DROP TRIGGER IF EXISTS ins_cu_request_parking;

CREATE TRIGGER ins_cu_request_parking  AFTER INSERT ON cu_request_parking FOR EACH ROW
BEGIN

  DECLARE _parking_id, _request_id, _project_id, _supplier_id, _user_branch_id, _extend_estimate_id
        , _customer_id, _customer_branch_id, _customer_user_id INT;
  DECLARE _project_natural_id, _request_natural_id, supplier_natural_id,  _user_branch_natural_id, _extend_estimate_natural_id
        , _customer_natural_id, _customer_branch_natural_id, _customer_user_natural_id VARCHAR(255);

  -- 駐車場情報の取得
  SELECT cp.core_id, p.supplier_id, p.supplier_natural_id
  INTO _parking_id, _supplier_id, supplier_natural_id
  FROM cu_parking cp
    INNER JOIN parking p ON p.id = cp.core_id
  WHERE parking_id = new.parking_id;
  
  -- 工事情報の取得
  SELECT 
      r.project_id, 
      r.project_natural_id,
      r.id,
      r.request_natural_id, 
      r.customer_id,
      r.customer_natural_id,
      r.customer_branch_id,
      r.customer_branch_natural_id,
      r.customer_user_id,
      r.customer_user_natural_id,
      r.user_branch_id,
      r.user_branch_natural_id,
      cr.extend_estimate_id
  INTO 
    _project_id,
    _project_natural_id,
    _request_id,
    _request_natural_id,
    _customer_id,
    _customer_natural_id,
    _customer_branch_id,
    _customer_branch_natural_id,
    _customer_user_id,
    _customer_user_natural_id,
    _user_branch_id,
    _user_branch_natural_id,
    _extend_estimate_id
  FROM cu_request cr
    INNER JOIN request r ON r.id = cr.core_id
  WHERE cr.request_id = new.request_id;
  
  -- 延長元見積ID の取得
  IF _extend_estimate_id IS NOT NULL THEN

    SELECT e.estimate_natural_id
    INTO _extend_estimate_natural_id
    FROM cu_estimate ce
      INNER JOIN estimate ON e.core_id = ce.estimate_id
    WHERE estimate_id = _extend_estimate_id;

  END IF;
  
  -- 依頼駐車場管理の登録
  INSERT INTO request_parking( 
      create_timestamp
    , update_timestamp
    , ulid
    , project_id
    , project_natural_id
    , request_id
    , request_natural_id
    , parking_id
    , customer_id
    , customer_natural_id
    , customer_branch_id
    , customer_branch_natural_id
    , customer_user_id
    , customer_user_natural_id
    , supplier_id
    , supplier_natural_id
    , user_branch_id
    , user_branch_natural_id
    , request_parking_status
    , route_map_parking_flag
    , extend_estimate_natural_id
    , lastupdate_user_id
    , want_parking_flag
  ) 
  VALUES (
      CURRENT_TIMESTAMP
    , CURRENT_TIMESTAMP
    , getULID()
    , _project_id
    , _project_natural_id
    , _request_id
    , _request_natural_id
    , _parking_id
    , _customer_id
    , _customer_natural_id
    , _customer_branch_id
    , _customer_branch_natural_id
    , _customer_user_id
    , _customer_user_natural_id
    , _supplier_id
    , _supplier_natural_id
    , _user_branch_id
    , _user_branch_natural_id
    , 0
    , 1
    , _extend_estimate_natural_id
    , 1
    , 1
   );

END;


-- トリガー： 見積（更新） 更新日：21/06/09
DROP TRIGGER IF EXISTS upd_cu_estimate;

CREATE TRIGGER upd_cu_estimate  AFTER UPDATE ON cu_estimate FOR EACH ROW
BEGIN

  -- 顧客向けシステムで更新した場合
  IF update_system_type = 2 THEN


    -- 発注処理
    -- 基幹側の更新対象を改めて確認
    
    -- 失注処理
    IF new.estimate_status = 7 THEN
      UPDATE estimate
         SET 
           estimate_status = new.estimate_status,
           update_timestamp = CURRENT_TIMESTAMP,
           lastupdate_user_id = 1
       WHERE id = new.core_id;
    END IF;
    
    
  END IF;
END;


-- トリガー： 契約（更新）2021/06/10
-- 契約時に基幹側を更新する処理なし
-- 顧客システム側のみでステータス管理

-- ユーザー情報（更新）
DROP TRIGGER IF EXISTS upd_cu_user;

CREATE TRIGGER upd_cu_user  BEFORE UPDATE ON cu_user FOR EACH ROW
BEGIN

  -- 顧客向けシステムで更新した場合
  IF new.update_system_type = 2 THEN

    -- 顧客担当者情報の更新
    UPDATE customer_user cu 
      INNER JOIN cu_customer_user ccu on ccu.core_id = cu.id  
      INNER JOIN cu_user_branch cub ON cub.customer_user_id = ccu.customer_user_id       
      SET 
      cu.lastupdate_user_id              = 1
     ,cu.update_timestamp                = CURRENT_TIMESTAMP
     ,cu.customer_user_name              = new.customer_user_name
     ,cu.customer_user_name_kana         = new.customer_user_name_kana
     ,cu.customer_user_email             = new.login_id
     ,cu.customer_user_tel               = new.customer_user_tel
     ,cu.customer_reminder_sms_flag      = new.customer_reminder_sms_flag
    WHERE cub.user_id = new.user_id;
    
  END IF;

END;

-- ユーザー所属支店（登録）
DROP TRIGGER IF EXISTS ins_cu_user_branch;

CREATE TRIGGER ins_cu_user_branch  BEFORE UPDATE ON cu_user_branch FOR EACH ROW
BEGIN

  DECLARE _customer_id, _customer_branch_id, _customer_user_id, _cu_customer_user_id INT;
  DECLARE _customer_natural_id, _customer_branch_natural_id, _customer_user_natural_id
        , _customer_user_name, _customer_user_name_kana VARCHAR(255) ;
  DECLARE _login_id VARCHAR(2048);
  DECLARE _customer_reminder_sms_flag BOOL;
  DECLARE _customer_user_tel VARCHAR(13);

  -- 顧客情報の取得
  SELECT cb.customer_id, cb.customer_natural_id, cb.id, cb.customer_branch_natural_id
  INTO _customer_id, _customer_natural_id, _customer_branch_id, _customer_branch_natural_id
  FROM cu_customer_branch ccb
    INNER JOIN customer_branch cb ON cb.id = ccb.core_id
  WHERE ccb.customer_branch_id = new.customer_branch_id;
  
  -- ユーザー情報の取得
  SELECT login_id,  customer_user_name,  customer_user_name_kana,  customer_reminder_sms_flag,  customer_user_tel
  INTO  _login_id, _customer_user_name, _customer_user_name_kana, _customer_reminder_sms_flag, _customer_user_tel
  FROM cu_user
  WHERE user_id = new.user_id;

  -- 顧客担当者マスタに存在確認
  SELECT customer_user_id, customer_user_natural_id
  INTO _customer_user_id, _customer_user_natural_id
  FROM customer_user
  WHERE customer_id = _customer_id
    AND customer_branch_id = _customer_branch_id
    AND customer_user_email = _login_id
  LIMIT 1;

  -- データが存在しない場合、
  IF _customer_id IS NULL THEN

    -- 顧客支店担当者を作成
    INSERT INTO customer_user (
      customer_id
     ,customer_natural_id
     ,customer_branch_id
     ,customer_branch_natural_id
     ,customer_user_natural_id
     ,lastupdate_user_id
     ,ulid
     ,create_timestamp
     ,update_timestamp
     ,customer_user_name
     ,customer_user_name_kana
     ,customer_user_email
     ,customer_user_tel
     ,customer_reminder_sms_flag
    )
    VALUES (
      _customer_id
     ,_customer_natural_id
     ,_customer_branch_id
     ,_customer_branch_natural_id
     ,_customer_user_natural_id
     ,1
     ,getULID()
     ,CURRENT_TIMESTAMP
     ,CURRENT_TIMESTAMP
     ,_customer_user_name
     ,_customer_user_name_kana
     ,_customer_user_email
     ,_customer_user_tel
     ,_customer_reminder_sms_flag    
    );

    SELECT customer_user_id
      INTO _cu_customer_user_id
      FROM cu_customer_user ccu
     WHERE ccu.core_id = LAST_INSERT_ID();  

  ELSE
  -- データが存在した場合
  -- 強制的に1件のみを取得する
  -- 同一支店で同一メールアドレスを利用できるユーザーは1名まで
    SELECT customer_user_id
      INTO _cu_customer_user_id
      FROM cu_customer_user ccu
     WHERE ccu.core_id = _customer_user_id;  
  
  END IF;
  
  -- 顧客担当者IDをセット
  SET new.customer_user_id = _cu_customer_user_id;
  
END;
