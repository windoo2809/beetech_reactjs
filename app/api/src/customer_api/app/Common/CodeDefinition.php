<?php


namespace App\Common;


class CodeDefinition
{
    /** 依頼種別 */
    /** 依頼種別.初回 */
    const REQ_FIRST_TIME = 0;
    /** 依頼種別.追加 */
    const REQ_FIRST_ADD = 1;
    /** 依頼種別.延長 */
    const REQ_EXTEND = 2;
    /** 依頼種別.積替 */
    const REQ_ACCUMULATE = 3;
    /** 依頼種別.その他ー道路使用許可 */
    const REQ_OTHER_ROAD_USE_PERMIT = 4;
    /** 依頼種別.その他ー取扱説明書収納 */
    const REQ_OTHER_INSTRUCTION_MANUAL_STORAGE = 5;
    /** 依頼種別.その他ー電線移設 */
    const REQ_OTHERS_ELECTRIC_WIRE_RELOCATION = 6;

    const REQUEST_TYPE_MAP = [
        self::REQ_FIRST_TIME => '初回',
        self::REQ_FIRST_ADD => '追加',
        self::REQ_EXTEND => '延長',
        self::REQ_ACCUMULATE => '積替',
        self::REQ_OTHER_ROAD_USE_PERMIT => 'その他ー道路使用許可',
        self::REQ_OTHER_INSTRUCTION_MANUAL_STORAGE => 'その他ー取扱説明書収納',
        self::REQ_OTHERS_ELECTRIC_WIRE_RELOCATION => 'その他ー電線移設',

    ];

    /** 申請ステータス */
    /** 申請ステータス.未申請 */
    const APPLICATION_STATUS_NOT_APPLIED = 0;
    /** 申請ステータス.申請 */
    const APPLICATION_STATUS_APPLIED = 1;
    /** 申請ステータス.承認 */
    const APPLICATION_STATUS_RECOGNITION = 2;
    /** 申請ステータス.差戻 */
    const APPLICATION_STATUS_REMAND = 3;
    /** 申請ステータス.取下 */
    const APPLICATION_STATUS_CANCEL = 4;

    const APPLICATION_STATUS_MAP = [
        self::APPLICATION_STATUS_NOT_APPLIED => '未申請',
        self::APPLICATION_STATUS_APPLIED => '申請',
        self::APPLICATION_STATUS_RECOGNITION => '承認',
        self::APPLICATION_STATUS_REMAND => '差戻',
        self::APPLICATION_STATUS_CANCEL => '取下',
    ];

    const APPLICATION_STATUS_LIST = [
        self::APPLICATION_STATUS_NOT_APPLIED,
        self::APPLICATION_STATUS_APPLIED,
        self::APPLICATION_STATUS_RECOGNITION,
        self::APPLICATION_STATUS_REMAND,
        self::APPLICATION_STATUS_CANCEL,
    ];

    /** 支払ステータス */
    /** 支払ステータス.未払い */
    CONST PAYMENT_STATUS_UNPAID = 0;
    /** 支払ステータス.一部入金 */
    CONST PAYMENT_STATUS_PARTIAL_DEPOSIT = 1;
    /** 支払ステータス.支払完了 */
    CONST PAYMENT_STATUS_PAYMENT_COMPLETED = 2;

    const PAYMENT_STATUS_MAP = [
        self::PAYMENT_STATUS_UNPAID => '未払い',
        self::PAYMENT_STATUS_PARTIAL_DEPOSIT => '一部入金',
        self::PAYMENT_STATUS_PAYMENT_COMPLETED => '支払完了',
    ];

    const ALL_PAYMENT_STATUS = [
        self::PAYMENT_STATUS_UNPAID,
        self::PAYMENT_STATUS_PARTIAL_DEPOSIT,
        self::PAYMENT_STATUS_PAYMENT_COMPLETED,
    ];

    /** 見積依頼ステータス */
    /** 見積依頼ステータス.受付 */
    const REQUEST_STATUS_RECEPTION = 0;
    /** 見積依頼ステータス.調査中 */
    const REQUEST_STATUS_INVESTIGATING_WEB = 1;
    /** 見積依頼ステータス.調査中 */
    const REQUEST_STATUS_INVESTIGATING = 2;
    /** 見積依頼ステータス.調査完了 */
    const REQUEST_STATUS_SURVEY_COMPLETED = 3;
    /** 見積依頼ステータス.キャンセル */
    const REQUEST_STATUS_CANCELED = 7;
    /** 見積依頼ステータス.調査中止 */
    const REQUEST_STATUS_SURVEY_INVESTIGATION_CANCELED = 9;

