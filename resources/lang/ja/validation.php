<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute フィールドを承諾してください。',
    'accepted_if' => ':other が :value の場合、:attribute フィールドを承諾してください。',
    'active_url' => ':attribute フィールドは有効なURLである必要があります。',
    'after' => ':attribute フィールドは :date より後の日付である必要があります。',
    'after_or_equal' => ':attribute フィールドは :date 以降の日付である必要があります。',
    'alpha' => ':attribute フィールドは文字のみで構成されている必要があります。',
    'alpha_dash' => ':attribute フィールドは文字、数字、ダッシュ、アンダースコアのみで構成されている必要があります。',
    'alpha_num' => ':attribute フィールドは文字と数字のみで構成されている必要があります。',
    'any_of' => ':attribute フィールドが無効です。',
    'array' => ':attribute フィールドは配列である必要があります。',
    'ascii' => ':attribute フィールドは1バイトの英数字と記号のみで構成されている必要があります。',
    'before' => ':attribute フィールドは :date より前の日付である必要があります。',
    'before_or_equal' => ':attribute フィールドは :date 以前の日付である必要があります。',
    'between' => [
        'array' => ':attribute フィールドは :min 個から :max 個の項目を含む必要があります。',
        'file' => ':attribute フィールドは :min キロバイトから :max キロバイトである必要があります。',
        'numeric' => ':attribute フィールドは :min から :max の間である必要があります。',
        'string' => ':attribute フィールドは :min 文字から :max 文字である必要があります。',
    ],
    'boolean' => ':attribute フィールドは true または false である必要があります。',
    'can' => ':attribute フィールドに権限のない値が含まれています。',
    'confirmed' => ':attribute フィールドの確認が一致しません。',
    'contains' => ':attribute フィールドに必須の値が含まれていません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attribute フィールドは有効な日付である必要があります。',
    'date_equals' => ':attribute フィールドは :date と等しい日付である必要があります。',
    'date_format' => ':attribute フィールドは :format 形式と一致する必要があります。',
    'decimal' => ':attribute フィールドは :decimal 桁の小数点以下の桁数を持つ必要があります。',
    'declined' => ':attribute フィールドを辞退してください。',
    'declined_if' => ':other が :value の場合、:attribute フィールドを辞退してください。',
    'different' => ':attribute フィールドと :other は異なる必要があります。',
    'digits' => ':attribute フィールドは :digits 桁である必要があります。',
    'digits_between' => ':attribute フィールドは :min 桁から :max 桁である必要があります。',
    'dimensions' => ':attribute フィールドの画像サイズが無効です。',
    'distinct' => ':attribute フィールドに重複した値があります。',
    'doesnt_contain' => ':attribute フィールドは次の値を含んではいけません：:values。',
    'doesnt_end_with' => ':attribute フィールドは次の値で終わってはいけません：:values。',
    'doesnt_start_with' => ':attribute フィールドは次の値で始まってはいけません：:values。',
    'email' => ':attribute フィールドは有効なメールアドレスである必要があります。',
    'ends_with' => ':attribute フィールドは次の値のいずれかで終わる必要があります：:values。',
    'enum' => '選択された :attribute は無効です。',
    'exists' => '選択された :attribute は無効です。',
    'extensions' => ':attribute フィールドは次の拡張子のいずれかを持つ必要があります：:values。',
    'file' => ':attribute フィールドはファイルである必要があります。',
    'filled' => ':attribute フィールドは値を持つ必要があります。',
    'gt' => [
        'array' => ':attribute フィールドは :value 個より多くの項目を持つ必要があります。',
        'file' => ':attribute フィールドは :value キロバイトより大きい必要があります。',
        'numeric' => ':attribute フィールドは :value より大きい必要があります。',
        'string' => ':attribute フィールドは :value 文字より長い必要があります。',
    ],
    'gte' => [
        'array' => ':attribute フィールドは :value 個以上の項目を持つ必要があります。',
        'file' => ':attribute フィールドは :value キロバイト以上である必要があります。',
        'numeric' => ':attribute フィールドは :value 以上である必要があります。',
        'string' => ':attribute フィールドは :value 文字以上である必要があります。',
    ],
    'hex_color' => ':attribute フィールドは有効な16進数カラーである必要があります。',
    'image' => ':attribute フィールドは画像である必要があります。',
    'in' => '選択された :attribute は無効です。',
    'in_array' => ':attribute フィールドは :other に存在する必要があります。',
    'in_array_keys' => ':attribute フィールドは次のキーの少なくとも1つを含む必要があります：:values。',
    'integer' => ':attribute フィールドは整数である必要があります。',
    'ip' => ':attribute フィールドは有効なIPアドレスである必要があります。',
    'ipv4' => ':attribute フィールドは有効なIPv4アドレスである必要があります。',
    'ipv6' => ':attribute フィールドは有効なIPv6アドレスである必要があります。',
    'json' => ':attribute フィールドは有効なJSON文字列である必要があります。',
    'list' => ':attribute フィールドはリストである必要があります。',
    'lowercase' => ':attribute フィールドは小文字である必要があります。',
    'lt' => [
        'array' => ':attribute フィールドは :value 個未満の項目を持つ必要があります。',
        'file' => ':attribute フィールドは :value キロバイト未満である必要があります。',
        'numeric' => ':attribute フィールドは :value 未満である必要があります。',
        'string' => ':attribute フィールドは :value 文字未満である必要があります。',
    ],
    'lte' => [
        'array' => ':attribute フィールドは :value 個を超える項目を持ってはいけません。',
        'file' => ':attribute フィールドは :value キロバイト以下である必要があります。',
        'numeric' => ':attribute フィールドは :value 以下である必要があります。',
        'string' => ':attribute フィールドは :value 文字以下である必要があります。',
    ],
    'mac_address' => ':attribute フィールドは有効なMACアドレスである必要があります。',
    'max' => [
        'array' => ':attribute フィールドは :max 個を超える項目を持ってはいけません。',
        'file' => ':attribute フィールドは :max キロバイトを超えてはいけません。',
        'numeric' => ':attribute フィールドは :max を超えてはいけません。',
        'string' => ':attribute フィールドは :max 文字を超えてはいけません。',
    ],
    'max_digits' => ':attribute フィールドは :max 桁を超えてはいけません。',
    'mimes' => ':attribute フィールドは次の種類のファイルである必要があります：:values。',
    'mimetypes' => ':attribute フィールドは次の種類のファイルである必要があります：:values。',
    'min' => [
        'array' => ':attribute フィールドは最低 :min 個の項目を持つ必要があります。',
        'file' => ':attribute フィールドは最低 :min キロバイトである必要があります。',
        'numeric' => ':attribute フィールドは最低 :min である必要があります。',
        'string' => ':attribute フィールドは最低 :min 文字である必要があります。',
    ],
    'min_digits' => ':attribute フィールドは最低 :min 桁である必要があります。',
    'missing' => ':attribute フィールドは存在しない必要があります。',
    'missing_if' => ':other が :value の場合、:attribute フィールドは存在しない必要があります。',
    'missing_unless' => ':other が :value でない限り、:attribute フィールドは存在しない必要があります。',
    'missing_with' => ':values が存在する場合、:attribute フィールドは存在しない必要があります。',
    'missing_with_all' => ':values が存在する場合、:attribute フィールドは存在しない必要があります。',
    'multiple_of' => ':attribute フィールドは :value の倍数である必要があります。',
    'not_in' => '選択された :attribute は無効です。',
    'not_regex' => ':attribute フィールドの形式が無効です。',
    'numeric' => ':attribute フィールドは数値である必要があります。',
    'password' => [
        'letters' => ':attribute フィールドは最低1つの文字を含む必要があります。',
        'mixed' => ':attribute フィールドは最低1つの大文字と1つの小文字を含む必要があります。',
        'numbers' => ':attribute フィールドは最低1つの数字を含む必要があります。',
        'symbols' => ':attribute フィールドは最低1つの記号を含む必要があります。',
        'uncompromised' => '指定された :attribute はデータ漏洩で発見されました。別の :attribute を選択してください。',
    ],
    'present' => ':attribute フィールドが存在する必要があります。',
    'present_if' => ':other が :value の場合、:attribute フィールドが存在する必要があります。',
    'present_unless' => ':other が :value でない限り、:attribute フィールドが存在する必要があります。',
    'present_with' => ':values が存在する場合、:attribute フィールドが存在する必要があります。',
    'present_with_all' => ':values が存在する場合、:attribute フィールドが存在する必要があります。',
    'prohibited' => ':attribute フィールドは禁止されています。',
    'prohibited_if' => ':other が :value の場合、:attribute フィールドは禁止されています。',
    'prohibited_if_accepted' => ':other が承諾された場合、:attribute フィールドは禁止されています。',
    'prohibited_if_declined' => ':other が辞退された場合、:attribute フィールドは禁止されています。',
    'prohibited_unless' => ':other が :values に含まれない限り、:attribute フィールドは禁止されています。',
    'prohibits' => ':attribute フィールドは :other の存在を禁止します。',
    'regex' => ':attribute フィールドの形式が無効です。',
    'required' => ':attribute フィールドは必須です。',
    'required_array_keys' => ':attribute フィールドは次のエントリを含む必要があります：:values。',
    'required_if' => ':other が :value の場合、:attribute フィールドは必須です。',
    'required_if_accepted' => ':other が承諾された場合、:attribute フィールドは必須です。',
    'required_if_declined' => ':other が辞退された場合、:attribute フィールドは必須です。',
    'required_unless' => ':other が :values に含まれない限り、:attribute フィールドは必須です。',
    'required_with' => ':values が存在する場合、:attribute フィールドは必須です。',
    'required_with_all' => ':values が存在する場合、:attribute フィールドは必須です。',
    'required_without' => ':values が存在しない場合、:attribute フィールドは必須です。',
    'required_without_all' => ':values がいずれも存在しない場合、:attribute フィールドは必須です。',
    'same' => ':attribute フィールドは :other と一致する必要があります。',
    'size' => [
        'array' => ':attribute フィールドは :size 個の項目を含む必要があります。',
        'file' => ':attribute フィールドは :size キロバイトである必要があります。',
        'numeric' => ':attribute フィールドは :size である必要があります。',
        'string' => ':attribute フィールドは :size 文字である必要があります。',
    ],
    'starts_with' => ':attribute フィールドは次の値のいずれかで始まる必要があります：:values。',
    'string' => ':attribute フィールドは文字列である必要があります。',
    'timezone' => ':attribute フィールドは有効なタイムゾーンである必要があります。',
    'unique' => ':attribute は既に使用されています。',
    'uploaded' => ':attribute のアップロードに失敗しました。',
    'uppercase' => ':attribute フィールドは大文字である必要があります。',
    'url' => ':attribute フィールドは有効なURLである必要があります。',
    'ulid' => ':attribute フィールドは有効なULIDである必要があります。',
    'uuid' => ':attribute フィールドは有効なUUIDである必要があります。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'start_time_past' => '開始時刻は現在時刻より前に設定できません。',
        'start_time_minimum' => '開始日時は現在から最低1時間後である必要があります。',
        'end_time_minimum' => '終了時刻は開始時刻から最低3時間後である必要があります。',
        'password_required' => '新しいパスワードを入力してパスワードを変更してください。',
        'upload_valid_image' => '5MB以下の有効なJPG、JPEG、PNG、またはWEBP画像をアップロードしてください。',
        'invalid_operation' => 'オペレーションはline_crossing_counterまたはregion_people_counterのみサポートしています',
        'invalid_access_token' => 'アクセストークンまたはシークレットキーが無効です。',
        'commands_required' => '実行するコマンドを少なくとも1つ提供してください。',
        'invalid_device_type' => '選択されたデバイス種別が無効です。有効なオプションを選択してください。',
        'commands_valid_array' => 'コマンドは有効な配列形式で送信する必要があります。',

        'date_time.date_format' => '日時はYYYY-MM-DD HH:MM形式である必要があります (例:2025-09-11 14:30)。',

        'file_confirmation'  => '削除を確認するため、ファイル名を入力してください。',
        'file_name_mismatch' => 'ファイル名が一致しません。',

        'water_tier_required' => '少なくとも1つの料金段階が必要です。',
        'water_tier_min_cubic_meters_required' => '最小立方メートルの項目は必須です。',
        'water_tier_min_cubic_meters_numeric' => '最小立方メートルは数値でなければなりません。',
        'water_tier_max_cubic_meters_required' => '最大立方メートルの項目は必須です。',
        'water_tier_max_cubic_meters_numeric' => '最大立方メートルは数値でなければなりません。',
        'water_tier_max_cubic_meters_gt' => '最大立方メートルは最小立方メートルより大きくなければなりません。',
        'water_tier_rate_per_cubic_meter_required' => '立方メートルあたりの料金の項目は必須です。',
        'water_tier_rate_per_cubic_meter_numeric' => '立方メートルあたりの料金は数値でなければなりません。',
        'password_mismatch' => 'パスワードが一致しません。再度入力してください。',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
