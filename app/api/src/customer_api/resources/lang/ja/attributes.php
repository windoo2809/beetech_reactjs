<?php

return[
    //common define
    'success' => 'success',
    'update_fail' => 'update fail',
    '200_no_data' => 'no data',
    '400_bad_request' => 'Bad Request',
    '401_unauthorized' => 'Unauthorized',
    '422_unprocessable_entity' => 'Unprocessable Entity',
    '500_internal_server_error' => 'システムで不具合が発生しています。システムの管理者へお問い合わせください。',
    '403_forbidden' => 'データにアクセスする権限が設定されていません。システム管理者へお問い合わせください。',
    '404_notfound' => '404 Not Found',
    'page' => 'ページ',
    'limit' => 'リミット',
    'limit_items' => '表示件数',
    'page_volume' => 'リミット',
    'next_page' => 'ページ',
    'application_limit' => '表示件数',
    'current_page' => 'ページ',
    'record_not_found' => 'Record Not Found',
    /**
     * Define mail
     */
    'mail' => [
        'subject_password_reset' => '【駐車場見積依頼サービス】パスワード再設定のお知らせ',
        'subject_active_account' => '【駐車場見積依頼サービス】ユーザー登録完了のお知らせ',
        'subject_update_mail_address' => '【駐車場見積依頼サービス】Eメールアドレス変更のお知らせ',
        'subject_update_user_import_not_login' => '【駐車場見積依頼サービス】未ログインユーザー様へのお知らせ',
        'subject_import_result' => '【駐車場見積依頼サービス】ユーザーCSV取込み結果のお知らせ',
        'subject_update_user' => '【駐車場見積依頼サービス】Eメールアドレス変更のお知らせ',
        'subject' => '件名',
        'text' => '本文',
        'mail_address' => 'メールアドレス'
    ],
    //Cu_sub_contract
    'cu_sub_contract' => [
        'user_id' => 'ユーザーID',
        'customer_id' => '顧客会社ID',
        'message_200_no_data' => '下請情報がありません。'
    ],

    /**
     * Define text cu_user
     */
    'cu_user' => [
        'loginID' => 'ログインID',
        'password' => 'パスワード',
        'userNoData' => 'ユーザー情報がありません。',
        'userLocked' => 'アカウントがロックされています。',
        'branch_name' => '支店名',
        'user_name' => 'ユーザー名',
        'role_id' => '役割',
        'locked' => 'ロック中',
        'user_id' => 'ユーザーID',
        'un_lock' => 'ロックしない',
        'no_belong' => '所属なし',
        'header' => 'ヘッダー',
        'file' => 'CSVファイル',
        'belong' => '所属状況',
        'belong_status' => '所属',
        'customer_branch_id' => '顧客支店ID',
        'customer_user_name' => 'ユーザー名',
        'customer_user_name_kana' => 'ユーザー名カナ',
        'customer_reminder_sms_flag' => 'SMSリマインド送',
        'customer_reminder_sms_flag_status' => 'SMSリマインド送信',
        'customer_user_tel' => '携帯番号',
        'customer_user_email' => 'メールアドレス',
        'user_lock' => 'システム利用ロック状況',
        'role' => 'ロール',
        'token' => 'トークン',
        'email' => 'メールアドレス',
        'status' => '状態',
        'customer_id' => '顧客会社ID',
        'user_locked' => 'アカウントがロックされています。システム管理者にお問い合わせください。',
        'user_active' => 'アクティブフラグ',
        'sort' => 'ソートキー',
        'customer_login_id' => '会社ログインID'
    ],

    /**
     * Define text cu_customer_branch
     */
    'cu_customer_branch' => [
        'loginID' => 'ログインID',
        'noData' => '所属支店情報がありません。',
        'message_200_no_data' => '顧客支店情報がありません。',
        'customer_branch_name' => '顧客支店名',
        'customer_branch_id' => '顧客支店ID',
    ],

    /**
     * Define text cu_message
     */
    'cu_message' => [
        'user_id' => 'ユーザーID',
        'file' => 'ファイル',
        'body' => '本文',
        'update_fail' => 'メッセージを更新できません。',
        'create_fail' => 'メッセージを登録できません。',
        'noDataMessageCount' => 'メッセージの件数を取得できません。',
        'project_id' => '工事ID',
        'delete_file' => 'ファイル削除フラグ',
        'message_id' => 'メッセージID',
        'required_without' => 'メッセージID',
    ],

    /**
     * Define text cu_customer
     */
    'cu_customer' => [
        'message_200_no_data' => '顧客会社情報がありません。',
        'customer_name' => '顧客名',
        'customer_id' => '顧客会社ID'
    ],

    /**
     * Define text cu_customer_user
     */
    'cu_customer_user' => [
        'customer_user_name' => '顧客担当者名'
    ],

    /**
     * Define text cu_subcontract
     */
    'cu_subcontract' => [
        'customer_id' => '顧客会社ID',
        'subcontract_id' => '下請ID',
        'subcontract_name' => '会社名',
        'subcontract_kana' => '会社名：カナ',
        'subcontract_branch_name' => '支店名',
        'subcontract_branch_kana' => '支店名：カナ',
        'subcontract_user_division_name' => '部署名',
        'subcontract_user_name' => '担当者名',
        'subcontract_user_kana' => '担当者名：カナ',
        'subcontract_user_email' => '担当者メールアドレス',
        'subcontract_branch_tel' => '下請顧客支店：電話番号',
        'subcontract_user_tel' => '担当者携帯番号',
        'subcontract_user_fax' => '担当者FAX番号',
        'subcontract_reminder_sms_flag' => 'SMSリマインド通知',
        'page' => 'ページ',
        'limit' => 'リミット',
        'free_word' => 'フリーワード',
    ],

    /**
     * Define text cu_project
     */
    'cu_project' => [
        'user_id' => 'ユーザーID',
        'message_200_no_data' => '工事情報がありません。',
        'message_500' => 'Internal Server Error',
        'project_id' => '工事ID',
        'core_id' => '基幹システム連携ID',
        'customer_id' => '顧客会社ID',
        'customer_branch_id' => '顧客支店ID',
        'customer_user_id' => '顧客担当者ID',
        'branch_id' => '支店ID',
        'construction_number' => '工事番号',
        'site_name' => '現場名／邸名',
        'site_name_kana' => '現場名／邸名：カナ',
        'zip_code' => '郵便番号',
        'site_prefecture' => '都道府県コード',
        'address_cd' => '住所コード',
        'city_cd' => '市区町村コード',
        'site_city' => '市区町村名',
        'site_address' => '番地（町名以降）',
        'latitude' => '緯度',
        'longitude' => '経度',
        'project_start_date_from' => '工事開始日',
        'project_start_date_to' => '工事開始日',
        'project_finish_date_from' => '工事開始日',
        'project_finish_date_to' => '工事開始日',
        'project_start_date' => '工事開始日',
        'project_finish_date' => '工事完了日',
        'page' => 'ページ',
        'limit' => '表示件数',
        'request_id' => '見積依頼ID',
        'estimate_id' => '見積ID',
        'progress_status' => '進捗ステータス',
        'order' => '並び順',
        'payment_status' => '支払ステータス',
        'search_conditions' => '検索条件',
        'quote_available_start_date_from' => '契約開始日',
        'quote_available_start_date_to' => '契約開始日',
        'quote_available_end_date_from' => '契約終了日',
        'quote_available_end_date_to' => '契約終了日'
    ],

    'search_keyword' => '検索ワード',

    /**
     * Define text cu_parking
     */
    'cu_parking' => [
        'parking_data' => '駐車場情報',
        'parking_id' => '駐車場ID',
        'latitude' => '緯度',
        'longitude' => '経度',
        'radius' => '半径',
        'address' => '住所',
        'origin_longitude' => '出発地点の緯度',
        'origin_latitude' => '出発地点の経度',
        'destination_longitude' => '目的地点の緯度',
        'destination_latitude' => '目的地点の経度',
        'message_0130' => [
            'GOOGLE_NOT_FOUND' => '出発地点か目的地が不正です。',
            'GOOGLE_ZERO_RESULTS' => 'ルートが見つかりませんでした。',
            'GOOGLE_MAX_WAYPOINTS_EXCEEDED' => 'waypointsが多すぎます。',
            'GOOGLE_MAX_ROUTE_LENGTH_EXCEEDED' => 'ルートが長すきます。',
            'GOOGLE_OVER_DAILY_LIMIT' => 'APIキーが無効になっているか、支払いに問題がある可能性があります。',
            'GOOGLE_OVER_QUERY_LIMIT' => 'リクエストの上限に達している可能性があります。',
            'GOOGLE_REQUEST_DENIED' => 'リクエストが拒否されました。',
            'GOOGLE_INVALID_REQUEST' => 'パラメータが不足しています。',
            'GOOGLE_UNKNOWN_ERROR' => 'サーバーエラーのため、リクエストが処理できません。',
        ],
        'message_0100' => [
            'GOOGLE_ZERO_RESULTS' => '住所から緯度・経度が取得できません',
            'GOOGLE_OVER_DAILY_LIMIT' => 'APIキーが無効になっているか、支払いに問題がある可能性があります',
            'GOOGLE_OVER_QUERY_LIMIT' => 'リクエストの上限に達している可能性があります',
            'GOOGLE_REQUEST_DENIED' => 'リクエストが拒否されました',
            'GOOGLE_INVALID_REQUEST' => 'パラメータが不足しています',
            'GOOGLE_UNKNOWN_ERROR' => 'サーバーエラーのため、リクエストが処理できません',
        ],
    ],
    /**
     * Define text cu_file
     */
    'cu_file' => [
        'user_id' => 'ユーザーID',
        'file_type' => 'ファイル種別',
        'file_detail_type' => 'ファイル種別詳細',
        'customer_id' => '顧客ID',
        'project_id' => '工事ID',
        'request_id' => '見積依頼ID',
        'estimate_id' => '見積ID',
        'contract_id' => '契約ID',
        'invoice_id' => '請求ID',
        '403_no_permission' => '権限がありません。',
        'file' => 'ファイル',
        'remark' => '備考',
        'ref_id' => '連携ID',
        'file_id' => 'ファイルID',
        'customer_id_2' => '顧客会社ID',
    ],


    /**
     * Define text cu_information
     */
    'cu_information' => [
        'user_id' => 'ユーザーID',
        'message_403_no_data' => 'お知らせを取得できません。',
        'information_id' => 'お知らせID',
        'read' => '既読',
        'unread' => '未読',
        'page' => 'ページ',
        'limit' => 'リミット',
        'display_header' => '表示位置ヘッダー',
        'display_advertisement' => '表示位置広告エリア',
        'file_path' => 'パス情報',
    ],

    /**
     * Define text cu_estimate
     */
    'cu_estimate' => [
        'message_403_no_data' => '見積情報を更新できません。',
        'message_500' => 'Internal Server Error',
        'estimate_id' => '見積ID',
        'estimate_status' => '見積ステータス',
        'request_id' => '見積依頼ID',
        'estimate_change_flag' => '見積内容変更フラグ',
        'order_start_date' => '発注利用開始日',
        'order_end_date' => '発注利用終了日',
        'order_capacity_qty' => '発注台数',
        'order_amt' => '発注概算金額',
        'order_memo' => '発注連絡事項',
    ],

    /* define for create project error */
    'message_error_of_project' => [
        'not_found_subcontract' => 'not found subcontract',
        'subcontract_create' => '下請情報を登録できませんでした',
        'project' => '工事情報を登録できませんでした',
        'request' => '見積依頼を登録できませんでした',
        'request_parking' => '見積依頼対象駐車場を登録できませんでした',
    ],
    /**
     * Define text cu_request
     */
    'cu_request' => [
        'request_id' => '見積依頼ID',
        'project_id' => '工事ID',
        'core_id' => '基幹システム連携ID',
        'request_type' => '依頼種別',
        'request_date' => '依頼受付日',
        'estimate_deadline' => '見積提出期限',
        'car_qty' => '台数：乗用車（軽自動車・ハイエース等）',
        'light_truck_qty' => '台数：軽トラック',
        'truck_qty' => '台数：2ｔトラック',
        'other_car_qty' => '台数：その他（計）',
        'want_guide_type' => '案内方法',
        'cc_email' => '顧客が指定するCCメールアドレス',
        'response_request_date' => '顧客からの要望日',
        'request_status' => '見積依頼ステータス',
        'request_other_qty' => '個数',
        'subcontract_want_guide_type' => '案内方法：下請用',
        'subcontract_name' => '下請顧客会社名',
        'subcontract_kana' => '下請顧客会社名：カナ',
        'subcontract_branch_name' => '下請顧客支店名',
        'subcontract_branch_kana' => '下請顧客支店名：カナ',
        'subcontract_user_division_name' => '下請顧客部署名',
        'subcontract_user_name' => '下請顧客担当者名',
        'subcontract_user_kana' => '下請顧客担当者名：カナ',
        'subcontract_user_email' => '下請顧客担当者メールアドレス',
        'subcontract_user_tel' => '下請顧客担当者携帯番号',
        'subcontract_user_fax' => '下請顧客担当者FAX番号',
        'want_start_date' => '利用期間：開始',
        'want_end_date' => '利用期間：終了',
        'construction_number' => '工事番号',
        'site_name' => '現場名',
        'address' => '現場住所',
        'subcontract_id' => '下請ID',
        'send_destination_type' => '送付先種別',
        'request_other_deadline' => '着手期限日',
        'request_other_start_date' => '契約開始日',
        'request_other_end_date' => '契約終了日',
        'subcontract_branch_tel' => '下請顧客支店：電話番号',
        'extend_estimate_id' => '延長元見積ID',
        'reference_date' => '基準日',
        'days' => '日数',
        'customer_other_request' => '顧客からの要望等',
        'parking_id' => '駐車場ID',
        'request_capacity_qty' => '依頼台数',
        'request_end_date' => '契約終了日',
    ],

    /**
     * Define text cu_contract
     */
    'cu_contract' => [
        'contract_id' => '契約ID',
        'core_id' => '基幹システム連携ID',
        'project_id' => '工事ID',
        'estimate_id' => '見積ID',
        'parking_id' => '駐車場ID',
        'branch_id' => '支店ID',
        'contract_status' => '契約ステータス',
        'parking_name' => '駐車場名',
        'parking_name_kana' => '駐車場名：カナ',
        'quote_capacity_qty' => '確定見積台数',
        'quote_subtotal_amt' => '確定見積：見積小計',
        'quote_tax_amt' => '確定見積：税額',
        'quote_total_amt' => '確定見積：合計額',
        'purchase_order_register_type'  => '発注書アップロードシステム',
        'purchase_order_upload_date' => '発注書アップロード日',
        'purchase_order_check_date' => '発注書確認日',
        'extension_type' => '契約延長区分',
        'order_schedule_date' => '受注予定日',
        'order_process_date' => '受注処理日',
        'quote_available_start_date' => '契約開始日',
        'quote_available_end_date' => '契約終了日',
        'purchase_order_check_flag' => '発注書確認フラグ',
        'message_500' => 'Internal Server Error',
    ],

    /**
     * Define text cu_application
     */
    'cu_application' => [
        'application_id' => '申請ID',
        'estimate_id' => '見積ID',
        'approval_user_id' => '承認担当者ID',
        'application_comment' => '申請コメント',
        'application_status' => '申請ステータス',
        'approval_comment' => '承認コメント',
        'construction_number' => '工事番号',
        'site_name' => '現場名',
        'address' => '現場住所',
        'want_start_date' => '利用期間：開始',
        'want_end_date' => '利用期間：終了',
        'request_id' => '見積依頼ID'
    ],
    /**
     * Define text cu_invoice
     */
    'cu_invoice' => [
        'invoice_id' => '請求ID',
        'free_word' => 'フリーワード',
        'payment_status' => '支払いステータス',
    ],
    /**
     * Define text cu_address
     */
    'cu_address' => [
        'city_name' => '市区町村',
        'zip_cd' => '郵便番号',
    ],
    /**
     * Define text cu_customer_option
     */
    'cu_customer_option' => [
        'admin_user_login_id' => 'システム管理者',
        'approval' => '承認機能利用',
        'data_scope' => 'データ参照範囲',
    ],
    'cu_request_parking' => [
        'request_id' => '見積依頼ID',
    ],
];