    const REQUEST_STATUS_MAP = [
        self::REQUEST_STATUS_RECEPTION => '受付',
        self::REQUEST_STATUS_INVESTIGATING_WEB => '調査中',
        self::REQUEST_STATUS_INVESTIGATING => '調査中',
        self::REQUEST_STATUS_SURVEY_COMPLETED => '調査完了',
        self::REQUEST_STATUS_CANCELED => 'キャンセル',
        self::REQUEST_STATUS_SURVEY_INVESTIGATION_CANCELED => '調査中止',
    ];

    /**送付先種別 */
    /**元請のみに送る */
    const SEND_DESTINATION_TYPE_MAIN_CONTRACT = 0;
    /**下請のみに送る */
    const SEND_DESTINATION_TYPE_SUBCONTRACT = 1;
     /**両方に送る */
    const SEND_DESTINATION_TYPE_ALL = 2;

    const SEND_DESTINATION_TYPE_LIST = [
        CodeDefinition::SEND_DESTINATION_TYPE_MAIN_CONTRACT,
        CodeDefinition::SEND_DESTINATION_TYPE_SUBCONTRACT,
        CodeDefinition::SEND_DESTINATION_TYPE_ALL,
    ];

    const SEND_DESTINATION_TYPE_MAP = [
        CodeDefinition::SEND_DESTINATION_TYPE_MAIN_CONTRACT => "自社へ送付",
        CodeDefinition::SEND_DESTINATION_TYPE_SUBCONTRACT => "下請へ送付",
        CodeDefinition::SEND_DESTINATION_TYPE_ALL => "両方へ送付",
    ];

    /** 見積ステータス */
    /** 見積ステータス.未作成 */
    const ESTIMATE_STATUS_NOT_CREATED = 0;
    /** 見積ステータス.受注待ち */
    const ESTIMATE_STATUS_WAITING_ORDER_CREATED_SURVEY = 1;
    /** 見積ステータス.発注書受領 */
    const ESTIMATE_STATUS_PURCHASE_ORDER_RECEIPT = 2;
    /** 見積ステータス.受注待ち */
    const ESTIMATE_STATUS_WAITING_ORDER_SENDED_SURVEY = 3;
    /** 見積ステータス.受注 */
    const ESTIMATE_STATUS_RECEIVED_ORDERS = 4;
    /** 見積ステータス.確定見積送付済 */
    const ESTIMATE_STATUS_CONFIRMED_QUITATION = 5;
    /** 見積ステータス.キャンセル予約 */
    const ESTIMATE_STATUS_CANCEL_RESERVATION = 6;
    /** 見積ステータス.キャンセル */
    const ESTIMATE_STATUS_USER_CANCEL = 7;
    /** 見積ステータス.キャンセル */
    const ESTIMATE_STATUS_HAND_CANCEL = 8;
    /** 見積ステータス.キャンセル */
    const ESTIMATE_STATUS_AUTO_CANCEL = 9;

    const ESTIMATE_STATUS_MAP = [
        self::ESTIMATE_STATUS_NOT_CREATED => '未作成',
        self::ESTIMATE_STATUS_WAITING_ORDER_CREATED_SURVEY => '受注待ち',
        self::ESTIMATE_STATUS_PURCHASE_ORDER_RECEIPT => '発注書受領',
        self::ESTIMATE_STATUS_WAITING_ORDER_SENDED_SURVEY => '受注待ち',
        self::ESTIMATE_STATUS_RECEIVED_ORDERS => '受注',
        self::ESTIMATE_STATUS_CONFIRMED_QUITATION => '確定見積送付済',
        self::ESTIMATE_STATUS_USER_CANCEL => 'キャンセル',
        self::ESTIMATE_STATUS_HAND_CANCEL => 'キャンセル',
        self::ESTIMATE_STATUS_AUTO_CANCEL => 'キャンセル',
    ];

