<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application
    |--------------------------------------------------------------------------
    */

    'input.required' => 'Please fill out this field',

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
    'admin.input.duplicate_username' => 'Username has already been taken.',
    'admin.input.special_character' => 'Username cannot contain special characters',
    'admin.input.invalid_username_length' => 'The length of username must between 4 to 20',

    'admin.internal_error' => 'Internal Error.',
    'admin.duplicate_username' => 'Duplicate username!',
    'admin.username.alphanumericWithDot' => 'Username must be in alphanumeric',
    'admin.invalid_status' => 'Invalid status.',
    'admin.invalid_newpassword' => 'Invalid password',
    'admin.invalid_password' => 'Invalid password.',
    'admin.invalid_password_length' => 'Password must be 8-15 characters in length.',
    'admin.invalid_currentpassword' => 'Invalid current password.',
    'admin.passwordscannotsame' => '"Current Password" and "New Password" cannot be same.',
    'admin.passwordsnotmatch' => '"New Password" and "Confirm New Password" not match.',
    'admin.password.input' => 'Password must be in alphanumeric or symbol',

    /*
    |--------------------------------------------------------------------------
    | Downline Controller
    |--------------------------------------------------------------------------
    */
    'merchant.input.duplicate_username' => 'Username has already been taken.',
    'merchant.input.special_character' => 'Username cannot contain special characters',
    'merchant.input.invalid_username_length' => 'The length of username must between 4 to 20',

    'merchant.internal_error' => 'Internal Error.',
    'merchant.duplicate_username' => 'Duplicate Username!',
    'merchant.username.alphanumericWithDot' => 'Username must be in alphanumeric',
    'merchant.insufficient_credit' =>'Insufficient Credit (Please contact your upline to deposit credit)',
    'merchant.invalid_status' => 'Invalid Status.',
    'merchant.invalid_suspended' => 'Invalid Suspended.',
    'merchant.invalid_currency' => 'Invalid Currency.',
    'merchant.invalid_credit' => 'Invalid Credit',
    'merchant.invalid_credit_length' => 'Credit amount cannot exceed 15 digits',
    'merchant.invalid_adjustment_type' => 'Invalid Adjustment Type',
    'merchant.invalid_password_length' => 'Password must be 8-15 characters in length.',
    'merchant.passwords_not_match' => 'Password confirmation does not match',
    'merchant.fullname.alphanumeric' => 'Alias must be in alphanumeric',
    'merchant.fullname.4_20' => 'Alias must be 4 to 20 in length',
    'merchant.credit.is_numeric' => 'Credit amount must in numeric and cannot be smaller than 1',
    'merchant.credit.nonnegative' => 'Credit amount must larger than zero.',
    'merchant.invalid_comm' => 'Invalid Commission',
    'merchant.invalid_evopt' => 'Invalid Evo PT',
    'merchant.invalid_habapt' => 'Invalid Haba PT',
    'merchant.invalid_wmpt' => 'Invalid WM PT',
    'merchant.invalid_pragpt' => 'Invalid Prag PT',
    'merchant.invalid_upperevo' => 'Evo PT larger than upper given PT',
    'merchant.invalid_upperhaba' => 'Haba pt larger than upper given PT',
    'merchant.invalid_upperwm' => 'WM pt larger than upper given PT',
    'merchant.invalid_upperprag' => 'Prag pt larger than upper given PT',
    'merchant.invalid_downevo' => 'Evo PT less than downline given PT',
    'merchant.invalid_downhaba' => 'Haba PT less than downline given PT',
    'merchant.invalid_downwm' => 'WM PT less than downline given PT',
    'merchant.invalid_downprag' => 'Prag PT less than downline given PT',
    'merchant.invalid_regcode' => 'registration code must in alphanumeric',
    'merchant.duplicate_regcode' => 'Registration code has already been taken',
    'merchant.invalid_regcode_length' => 'Registration code must be 5 in length',
    'merchant.password.input' => 'Password must be in alphanumeric or symbol',

    /*
    |--------------------------------------------------------------------------
    | Subaccount Controller
    |--------------------------------------------------------------------------
    */
    'subaccount.input.duplicate_username' => 'The username has already been taken.',
    'subaccount.input.special_character' => 'The username cannot contain special characters',
    'subaccount.input.invalid_username_length' => 'The length of username must between 4 to 20',

    'subaccount.internal_error' => 'Internal Error.',
    'subaccount.duplicate_username' => 'Duplicate username!',
    'subaccount.invalid_status' => 'Invalid status.',
    'subaccount.invalid_newpassword' => 'Invalid password.',
    'subaccount.invalid_password' => 'Invalid password.',
    'subaccount.invalid_password_length' => 'Password must be 8-15 characters in length.',
    'subaccount.invalid_currentpassword' => 'Invalid current password.',
    'subaccount.passwordscannotsame' => '"Current Password" and "New Password" cannot be same.',
    'subaccount.passwordsnotmatch' => '"New Password" and "Confirm New Password" not match.',

     /*
    |--------------------------------------------------------------------------
    | Member Controller
    |--------------------------------------------------------------------------
    */
    'member.input.duplicate_username' => 'Username has already been taken.',
    'member.input.duplicate_email' => 'Email has already been taken',
    'member.input.special_character' => 'Username cannot contain special characters',
    'member.input.invalid_username_length' => 'The length of username must between 4 to 20',

    'member.internal_error' => 'Internal Error.',
    'member.duplicate_username' => 'Duplicate username!',
    'member.username.alphanumericWithDot' => 'Username must be in alphanumeric',
    'member.invalid_status' => 'Invalid status.',
    'member.invalid_password' => 'Invalid password.',
    'member.invalid_password_length' => 'Password must be 8-15 characters in length.',
    'member.passwordsnotmatch' => 'Password confirmation does not match',
    'member.fullname.alphabetWithSpace' => 'Name must be in alphabet',
    'member.fullname.empty' => 'Name cannot be empty',
    'member.credit.is_numeric' => 'Credit amount must in numeric and cannot be smaller than 1',
    'member.credit.nonnegative' => 'Credit amount must larger than zero.',
    'member.invalid_credit_length' => 'Credit amount cannot exceed 15 digits',
    'member.insufficient_credit' =>'Insufficient Credit (Please contact your agent to deposit credit)',
    'member.password.input' => 'Password must be in alphanumeric or symbol',
    'member.input.mobile_numeric' => 'Mobile No must be in digit number',
    'member.input.invalid_email'  => 'Invalid Email Format',

      /*
    |--------------------------------------------------------------------------
    | Credit Controller
    |--------------------------------------------------------------------------
    */
    'credit.merchant.insufficient_credit' =>'Insufficient Credit (Please contact your upline to deposit credit)',
    'credit.merchant.exceed_limit' =>'Withdraw amount is exceeded available credit',
    'credit.member.invalid_credit' =>'Invalid Credit',
    'credit.merchant.invalid_credit' =>'Invalid Credit',
    'credit.member.insufficient_credit' =>'Insufficient Credit (Please contact your agent to deposit credit)',
    'credit.member.exceed_limit' => 'Withdraw amount is exceeded available credit',
    'credit.merchant.nonnegative' =>'Credit amount must larger than zero.',
    'credit.member.nonnegative' =>'Credit amount must larger than zero.',

    /*
    |--------------------------------------------------------------------------
    | GameList Controller
    |--------------------------------------------------------------------------
    */
    'gamelist.internal_error' => 'Internal Error.',
    'gamelist.fail' => 'Update Fail.',

    /*
    |--------------------------------------------------------------------------
    | DefaultAG Controller
    |--------------------------------------------------------------------------
    */
    'ag.invalid' => 'Agent does not exist in default list', 

    /*
    |--------------------------------------------------------------------------
    | BankInfo Controller
    |--------------------------------------------------------------------------
    */
    'bank.info.duplicate' => 'Duplicate Bank Acc',
    'bank.info.invalid' => 'Invalid Bank Info',
    'bank.info.bankname.empty' => 'Bank Name cannot be empty',
    'bank.info.holdername.empty' => 'Bank Account Holder Name cannot be empty',
    'bank.info.bankname.alphabet' => 'Bank Name must be in alphabet',
    'bank.info.holdername.alphabet' => 'Bank Account Holder Name must be in alphabet',
    'bank.info.accno.numeric' => 'Bank Account No must be in digit number',
    'bank.info.max' => 'Only can have max to 1 open bank account',

    /*
    |--------------------------------------------------------------------------
    | Member Message Controller
    |--------------------------------------------------------------------------
    */
    'member.msg.nomember' =>'No Members Selected',
    'member.msg.nomsg' =>'No Message Selected',
    'member.msg.insertmsg' =>'Please Insert The Message',
    'member.msg.insertsubject' =>'Please Insert The Subject',
    'member.msg.invalidmember' => 'Invalid Member',
    'member.msg.invalidmember' => 'Invalid Message',
    'member.msg.internal_error' => 'Internal Error',

    /*
    |--------------------------------------------------------------------------
    | MemberDW Controller
    |--------------------------------------------------------------------------
    */
    'memberdw.invalid_process' => 'Invalid process',
    'memberdw.txnprocess' => 'Txn already processed',
    'memberdw.insufficient_credit' => 'Agent credit is not enough for deposit',
    'memberdw.internal_error' => 'Internal Error',



    /*
    |--------------------------------------------------------------------------
    | Crypto Controller
    |--------------------------------------------------------------------------
    */

    'crypto.ratevalue.is_amount' => 'Rate Value must in amount and cannot be smaller than 0',

];
