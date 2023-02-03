<?php

return [

    /*
    |--------------------------------------------------------------------------
    | バリデーション言語行
    |--------------------------------------------------------------------------
    |
    | 以下の言語行はバリデタークラスにより使用されるデフォルトのエラー
    | メッセージです。サイズルールのようにいくつかのバリデーションを
    | 持っているものもあります。メッセージはご自由に調整してください。
    |
    */

    'accepted' => ':attributeを承認してください。',
    'active_url' => ':attributeが有効なURLではありません。',
    'after' => ':attributeには:dateより後の日付を指定してください。',
    'after_or_equal' => ':attributeには、:date以降の日付を指定してください。',
    'alpha' => ':attributeはアルファベットのみがご利用できます。',
    'alpha_dash' => ':attributeはアルファベットとダッシュ(-)及び下線(_)がご利用できます。',
    'alpha_num' => ':attributeはアルファベット数字がご利用できます。',
    'array' => ':attributeは配列でなくてはなりません。',
    'before' => ':attributeには、:dateより前の日付をご利用ください。',
    'before_or_equal' => [
        "errorcode" => "E400013",
        "message" => ":attributeは、:dateより小さい日付を入力してください。"
    ],
    'between' => [
        'numeric' => ':attributeは、:minから:maxの間で指定してください。',
        'file' => ':attributeは、:min kBから、:max kBの間で指定してください。',
        'string' => ':attributeは、:min文字から、:max文字の間で指定してください。',
        'array' => ':attributeは、:min個から:max個の間で指定してください。',
    ],
    'boolean' => [
        'errorcode' => 'E400007',
        'message' => ':attributeのデータ型が不正です。'
    ],
    'confirmed' => ':attributeと、確認フィールドとが、一致していません。',
    'date' => ':attributeには有効な日付を指定してください。',
    'date_equals' => ':attributeには、:dateと同じ日付けを指定してください。',
    'date_format' => [
        'errorcode' => 'E400019',
        'message' => ':attributeの日付フォーマットが正しくありません。'
    ],
    'different' => ':attributeと:otherには、異なった内容を指定してください。',
    'digits' => ':attributeは:digits桁である必要があります。',
    'digits_between' => ':attributeは:min桁から:max桁である必要があります。',
    'dimensions' => ':attributeの図形サイズが正しくありません。',
    'distinct' => ':attributeには異なった値を指定してください。',
    'email' => [
        "errorcode" => "E400009",
        "message" => ":attributeは有効なメールアドレスを入力してください。"
    ],
    'ends_with' => ':attributeには、:valuesのどれかで終わる値を指定してください。',
    'exists' => [
        'errorcode' => 'E400006',
        'message' => '選択された:attributeは正しくありません。'
    ],
    'file' => ':attributeにはファイルを指定してください。',
    'filled' => ':attributeに値を指定してください。',
    'gt' => [
        'numeric' => ':attributeには、:valueより大きな値を指定してください。',
        'file' => ':attributeには、:value kBより大きなファイルを指定してください。',
        'string' => ':attributeは、:value文字より長く指定してください。',
        'array' => ':attributeには、:value個より多くのアイテムを指定してください。',
    ],
    'gte' => [
        'numeric' => ':attributeには、:value以上の値を指定してください。',
        'file' => ':attributeには、:value kB以上のファイルを指定してください。',
        'string' => ':attributeは、:value文字以上で指定してください。',
        'array' => ':attributeには、:value個以上のアイテムを指定してください。',
    ],
    'image' => ':attributeには画像ファイルを指定してください。',
    'in' => [
        'errorcode' => 'E400006',
        'message' => '指定された:attributeは存在しません。'
    ],
    'in_array' => ':attributeには:otherの値を指定してください。',
    'integer' => [
        'errorcode' => 'E400002',
        'message' => ':attributeは半角数字で入力してください。',
    ],
    'ip' => ':attributeには、有効なIPアドレスを指定してください。',
    'ipv4' => ':attributeには、有効なIPv4アドレスを指定してください。',
    'ipv6' => ':attributeには、有効なIPv6アドレスを指定してください。',
    'json' => ':attributeには、有効なJSON文字列を指定してください。',
    'lt' => [
        'numeric' => ':attributeには、:valueより小さな値を指定してください。',
        'file' => ':attributeには、:value kBより小さなファイルを指定してください。',
        'string' => ':attributeは、:value文字より短く指定してください。',
        'array' => ':attributeには、:value個より少ないアイテムを指定してください。',
    ],
    'lte' => [
        'numeric' => ':attributeには、:value以下の値を指定してください。',
        'file' => ':attributeには、:value kB以下のファイルを指定してください。',
        'string' => [
            'errorcode' => 'E400013',
            'message' => ':attributeは、:value文字以下で指定してください。',
        ],
        'array' => ':attributeには、:value個以下のアイテムを指定してください。',
    ],
    'max' => [
        'numeric' => [
            'errorcode' => 'E400015',
            'message' => ':attributeは:max以下で入力してください。'
        ],
        'file' =>[
            'errorcode' => 'E400014',
            'message' =>  ':attributeには :max kB以内で登録してください。',
        ],
        'string' => [
            'errorcode' => 'E400004',
            'message' => ':attributeは:max文字以下で入力してください。',
        ],
        'array' => ':attributeは :max個以下指定してください。',
    ],
    'mimes' => ':attributeには:valuesタイプのファイルを指定してください。',
    'mimetypes' => ':attributeには:valuesタイプのファイルを指定してください。',
    'min' => [
        'numeric' => [
            'errorcode' => 'E400016',
            'message' => ':attributeは:min以上で入力してください。'
        ],
        'file' => ':attributeには、:min kB以上のファイルを指定してください。',
        'string' => ':attributeは:min文字以上でなければなりません。',
        'array' => ':attributeは:min個以上指定してください。',
    ],
    'not_in' => '選択された:attributeは正しくありません。',
    'not_regex' => ':attributeの形式が正しくありません。',
    'numeric' => [
        'errorcode' => 'E400002',
        'message' => ':attributeは半角数字で入力してください。'
    ],
    'present' => ':attributeが存在していません。',
    'regex' => ':attributeに正しい形式を指定してください。',
    'required' => [
        'errorcode' => 'E400001',
        'message' => ':attributeは必須項目です。'
    ],
    'required_if' => [
        'errorcode' => 'E400001',
        'message' => ':otherが:valueの場合、:attributeも指定してください。'
    ],
    'required_unless' => ':otherが:valuesでない場合、:attributeを指定してください。',
    'required_with' => ':valuesを指定する場合は、:attributeも指定してください。',
    'required_with_all' => ':valuesを指定する場合は、:attributeも指定してください。',
    'required_without' => ':valuesを指定しない場合は、:attributeを指定してください。',
    'required_without_all' => ':valuesのどれも指定しない場合は、:attributeを指定してください。',
    'same' => ':attributeと:otherには同じ値を指定してください。',
    'size' => [
        'numeric' => ':attributeは:sizeを指定してください。',
        'file' => [
            'errorcode' => 'E400014',
            'message' => ':attributeのファイルは、:sizeキロバイトでなくてはなりません。',
        ],
        'string' => ':attributeは:size文字で指定してください。',
        'array' => ':attributeは:size個指定してください。',
    ],
    'starts_with' => ':attributeには、:valuesのどれかで始まる値を指定してください。',
    'string' => ':attributeは文字列を指定してください。',
    'timezone' => ':attributeには、有効なゾーンを指定してください。',
    'unique' => [
        'errorcode' => 'E400010',
        'message' => '使用されている:attributeです。'
    ],
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'url' => ':attributeに正しい形式を指定してください。',
    'uuid' => ':attributeに有効なUUIDを指定してください。',

    /*
    |--------------------------------------------------------------------------
    | Custom バリデーション言語行
    |--------------------------------------------------------------------------
    |
    | "属性.ルール"の規約でキーを指定することでカスタムバリデーション
    | メッセージを定義できます。指定した属性ルールに対する特定の
    | カスタム言語行を手早く指定できます。
    |
    */

    'custom' => [
        'regex' => [
            'numeric' => [
                'errorcode' => 'E400002',
                'message' => ':attributeは半角数字で入力してください。'
            ],
            'zip_code' => [
                "errorcode" => "E400017",
                "message" => "郵便番号を000-0000の形式で入力してください。"
            ],
            'email' => [
                "errorcode" => "E400009",
                "message" => ":attributeは有効なメールアドレスを入力してください。"
            ],
            'password' => [
                "errorcode" => "E400011",
                "message" => ":attributeは8-32文字の英数字と記号で入力してください。"
            ],
            'role_id' => [
                "errorcode" => "E400002",
                "message" => ":attributeは半角数字で入力してください。"
            ],
            'customer_branch_id' => [
                "errorcode" => "E400002",
                "message" => "顧客支店IDは半角数字で入力してください。"
            ],
            'kana' => [
                "errorcode" => "E400020",
                "message" => ":attributeはカナで入力してください。"
            ],
            'mobile_phone' => [
                "errorcode" => "E400008",
                "message" => ":attributeは10桁か11桁の数字で入力してください。",
            ],
            'belong' => [
                "errorcode" => "E400002",
                "message" => "所属状況は半角数字で入力してください。",
            ],
            'customer_id' => [
                "errorcode" => "E400002",
                "message" => "顧客会社IDは半角数字で入力してください。"
            ],
            'customer_login_id' => [
                "errorcode" => "E400002",
                "message" => ":attributeは有効な会社ログインIDを入力してください。"
            ],
        ],
        'exist' => [
            'role_id' => [
                "errorcode" => "E400006",
                "message" => "指定された:attributeは存在しません。"
            ],
            'incorrect' => [
                "errorcode" => "E400025",
                "message" => ":attributeの入力された値が正しくありません。"
            ],
            'customer_branch_id' => [
                "errorcode" => "E400006",
                "message" => "指定された顧客支店IDは存在しません。"
            ],
            'user_id' => [
                "errorcode" => "E400006",
                "message" => "指定されたユーザーIDは存在しません。"
            ],
        ],
        'boolean' => [
            "errorcode" => "E400007",
            "message" => ":attributeのデータ型が不正です。"
        ],
        'required_if' => [
            'customer_user_tel' => [
                "errorcode" => "E400021",
                "message" => "SMSリマインド送信を希望する場合は、携帯番号を入力してください。"
            ]
        ],
        'unique' => [
            "errorcode" => "E400010",
            "message" => "使用されているメールアドレスです。"
        ],
        'required_parking_data' => [
            'errorcode' => "E400006",
            "message" => "",
        ],
        'file' => [
            'csv' => [
                "errorcode" => "E400018",
                "message" => "CSVファイルのフォーマットに問題があります。"
            ],
            'file_detail_type_valid' => [
                'errorcode' => 'E400006',
                'message' => '指定された:attributeは存在しません。'
            ],
            'encoding' => [
                'errorcode' => 'E400036',
                'message' => 'CSVファイルの文字コードはShift_JISにしてください。'
            ],
            'header' => [
                'errorcode' => 'E400018',
                'message' => 'CSVファイルのフォーマットに問題があります。'
            ],
            'required_without_all_field' => [
                'errorcode' => 'E400001',
                'message' => '工事ID、見積依頼ID、見積ID、契約ID、請求IDのいずれかは必須項目です。'
            ],
            'max_size' =>'ファイルは:size以内で登録してください。',
            'max_item' =>'データが100件を超えています。'
        ],
        'custom_before_or_equal' => [
            "errorcode" => "E400013",
            "message" => ":attributeは、:dateより小さい日付を入力してください。"
        ],
        'required_with_sms_flg' => [
            "errorcode" => "E400021",
            "message" => "SMSリマインド送信を希望する場合は、携帯番号を入力してください。"
        ],
        'not_in' => [
            'role_id' => [
                "errorcode" => "E400006",
                "message" => "指定された役割は存在しません。"
            ]
        ],
        'compare' => [
            'password' => "パスワードが正しくありません。"
        ],
        "is_approval_user" => [
            "errorcode" => "E400024",
            "message" => "入力された申請者と承認者は同じ支店に所属していません。"
        ],
        "in_array" => [
            "errorcode" => "E400006",
            "message" => "指定された:attributeは存在しません。"
        ],
        "is_positive_numeric" => [
            'numeric' => [
                'errorcode' => 'E400002',
                'message' => ':attributeは半角数字で入力してください。'
            ],
        ],
        'required_customer_branch_id' => [
            'errorcode' => 'E400001',
            'message' => '顧客支店IDは必須項目です。'
        ],
        'required' => [
            'errorcode' => 'E400001',
            'message' => ':attributeは必須項目です。'
        ],
        'string' => [
            'errorcode' => 'E400003',
            'message' => ':attributeは半角英数字で入力してください。'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | カスタムバリデーション属性名
    |--------------------------------------------------------------------------
    |
    | 以下の言語行は、例えば"email"の代わりに「メールアドレス」のように、
    | 読み手にフレンドリーな表現でプレースホルダーを置き換えるために指定する
    | 言語行です。これはメッセージをよりきれいに表示するために役に立ちます。
    |
    */

    'error_code' => [
        'E400001' => 'は必須項目です。',
        'E400002' => 'は半角数字で入力してください。',
    ],

    'errorcode' => [
        'E400001' => ':attributeは必須項目です。',
        'E400002' => ':attributeは半角数字で入力してください。',
        'E400023' => 'メッセージの更新には本文、ファイル、ファイル削除のいずれかが必須となります。',
    ],

    'message_422' => 'The given data was invalid.',
    'required_for_read' => ":readか:unreadのどちらかは１を指定してください。",
    'not_true_with' => ":attr1と:attr2の両方をTRUEに指定することは出来ません。",

    'parking_id' => [
        'exist_in_database' => 'List parking id must be a numeric and exists in database'
    ],

    '401_unauthorized' => [
        'errorcode' => 'E400024',
        'message' => '入力された申請者と承認者は同じ支店に所属していません。'
    ],
    'exist_parking_info' => '駐車場情報が存在しません。',
    'record_notfound_error' => [
        'cu_subcontract' => "下請情報が存在しません。",
        'cu_parking' => "駐車場情報が存在しません。",
    ],
    '400_invalid_role_system_owner' => [
        'errorcode' => 'E400022',
        'message' => 'システムオーナーのロールの変更は出来ません。'
    ],
    '400_invalid_user_lock_system_owner' => [
        'errorcode' => 'E400030',
        'message' => 'システムオーナーをロックすることは出来ません。'
    ],
];