    const ESTIMATE_STATUS_LIST = [
        self::ESTIMATE_STATUS_NOT_CREATED,
        self::ESTIMATE_STATUS_WAITING_ORDER_CREATED_SURVEY,
        self::ESTIMATE_STATUS_PURCHASE_ORDER_RECEIPT,
        self::ESTIMATE_STATUS_WAITING_ORDER_SENDED_SURVEY,
        self::ESTIMATE_STATUS_RECEIVED_ORDERS,
        self::ESTIMATE_STATUS_CONFIRMED_QUITATION,
        self::ESTIMATE_STATUS_USER_CANCEL,
        self::ESTIMATE_STATUS_HAND_CANCEL,
        self::ESTIMATE_STATUS_AUTO_CANCEL,
    ];

    /** 契約ステータス */
    /** 契約ステータス.受注準備中 */
    const CONTRACT_STATUS_PREPARING_TO_RECEIVE_AN_ORDER = 1;
    /** 契約ステータス.契約書準備中 */
    const CONTRACT_STATUS_PREPARING_CONTRACT = 2;
    /** 契約ステータス.契約書返送待ち */
    const CONTRACT_STATUS_WAITING_FOR_CONTRACT_TO_BE_RRETURNED = 3;
    /** 契約ステータス.契約中 */
    const CONTRACT_STATUS_UNDER_CONTRACT = 4;
    /** 契約ステータス.契約終了 */
    const CONTRACT_STATUS_END_CONTRACT = 5;
    /** 契約ステータス.キャンセル */
    const CONTRACT_STATUS_CANCEL = 7;

    const CONTRACT_STATUS_MAP = [
        self::CONTRACT_STATUS_PREPARING_TO_RECEIVE_AN_ORDER => '受注準備中',
        self::CONTRACT_STATUS_PREPARING_CONTRACT => '契約書準備中',
        self::CONTRACT_STATUS_WAITING_FOR_CONTRACT_TO_BE_RRETURNED => '契約書返送待ち',
        self::CONTRACT_STATUS_UNDER_CONTRACT => '契約中',
        self::CONTRACT_STATUS_END_CONTRACT => '契約終了',
        self::CONTRACT_STATUS_CANCEL => 'キャンセル',
    ];


    /**請求ステータス */
    /**invoice_status */
    /**未処理 */
    const INVOICE_STATUS_UNTREATED = 0;
    /**請求依頼済 */
    const INVOICE_STATUS_REQUESTED_PAYMENT = 1;
    /**請求書生成済 */
    const INVOICE_STATUS_GENERATED_INVOICE = 2;
    /**請求処理待ち */
    const INVOICE_STATUS_WAITING_FOR_TREAT_INVOICE = 3;
    /**請求完了 */
    const INVOICE_STATUS_COMPLETED_PAYMENT_REQUEST = 4;
    /**請求完了 */
    const INVOICE_STATUS_CANCEL_PAYMENT = 5;



    /** 契約延長区分 */
    /** 契約延長区分.未確認 */
    const EXTENSION_TYPE_UNCONFIRMED = 0;
    /** 契約延長区分.延長申込 */
    const EXTENSION_TYPE_EXTENSION_APPLICATION = 1;
    /** 契約延長区分.延長無し */
    const EXTENSION_TYPE_NO_EXTENSION = 2;

    const EXTENSION_TYPE_MAP = [
        self::EXTENSION_TYPE_UNCONFIRMED => '未確認',
        self::EXTENSION_TYPE_EXTENSION_APPLICATION => '延長申込',
        self::EXTENSION_TYPE_NO_EXTENSION => '延長無し',
    ];

    /** ファイル種別 */
    /** ファイル種別.工事 */
    const FILE_TYPE_CONSTRUCTION = 1;
    /** ファイル種別.見積依頼 */
    const FILE_TYPE_REQUEST_ESTIMATE = 2;
    /** ファイル種別.見積 */
    const FILE_TYPE_ESTIMATE = 3;
    /** ファイル種別.発注 */
    const FILE_TYPE_ORDER = 4;
    /** ファイル種別.契約 */
    const FILE_TYPE_CONTRACT = 5;
    /** ファイル種別.請求 */
    const FILE_TYPE_BILLING = 6;
    /** ファイル種別.メッセージ */
    const FILE_TYPE_MESSAGE = 7;

    const FILE_TYPE_MAP = [
        self::FILE_TYPE_CONSTRUCTION => '工事',
        self::FILE_TYPE_REQUEST_ESTIMATE => '見積依頼',
        self::FILE_TYPE_ESTIMATE => '見積',
        self::FILE_TYPE_ORDER => '発注',
        self::FILE_TYPE_CONTRACT => '契約',
        self::FILE_TYPE_BILLING => '請求',
        self::FILE_TYPE_MESSAGE => 'メッセージ',
    ];

