<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application
    |--------------------------------------------------------------------------
    */

    'input.required' => '请填写此空格',

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    'login.account_inactive' => 'Your account is inactive. Please contact your upline.',
    'login.multiple_login' => 'You have signed in other device',

   /*
    |--------------------------------------------------------------------------
    | Admin Controller
    |--------------------------------------------------------------------------
    */
    'admin.input.duplicate_username' => '用户名已被使用',
    'admin.input.special_character' => '用户名不能有特殊字符',
    'admin.input.invalid_username_length' => '用户名只能在4到20字数之间',

    'admin.internal_error' => '内部错误',
    'admin.duplicate_username' => '用户名已被使用！',
    'admin.username.alphanumericWithDot' => '用户名只能字母数字',
    'admin.invalid_newpassword' => '密码无效',
    'admin.invalid_status' => '状态无效.',
    'admin.invalid_password' => '密码无效.',
    'admin.invalid_password_length' => '密码只能在8至15字数之间',
    'admin.invalid_currentpassword' => '当前密码无效.',
    'admin.passwordscannotsame' => '当前密码和新密码不可以一样',
    'admin.passwordsnotmatch' => '新密码和确认密码不匹配',
    'admin.password.input' => '密碼只能字母數字或者符号',

    /*
    |--------------------------------------------------------------------------
    | Downline Controller
    |--------------------------------------------------------------------------
    */
    'merchant.input.duplicate_username' => '用户名已被使用',
    'merchant.input.special_character' => '用户名不能有特殊字符',
    'merchant.input.invalid_username_length' => '用户名只能在4到20字数之间',

    'merchant.internal_error' => '内部错误',
    'merchant.duplicate_username' => '用户名已被使用！',
    'merchant.username.alphanumericWithDot' => '用户名只能字母数字',
    'merchant.insufficient_credit' =>'余额不足 (请联系上家存款)',
    'merchant.invalid_status' => '状态无效',
    'merchant.invalid_suspended' => '暂停无效.',
    'merchant.invalid_currency' => '货币无效',
    'merchant.invalid_credit' => '信用额度无效',
    'merchant.invalid_credit_length' => '数额不可以超过15字数',
    'merchant.invalid_password_length' => '密码只能在8至15字数之间',
    'merchant.passwords_not_match' => '密码和确认密码不匹配',
    'merchant.fullname.alphanumeric' => '别名只能字母数字',
    'merchant.fullname.4_20' => '别名只能在4到20字数之间',
    'merchant.credit.is_numeric' => '信用额度只能数字并且不能小于1',
    'merchant.credit.nonnegative' => '信用额度要大过0',
    'merchant.invalid_comm' => '佣金无效',
    'merchant.invalid_evopt' => 'Evo的占成数无效',
    'merchant.invalid_habapt' => 'Haba的占成数无效',
    'merchant.invalid_wmpt' => 'WM的占成数无效',
    'merchant.invalid_pragpt' => 'Prag的占成数无效',
    'merchant.invalid_upperevo' => 'Evo占成数大过上线给予的占成数',
    'merchant.invalid_upperhaba' => 'Haba占成数大过上线给予的占成数',
    'merchant.invalid_upperwm' => 'WM占成数大过上线给予的占成数',
    'merchant.invalid_upperprag' => 'Prag占成数大过上线给予的占成数',
    'merchant.invalid_downevo' => 'Evo占成数少过下线给予的的占成数',
    'merchant.invalid_downhaba' => 'Haba占成数少过下线给予的的占成数',
    'merchant.invalid_downwm' => 'WM占成数少过下线给予的的占成数',
    'merchant.invalid_downprag' => 'Prag占成数少过下线给予的的占成数',
    'merchant.invalid_regcode' => '注册码只能字母数字',
    'merchant.duplicate_regcode' => '注册码已被使用',
    'merchant.invalid_regcode_length' => '注册码一定要5字数',
    'merchant.password.input' => '密碼只能字母數字或者符号',

    /*
    |--------------------------------------------------------------------------
    | Subaccount Controller
    |--------------------------------------------------------------------------
    */
    'subaccount.input.duplicate_username' => '用户名已被使用',
    'subaccount.input.special_character' => '用户名不能有特殊字符',
    'subaccount.input.invalid_username_length' => '用户名只能在4到20字数之间',

    'subaccount.internal_error' => '内部错误',
    'subaccount.duplicate_username' => '用户名已被使用！',
    'subaccount.invalid_status' => '状态无效',
    'subaccount.invalid_newpassword' => '密码无效',
    'subaccount.invalid_password' => '密码无效',
    'subaccount.invalid_password_length' => '密码只能在8至15字数之间',
    'subaccount.invalid_currentpassword' => '当前密码无效.',
    'subaccount.passwordscannotsame' => '当前密码和密码不可以一样',
    'subaccount.passwordsnotmatch' => '密码和确认密码不匹配',

    /*
    |--------------------------------------------------------------------------
    | Member Controller
    |--------------------------------------------------------------------------
    */
    'member.input.duplicate_username' => '用户名已被使用',
    'member.input.duplicate_email' => '电子邮件已被使用',
    'member.input.special_character' => '用户名不能有特殊字符',
    'member.input.invalid_username_length' => '用户名只能在4到20字数之间',

    'member.internal_error' => '内部错误',
    'member.duplicate_username' => '用户名已被使用！',
    'member.username.alphanumericWithDot' => '用户名只能字母数字',
    'member.invalid_status' => '状态无效',
    'member.invalid_password' => '密码无效',
    'member.invalid_password_length' => '密码只能在8至15字数之间',
    'member.passwordsnotmatch' => '密码和确认密码不匹配',
    'member.fullname.alphabetWithSpace' => '真实姓名只能字母',
    'member.fullname.empty' => '真实姓名不可以空格',
    'member.credit.nonnegative' => '信用额度要大过0',
    'member.credit.is_numeric' => '信用额度只能数字并且不能小于1',
    'member.credit.nonnegative' => '信用额度要大过0',
    'member.invalid_credit_length' => '数额不可以超过15字数',
    'member.insufficient_credit' =>'余额不足 (请联系上家存款)',
    'member.password.input' => '密碼只能字母數字或者符号',
    'member.input.mobile_numeric' => '手机号码必须是号码',
    'member.input.invalid_email'  => '电子邮件无效',

      /*
    |--------------------------------------------------------------------------
    | Credit Controller
    |--------------------------------------------------------------------------
    */
    'credit.merchant.insufficient_credit' =>'余额不足 (请联系你的上家存款)',
    'credit.merchant.exceed_limit' =>'提款的数额超过余额',
    'credit.merchant.invalid_credit' =>'信用额度无效',
    'credit.member.invalid_credit' =>'信用额度无效',
    'credit.member.insufficient_credit' =>'余额不足 (请联系你的上家存款)',
    'credit.member.exceed_limit' =>'提款的数额超过余额',
    'credit.merchant.nonnegative' =>'信用额度要大过0',
    'credit.member.nonnegative' =>'信用额度要大过0',

    /*
    |--------------------------------------------------------------------------
    | GameList Controller
    |--------------------------------------------------------------------------
    */
    'gamelist.internal_error' => '内部错误',
    'gamelist.fail' => '更新失败.',

    /*
    |--------------------------------------------------------------------------
    | DefaultAG Controller
    |--------------------------------------------------------------------------
    */
    'ag.invalid' => '列表中不存在此代理',

    /*
    |--------------------------------------------------------------------------
    | BankInfo Controller
    |--------------------------------------------------------------------------
    */
    'bank.info.duplicate' => '银行户口重复了',
    'bank.info.invalid' => '银行资料无效',
    'bank.info.bankname.empty' => '银行名字不可以填写空白',
    'bank.info.holdername.empty' => '银行户口持有人不可以填写空白',
    'bank.info.bankname.alphabet' => '银行名字只能填写字母',
    'bank.info.holdername.alphabet' => '银行户口持有人只能填写字母',
    'bank.info.accno.numeric' => '银行户口号码只能填写号码',
    'bank.info.max' => '最多只能有1个公开的银行户口',

    /*
    |--------------------------------------------------------------------------
    | Member Message Controller
    |--------------------------------------------------------------------------
    */
    'member.msg.nomember' =>'请选择用户',
    'member.msg.nomsg' =>'请选择信息',
    'member.msg.insertmsg' =>'请填写信息',
    'member.msg.insertsubject' =>'请填写主题',
    'member.msg.invalidmember' => '用户无效',
    'member.msg.invalidmsg' => '信息无效',
    'member.msg.internal_error' => '内部错误',

    /*
    |--------------------------------------------------------------------------
    | MemberDW Controller
    |--------------------------------------------------------------------------
    */
    'memberdw.invalid_process' => '处理无效',
    'memberdw.txnprocess' => '交易已处理',
    'memberdw.insufficient_credit' => '上家余额不足',
    'memberdw.internal_error' => '内部错误',
];
