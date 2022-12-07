<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application
    |--------------------------------------------------------------------------
    */

    'input.required' => 'الرجاء ملء هذه الخانة',

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    'login.account_inactive' => 'حسابك غير نشط. يرجى الاتصال بلين الخاص.',
    'login.multiple_login' => 'كنت قد وقعت في جهاز آخر',

    /*
    |--------------------------------------------------------------------------
    | Admin Controller
    |--------------------------------------------------------------------------
    */
    'admin.input.duplicate_username' => 'لقد تم مسبقا أخذ اسم المستخدم.',
    'admin.input.special_character' => 'اسم المستخدم لا يمكن أن تحتوي على أحرف خاصة',
    'admin.input.invalid_username_length' => 'طول بد منه اسم المستخدم بين 4-20',

    'admin.internal_error' => 'خطأ داخلي.',
    'admin.duplicate_username' => 'اسم المستخدم مكررة!',
    'admin.username.alphanumericWithDot' => 'يجب أن يكون اسم المستخدم أبجديًا رقميًا',
    'admin.invalid_status' => 'حالة غير صالحة.',
    'admin.invalid_newpassword' => 'رمز مرور خاطئ',
    'admin.invalid_password' => 'رمز مرور خاطئ.',
    'admin.invalid_password_length' => 'يجب أن تكون كلمة 8-15 حرفا.',
    'admin.invalid_currentpassword' => 'كلمة مرور غير صحيحة.',
    'admin.passwordscannotsame' => '"كلمة السر الحالي" و "كلمة السر الجديدة" لا يمكن أن يكون نفسه.',
    'admin.passwordsnotmatch' => '"كلمة السر الجديدة" و "تأكيد كلمة السر الجديدة" لا تتطابق.',
    'admin.password.input' => 'يجب أن تكون كلمة في أبجدية أو رمز',

    /*
    |--------------------------------------------------------------------------
    | Downline Controller
    |--------------------------------------------------------------------------
    */
    'merchant.input.duplicate_username' => 'لقد تم مسبقا أخذ اسم المستخدم.',
    'merchant.input.special_character' => 'اسم المستخدم لا يمكن أن تحتوي على أحرف خاصة',
    'merchant.input.invalid_username_length' => 'طول بد منه اسم المستخدم بين 4-20',

    'merchant.internal_error' => 'خطأ داخلي.',
    'merchant.duplicate_username' => 'تكرار اسم المستخدم!',
    'merchant.username.alphanumericWithDot' => 'يجب أن يكون اسم المستخدم أبجديًا رقميًا',
    'merchant.insufficient_credit' => 'الائتمان غير كاف (يرجى الاتصال بلين الخاص على الائتمان إيداع)',
    'merchant.invalid_status' => 'الحالة غير صالحة.',
    'merchant.invalid_suspended' => 'موقوف غير صالح.',
    'merchant.invalid_currency' => 'العملات غير صالحة.',
    'merchant.invalid_credit' => 'غير صالح الائتمان',
    'merchant.invalid_credit_length' => 'مبلغ الائتمان لا يمكن أن يتجاوز 15 رقما',
    'merchant.invalid_password_length' => 'يجب أن تكون كلمة 8-15 حرفا.',
    'merchant.passwords_not_match' => 'لا يتطابق مع تأكيد كلمة المرور',
    'merchant.fullname.alphanumeric' => 'يجب أن يكون الاسم المستعار في أبجدية',
    'merchant.fullname.4_20' => 'يجب أن يكون الاسم المستعار 4-20 في الطول',
    'merchant.credit.is_numeric' => 'يجب مبلغ الائتمان في الرقمية ولا يمكن أن يكون أصغر من 1',
    'merchant.credit.nonnegative' => 'مبلغ الائتمان بد أكبر من الصفر.',
    'merchant.invalid_comm' => 'لجنة غير صالح',
    'merchant.invalid_evopt' => 'ايفو PT غير صالح',
    'merchant.invalid_habapt' => 'هابا PT غير صالح',
    'merchant.invalid_wmpt' => 'WM PT غير صالح',
    'merchant.invalid_pragpt' => 'براغ PT غير صالح',
    'merchant.invalid_upperevo' => 'ايفو PT أكبر من PT نظرا العلوي',
    'merchant.invalid_upperhaba' => 'هابا PT أكبر من PT نظرا العلوي',
    'merchant.invalid_upperwm' => 'WM PT أكبر من PT نظرا العلوي',
    'merchant.invalid_upperprag' => 'براغ PT أكبر من PT نظرا العلوي',
    'merchant.invalid_downevo' => 'ايفو PT أقل من دوونلين نظرا PT',
    'merchant.invalid_downhaba' => 'هابا PT أقل من دوونلين نظرا PT',
    'merchant.invalid_downwm' => 'WM PT أقل من دوونلين نظرا PT',
    'merchant.invalid_downprag' => 'براغ PT أقل من دوونلين نظرا PT',
    'merchant.invalid_regcode' => 'يجب رمز التسجيل في أبجدية',
    'merchant.duplicate_regcode' => 'وقد اتخذت بالفعل رمز التسجيل',
    'merchant.invalid_regcode_length' => 'يجب أن يكون رمز التسجيل 5 في الطول',
    'merchant.password.input' => 'يجب أن تكون كلمة في أبجدية أو رمز',

    /*
    |--------------------------------------------------------------------------
    | Subaccount Controller
    |--------------------------------------------------------------------------
    */
    'subaccount.input.duplicate_username' => 'وقد تم بالفعل اتخاذ اسم المستخدم.',
    'subaccount.input.special_character' => 'لا يمكن أن يحتوي اسم المستخدم الأحرف الخاصة',
    'subaccount.input.invalid_username_length' => 'طول بد منه اسم المستخدم بين 4-20',

    'subaccount.internal_error' => 'خطأ داخلي.',
    'subaccount.duplicate_username' => 'اسم المستخدم مكررة!',
    'subaccount.invalid_status' => 'حالة غير صالحة.',
    'subaccount.invalid_newpassword' => 'رمز مرور خاطئ.',
    'subaccount.invalid_password' => 'رمز مرور خاطئ.',
    'subaccount.invalid_password_length' => 'يجب أن تكون كلمة 8-15 حرفا.',
    'subaccount.invalid_currentpassword' => 'كلمة مرور غير صحيحة.',
    'subaccount.passwordscannotsame' => '"كلمة السر الحالي" و "كلمة السر الجديدة" لا يمكن أن يكون نفسه.',
    'subaccount.passwordsnotmatch' => '"كلمة السر الجديدة" و "تأكيد كلمة السر الجديدة" لا تتطابق.',

     /*
    |--------------------------------------------------------------------------
    | Member Controller
    |--------------------------------------------------------------------------
    */
    'member.input.duplicate_username' => 'لقد تم مسبقا أخذ اسم المستخدم.',
    'member.input.special_character' => 'اسم المستخدم لا يمكن أن تحتوي على أحرف خاصة',
    'member.input.invalid_username_length' => 'طول بد منه اسم المستخدم بين 4-20',
    'member.input.duplicate_email' => 'لقد اخذ الايميل من قبل',
    'member.internal_error' => 'خطأ داخلي.',
    'member.duplicate_username' => 'اسم المستخدم مكررة!',
    'member.username.alphanumericWithDot' => 'يجب أن يكون اسم المستخدم أبجديًا رقميًا',,
    'member.invalid_status' => 'حالة غير صالحة.',
    'member.invalid_password' => 'رمز مرور خاطئ.',
    'member.invalid_password_length' => 'يجب أن تكون كلمة 8-15 حرفا.',
    'member.passwordsnotmatch' => 'لا يتطابق مع تأكيد كلمة المرور',
    'member.fullname.alphabetWithSpace' => 'يجب أن يكون الاسم في الأبجدية',
    'member.fullname.empty' => 'اسم لا يمكن إفراغ',
    'member.credit.is_numeric' => 'يجب مبلغ الائتمان في الرقمية ولا يمكن أن يكون أصغر من 1',
    'member.credit.nonnegative' => 'مبلغ الائتمان بد أكبر من الصفر.',
    'member.invalid_credit_length' => 'مبلغ الائتمان لا يمكن أن يتجاوز 15 رقما',
    'member.insufficient_credit' => 'الائتمان غير كاف (يرجى الاتصال وكيلك على القروض الودائع)',
    'member.password.input' => 'يجب أن تكون كلمة في أبجدية أو رمز',
    'member.input.mobile_numeric' => 'يجب أن يكون رقم الجوال في  الخانة',
    'member.input.invalid_email'  => 'تنسيق البريد الإلكتروني غير صالح',

      /*
    |--------------------------------------------------------------------------
    | Credit Controller
    |--------------------------------------------------------------------------
    */
    'credit.merchant.insufficient_credit' => 'الائتمان غير كاف (يرجى الاتصال بلين الخاص على الائتمان إيداع)',
    'credit.merchant.exceed_limit' => 'تم تجاوز سحب مبلغ الائتمان المتاح',
    'credit.member.invalid_credit' => 'غير صالح الائتمان',
    'credit.merchant.invalid_credit' => 'غير صالح الائتمان',
    'credit.member.insufficient_credit' => 'الائتمان غير كاف (يرجى الاتصال وكيلك على القروض الودائع)',
    'credit.member.exceed_limit' => 'تم تجاوز سحب مبلغ الائتمان المتاح',
    'credit.merchant.nonnegative' => 'مبلغ الائتمان بد أكبر من الصفر.',
    'credit.member.nonnegative' => 'مبلغ الائتمان بد أكبر من الصفر.',

    /*
    |--------------------------------------------------------------------------
    | GameList Controller
    |--------------------------------------------------------------------------
    */
    'gamelist.internal_error' => 'خطأ داخلي.',
    'gamelist.fail' => 'تحديث فشل.',

    /*
    |--------------------------------------------------------------------------
    | DefaultAG Controller
    |--------------------------------------------------------------------------
    */
    'ag.invalid' => 'وكيل غير موجود في القائمة الافتراضية',

    /*
    |--------------------------------------------------------------------------
    | BankInfo Controller
    |--------------------------------------------------------------------------
    */
    'bank.info.duplicate' => 'تكرار اسم البنك',
    'bank.info.invalid' => 'بنك المعلومات غير صالح',
    'bank.info.bankname.empty' => 'اسم البنك لا يمكن إفراغ',
    'bank.info.holdername.empty' => 'حساب مصرفي اسم حامل لا يمكن أن يكون فارغا',
    'bank.info.bankname.alphabet' => 'يجب أن يكون اسم البنك في الأبجدية',
    'bank.info.holdername.alphabet' => 'يجب أن يكون الحساب المصرفي اسم حامل في الأبجدية',
    'bank.info.accno.numeric' => 'يجب أن يكون رقم الحساب البنكي في الخانة',
    'bank.info.max' => 'فقط يمكن أن يكون لديك ما يصل إلى 3 حساب مصرفي مفتوح',

    /*
    |--------------------------------------------------------------------------
    | Member Message Controller
    |--------------------------------------------------------------------------
    */
    'member.msg.nomember' => 'تحديد أي الأعضاء',
    'member.msg.nomsg' => 'لا رسالة مختارة',
    'member.msg.insertmsg' => 'الرجاء إدخال رسالة',
    'member.msg.insertsubject' => 'الرجاء إدخال موضوع',
    'member.msg.invalidmember' => 'عضو غير صالح',
    'member.msg.invalidmember' => 'غير صالح رسالة',
    'member.msg.internal_error' => 'خطأ داخلي',

    /*
    |--------------------------------------------------------------------------
    | MemberDW Controller
    |--------------------------------------------------------------------------
    */
    'memberdw.invalid_process' => 'عملية غير صالحة',
    'memberdw.txnprocess' => 'TXN معالجتها بالفعل',
    'memberdw.insufficient_credit' => 'الائتمان وكيل ليست كافية للإيداع',
    'memberdw.internal_error' => 'خطأ داخلي',

];