    const ALL_FILE_TYPE = [
        CodeDefinition::FILE_TYPE_CONSTRUCTION,
        CodeDefinition::FILE_TYPE_REQUEST_ESTIMATE,
        CodeDefinition::FILE_TYPE_ESTIMATE,
        CodeDefinition::FILE_TYPE_ORDER,
        CodeDefinition::FILE_TYPE_CONTRACT,
        CodeDefinition::FILE_TYPE_BILLING,
        CodeDefinition::FILE_TYPE_MESSAGE,
    ];
    /**現場図面 */
    const FILE_DETAIL_TYPE_CONSTRUCTION_DRAW = 101;
    /**その他資料 */
    const FILE_DETAIL_TYPE_CONSTRUCTION_OTHER = 109;
    /**見積依頼書 */
    const FILE_DETAIL_TYPE_REQUEST_ESTIMATE_FORM = 201;
    /**経路図 */
    const FILE_DETAIL_TYPE_REQUEST_ROUTE_MAP = 202;
    /**その他資料 */
    const FILE_DETAIL_TYPE_REQUEST_ESTIMATE_OTHER = 209;
    /**調査見積書 */
    const FILE_DETAIL_TYPE_ESTIMATE_SURVEY_FORM = 301;
    /**区画図面 */
    const FILE_DETAIL_TYPE_ESTIMATE_LAND_DRAW = 302;
    /**確定見積書 */
    const FILE_DETAIL_TYPE_ESTIMATE_DETERMINED = 303;
    /**その他資料 */
    const FILE_DETAIL_TYPE_ESTIMATE_OTHER = 309;
    /**発注書 */
    const FILE_DETAIL_TYPE_ORDER_FORM = 401;
    /**発注書（捺印済） */
    const FILE_DETAIL_TYPE_ORDER_FORM_STAMPED = 402;
    /**その他資料 */
    const FILE_DETAIL_TYPE_ORDER_OTHER = 409;
    /**契約書 */
    const FILE_DETAIL_TYPE_CONTRACT_FORM = 501;
    /**契約書（捺印済） */
    const FILE_DETAIL_TYPE_CONTRACT_FORM_STAMPED = 502;
    /**その他資料 */
    const FILE_DETAIL_TYPE_CONTRACT_OTHER = 509;
    /**請求書 */
    const FILE_DETAIL_TYPE_PAYMENT_REQUEST_FORM = 601;
    /**その他資料 */
    const FILE_DETAIL_TYPE_PAYMENT_REQUEST_OTHER = 609;
    /**添付ファイル */
    const FILE_DETAIL_TYPE_MESSAGE_ATTACHMENT = 701;

    const FILE_DETAIL_TYPE_GROUP = [
        self::FILE_TYPE_CONSTRUCTION => [
            self::FILE_DETAIL_TYPE_CONSTRUCTION_DRAW,
            self::FILE_DETAIL_TYPE_CONSTRUCTION_OTHER,
        ],
        self::FILE_TYPE_REQUEST_ESTIMATE => [
            self::FILE_DETAIL_TYPE_REQUEST_ESTIMATE_FORM,
            self::FILE_DETAIL_TYPE_REQUEST_ROUTE_MAP,
            self::FILE_DETAIL_TYPE_REQUEST_ESTIMATE_OTHER,
        ],
        self::FILE_TYPE_ESTIMATE => [
            self::FILE_DETAIL_TYPE_ESTIMATE_SURVEY_FORM,
            self::FILE_DETAIL_TYPE_ESTIMATE_LAND_DRAW,
            self::FILE_DETAIL_TYPE_ESTIMATE_DETERMINED,
            self::FILE_DETAIL_TYPE_ESTIMATE_OTHER,
        ],
        self::FILE_TYPE_ORDER => [
            self::FILE_DETAIL_TYPE_ORDER_FORM,
            self::FILE_DETAIL_TYPE_ORDER_FORM_STAMPED,
            self::FILE_DETAIL_TYPE_ORDER_OTHER,
        ],
        self::FILE_TYPE_CONTRACT => [
            self::FILE_DETAIL_TYPE_CONTRACT_FORM,
            self::FILE_DETAIL_TYPE_CONTRACT_FORM_STAMPED,
            self::FILE_DETAIL_TYPE_CONTRACT_OTHER,
        ],
        self::FILE_TYPE_BILLING => [
            self::FILE_DETAIL_TYPE_PAYMENT_REQUEST_FORM,
            self::FILE_DETAIL_TYPE_PAYMENT_REQUEST_OTHER,
        ],
        self::FILE_TYPE_MESSAGE => [
            self::FILE_DETAIL_TYPE_MESSAGE_ATTACHMENT,
        ],
    ];

    /** 権限 */
    /** 権限.スーパーユーザー */
    const ROLE_SUPER_USER = 0;
    /** 権限.システム管理者 */
    const ROLE_SYSTEM_ADMINISTRATOR = 1;
    /** 権限.承認者 */
    const ROLE_APPROVER = 2;
    /** 権限.経理担当者 */
    const ROLE_ACCOUNTANT = 3;
    /** 権限.一般 */
    const ROLE_PERSON_IN_CHARGE = 4;
    /** 権限.権限なし */
    const ROLE_NO_PERMISSION = 9;

    const ROLE_MAP = [
        self::ROLE_SUPER_USER => 'スーパーユーザー',
        self::ROLE_SYSTEM_ADMINISTRATOR => 'システム管理者',
        self::ROLE_APPROVER => '承認者',
        self::ROLE_ACCOUNTANT => '経理担当者',
        self::ROLE_PERSON_IN_CHARGE => '一般',
        self::ROLE_NO_PERMISSION => '権限なし',
    ];

    /** 所属状況 */
    /** 所属状況.所属なし */
    const BELONG_NO_AFFILIATION = 0;
    /** 所属状況.所属中 */
    const BELONG_BELONGING = 1;

    const BELONG_MAP = [
        self::BELONG_NO_AFFILIATION => '所属なし',
        self::BELONG_BELONGING => '所属',
    ];

    /** データ参照範囲 */
    /** データ参照範囲.すべて */
    const DATA_SCOPE_ALL = 0;
    /** データ参照範囲.支店単位 */
    const DATA_SCOPE_BRANCH_UNIT = 1;
    /** データ参照範囲.担当者単位 */
    const DATA_SCOPE_PERSON_IN_CHARGE = 2;

    const DATA_SCOPE_MAP = [
        self::DATA_SCOPE_ALL => 'すべて',
        self::DATA_SCOPE_BRANCH_UNIT => '支店単位',
        self::DATA_SCOPE_PERSON_IN_CHARGE => '担当者単位',
    ];

    /** 案内方法 */
    /** 案内方法.両方 */
    const WANT_GUIDE_TYPE_EMAIL_AND_FAX = 0;
    /** 案内方法.Eメール案内希望 */
    const WANT_GUIDE_TYPE_EMAIL = 1;
    /** 案内方法.FAX案内希望 */
    const WANT_GUIDE_TYPE_FAX = 2;

    const WANT_GUIDE_TYPE_MAP = [
        self::WANT_GUIDE_TYPE_EMAIL_AND_FAX => '両方',
        self::WANT_GUIDE_TYPE_EMAIL => 'Eメール案内希望',
        self::WANT_GUIDE_TYPE_FAX => 'FAX案内希望',
    ];

    /** 都道府県 */
    /** 都道府県.北海道 */
    const PREFECTURE_HOKKAIDO = '01';
    /** 都道府県.青森県 */
    const PREFECTURE_AOMORI = '02';
    /** 都道府県.岩手県 */
    const PREFECTURE_IWATE = '03';
    /** 都道府県.宮城県 */
    const PREFECTURE_MIYAGI = '04';
    /** 都道府県.秋田県 */
    const PREFECTURE_AKITA = '05';
    /** 都道府県.山形県 */
    const PREFECTURE_YAMAGATA = '06';
    /** 都道府県.福島県 */
    const PREFECTURE_FUKUSHIMA = '07';
    /** 都道府県.茨城県 */
    const PREFECTURE_IBARAKI = '08';
    /** 都道府県.栃木県 */
    const PREFECTURE_TOCHIGI = '09';
    /** 都道府県.群馬県 */
    const PREFECTURE_GUNMA = '10';
    /** 都道府県.埼玉県 */
    const PREFECTURE_SAITAMA = '11';
    /** 都道府県.千葉県 */
    const PREFECTURE_CHIBA = '12';
    /** 都道府県.東京都 */
    const PREFECTURE_TOKYO = '13';
    /** 都道府県.神奈川県 */
    const PREFECTURE_KANAGAWA = '14';
    /** 都道府県.新潟県 */
    const PREFECTURE_NIIGATA = '15';
    /** 都道府県.富山県 */
    const PREFECTURE_TOYAMA = '16';
    /** 都道府県.石川県 */
    const PREFECTURE_ISHIKAWA = '17';
    /** 都道府県.福井県 */
    const PREFECTURE_FUKUI = '18';
    /** 都道府県.山梨県 */
    const PREFECTURE_YAMANASHI = '19';
    /** 都道府県.長野県 */
    const PREFECTURE_NAGANO = '20';
    /** 都道府県.岐阜県 */
    const PREFECTURE_GIFU = '21';
    /** 都道府県.静岡県 */
    const PREFECTURE_SHIZUOKA = '22';
    /** 都道府県.愛知県 */
    const PREFECTURE_AICHI = '23';
    /** 都道府県.三重県 */
    const PREFECTURE_MIE = '24';
    /** 都道府県.滋賀県 */
    const PREFECTURE_SHIGA = '25';
    /** 都道府県.京都府 */
    const PREFECTURE_KYOTO = '26';
    /** 都道府県.大阪府 */
    const PREFECTURE_OSAKA = '27';
    /** 都道府県.兵庫県 */
    const PREFECTURE_HYOGO = '28';
    /** 都道府県.奈良県 */
    const PREFECTURE_NARA = '29';
    /** 都道府県.和歌山県 */
    const PREFECTURE_WAKAYAMA = '30';
    /** 都道府県.鳥取県 */
    const PREFECTURE_TOTTORI = '31';
    /** 都道府県.島根県 */
    const PREFECTURE_SHIMANE = '32';
    /** 都道府県.岡山県 */
    const PREFECTURE_OKAYAMA = '33';
    /** 都道府県.広島県 */
    const PREFECTURE_HIROSHIMA = '34';
    /** 都道府県.山口県 */
    const PREFECTURE_YAMAGUCHI = '35';
    /** 都道府県.徳島県 */
    const PREFECTURE_TOKUSHIMA = '36';
    /** 都道府県.香川県 */
    const PREFECTURE_KAGAWA = '37';
    /** 都道府県.愛媛県 */
    const PREFECTURE_EHIME = '38';
    /** 都道府県.高知県 */
    const PREFECTURE_KOCHI = '39';
    /** 都道府県.福岡県 */
    const PREFECTURE_FUKUOKA = '40';
    /** 都道府県.佐賀県 */
    const PREFECTURE_SAGA = '41';
    /** 都道府県.長崎県 */
    const PREFECTURE_NAGASAKI = '42';
    /** 都道府県.熊本県 */
    const PREFECTURE_KUMAMOTO = '43';
    /** 都道府県.大分県 */
    const PREFECTURE_OITA = '44';
    /** 都道府県.宮崎県 */
    const PREFECTURE_MIYAZAKI = '45';
    /** 都道府県.鹿児島県 */
    const PREFECTURE_KAGOSHIMA = '46';
    /** 都道府県.沖縄県 */
    const PREFECTURE_OKINAWA = '47';

    const PREFECTURE_MAP = [
        self::PREFECTURE_HOKKAIDO => '北海道',
        self::PREFECTURE_AOMORI => '青森県',
        self::PREFECTURE_IWATE => '岩手県',
        self::PREFECTURE_MIYAGI => '宮城県',
        self::PREFECTURE_AKITA => '秋田県',
        self::PREFECTURE_YAMAGATA => '山形県',
        self::PREFECTURE_FUKUSHIMA => '福島県',
        self::PREFECTURE_IBARAKI => '茨城県',
        self::PREFECTURE_TOCHIGI => '栃木県',
        self::PREFECTURE_GUNMA => '群馬県',
        self::PREFECTURE_SAITAMA => '埼玉県',
        self::PREFECTURE_CHIBA => '千葉県',
        self::PREFECTURE_TOKYO => '東京都',
        self::PREFECTURE_KANAGAWA => '神奈川県',
        self::PREFECTURE_NIIGATA => '新潟県',
        self::PREFECTURE_TOYAMA => '富山県',
        self::PREFECTURE_ISHIKAWA => '石川県',
        self::PREFECTURE_FUKUI => '福井県',
        self::PREFECTURE_YAMANASHI => '山梨県',
        self::PREFECTURE_NAGANO => '長野県',
        self::PREFECTURE_GIFU => '岐阜県',
        self::PREFECTURE_SHIZUOKA => '静岡県',
        self::PREFECTURE_AICHI => '愛知県',
        self::PREFECTURE_MIE => '三重県',
        self::PREFECTURE_SHIGA => '滋賀県',
        self::PREFECTURE_KYOTO => '京都府',
        self::PREFECTURE_OSAKA => '大阪府',
        self::PREFECTURE_HYOGO => '兵庫県',
        self::PREFECTURE_NARA => '奈良県',
        self::PREFECTURE_WAKAYAMA => '和歌山県',
        self::PREFECTURE_TOTTORI => '鳥取県',
        self::PREFECTURE_SHIMANE => '島根県',
        self::PREFECTURE_OKAYAMA => '岡山県',
        self::PREFECTURE_HIROSHIMA => '広島県',
        self::PREFECTURE_YAMAGUCHI => '山口県',
        self::PREFECTURE_TOKUSHIMA => '徳島県',
        self::PREFECTURE_KAGAWA => '香川県',
        self::PREFECTURE_EHIME => '愛媛県',
        self::PREFECTURE_KOCHI => '高知県',
        self::PREFECTURE_FUKUOKA=> '福岡県',
        self::PREFECTURE_SAGA => '佐賀県',
        self::PREFECTURE_NAGASAKI => '長崎県',
        self::PREFECTURE_KUMAMOTO => '熊本県',
        self::PREFECTURE_OITA => '大分県',
        self::PREFECTURE_MIYAZAKI => '宮崎県',
        self::PREFECTURE_KAGOSHIMA => '鹿児島県',
        self::PREFECTURE_OKINAWA => '沖縄県',
    ];

    const FILE_NAME_PREFIX = "FILE";

    const REMINDER_SMS_TRUE = 1;
    const REMINDER_SMS_FALSE = 0;
    const REMINDER_SMS_MAP = [
        self::REMINDER_SMS_FALSE => 'なし',
        self::REMINDER_SMS_TRUE => 'あり'
    ];

    /**
     * Get code name by code
     * @param array $codeNameMap
     * @param string $code
     * @return string
     */
    public static function getCodeName($code, array $codeNameMap = []) {
        if (array_key_exists($code, $codeNameMap)) {
            return $codeNameMap[$code];
        } else {
            return '';
        }
    }


    /* Define layout redirect if login success */
    const REDIRECT_TO_CUSTOMER_LAYOUT_FIRST_ACCESS = "WEG_01_0108";
    const REDIRECT_TO_CUSTOMER_LAYOUT_MANY_MANAGER = "WEG_01_0101";
    const REDIRECT_TO_CUSTOMER_LAYOUT_DEFAULT = "WEG_02_0001";
    const REDIRECT_TO_ADMIN_LAYOUT = "WEG_01_0102";

    /** Define for pagination */
    const PAGINATE_DEFAULT_PAGE = 1;
    const PAGINATE_DEFAULT_LIMIT = 20;
    const INFORMATION_PAGINATE_MAX_LIMIT = 100;
    const PROJECT_PAGINATE_DEFAULT_LIMIT = 5;

    /** Define for log level */
    const MONOLOG_LEVEL = [
        'ERROR',
        'INFO',
        'DEBUG'
    ];

    /** Define for upload file */
    const MAX_FILE_UPLOAD_ON_DAY = 999;

    /** Define for search project */
    const SEARCH_CONDITION_ONE = 1;
    const SEARCH_CONDITION_SECOND = 2;
    const SEARCH_CONDITION_THIRD = 3;
    const SEARCH_START_DAY = 30;
    const SEARCH_END_DAY = 60;

    #Config url reset password frontend and active account
    const ACTIVE_USER_EMAIL_PATH = "user/password/edit/first-time/";
    const UPDATE_USER_EMAIL_PATH = "changeUserInfo/";
    const RESET_PASSWORD_USER_EMAIL_PATH = "activate/";

    /** notification data_type */
    const DATA_TYPE_ALL = 1; //全て
    const DATA_TYPE_CUSTOMER_ID = 2; //顧客単位
    const DATA_TYPE_CUSTOMER_BRANCH_ID = 3; //顧客支店単位
    const DATA_TYPE_CUSTOMER_USER_ID = 4; //顧客担当者単位

    /** Define encoding Shift JIS */
    const SHIFT_JIS_ENCODING = "SJIS";
    const SHIFT_JIS_ENCODING_WIN = "SJIS-win";

    /** Define header file csv */
    const HEADER_CSV_FILE = [
        'login_id',
        'user_lock',
        'role',
        'customer_user_name',
        'customer_user_name_kana',
        'customer_user_tel',
        'customer_id',
        'customer_branch_id',
        'belong',
        'process_type'
    ];
    /** Define max item in csv file */
    const MAX_ITEM_CSV_FILE = 100;
    const MESSAGE_CORE_USER_NAME = "LANDMARK";

    /** Define length string password */
    const PASSWORD_LENGTH = 20;

    /** Define for encrypt and decrypt */
    const CIPHER_METHOD = 'aes-256-cbc';
    const CHARACTER = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789`-=~!@#$%^&*()_+,./<>?;:[]{}\|";

    /** Define for survey_tax_in_flag */
    const SURVEY_TAX_IN_FLAG_FALSE = 0;
    const SURVEY_TAX_IN_FLAG_TRUE = 1;
    const SURVEY_TAX_IN_FLAG_MAP = [
        self::SURVEY_TAX_IN_FLAG_FALSE => '税抜',
        self::SURVEY_TAX_IN_FLAG_TRUE => '税込'
    ];

    /** 取得不能 */
    const PROGRESS_STATUS_CANT_NOT_GET = 0;
    /** 見積処理中 */
    const PROGRESS_STATUS_PROCESSING_ESTIMATE = 1;
    /** 注文待ち */
    const PROGRESS_STATUS_WAITING_FOR_ORDER = 2;
    /** 受注処理中 */
    const PROGRESS_STATUS_RECEIVING_ORDER = 3;
    /** ご利用準備完了 */
    const PROGRESS_STATUS_COMPLETELY_PREPARED_TO_USE = 4;
    /** ご契約待ち */
    const PROGRESS_STATUS_WAIT_FOR_CONTRACT = 5;
    /** ご契約中 */
    const PROGRESS_STATUS_IN_CONTRACT = 6;
    /** 受付済み */
    const PROGRESS_STATUS_RECEIVED = 11;
    /** 完了 */
    const PROGRESS_STATUS_COMPLETED = 80;
    /** キャンセル */
    const PROGRESS_STATUS_CANCEL = 99;

    const PROGRESS_STATUS_LIST = [
        self::PROGRESS_STATUS_CANT_NOT_GET,
        self::PROGRESS_STATUS_PROCESSING_ESTIMATE,
        self::PROGRESS_STATUS_WAITING_FOR_ORDER,
        self::PROGRESS_STATUS_RECEIVING_ORDER,
        self::PROGRESS_STATUS_COMPLETELY_PREPARED_TO_USE,
        self::PROGRESS_STATUS_WAIT_FOR_CONTRACT,
        self::PROGRESS_STATUS_IN_CONTRACT,
        self::PROGRESS_STATUS_RECEIVED,
        self::PROGRESS_STATUS_COMPLETED,
        self::PROGRESS_STATUS_CANCEL,
    ];

    /** その他作業ステータス */
    /** その他作業ステータス.未着手 */
    const REQUEST_OTHER_STATUS_NOT_START = 0;
    /** その他作業ステータス.step1 */
    const REQUEST_OTHER_STATUS_STEP1 = 1;
    /** その他作業ステータス.step2 */
    const REQUEST_OTHER_STATUS_STEP2 = 2;
    /** その他作業ステータス.step3 */
    const REQUEST_OTHER_STATUS_STEP3 = 3;
    /** その他作業ステータス.step4 */
    const REQUEST_OTHER_STATUS_STEP4 = 4;
    /** その他作業ステータス.step5 */
    const REQUEST_OTHER_STATUS_STEP5 = 5;
    /** その他作業ステータス.完了 */
    const REQUEST_OTHER_STATUS_COMPLETED = 6;

    const REQUEST_OTHER_STATUS_MAP = [
        self::REQUEST_OTHER_STATUS_NOT_START => '未着手',
        self::REQUEST_OTHER_STATUS_STEP1 => 'step1',
        self::REQUEST_OTHER_STATUS_STEP2 => 'step2',
        self::REQUEST_OTHER_STATUS_STEP3 => 'step3',
        self::REQUEST_OTHER_STATUS_STEP4 => 'step4',
        self::REQUEST_OTHER_STATUS_STEP5 => 'step5',
        self::REQUEST_OTHER_STATUS_COMPLETED => '完了',
    ];
}
