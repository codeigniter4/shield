<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Shield\Language\fa;

return [
    // Exceptions
    'unknownAuthenticator'  => '{0} احراز هویت معتبری نمی باشد.',
    'unknownUserProvider'   => 'قادر به تعیین ارائه دهنده کاربر برای استفاده نیست.',
    'invalidUser'           => 'قادر به پیداکردن کاربر مشخص شده نیست',
    'badAttempt'            => 'امکان ورود به سیستم نیست. لطفا اعتبارنامه خود را بررسی کنید.',
    'noPassword'            => 'تایید کاربر بدون رمز عبور ممکن نیست.',
    'invalidPassword'       => 'ناتوان در ورود به سیستم. لطفا رمز عبور خود را بررسی کنید.',
    'noToken'               => 'هر درخواست باید دارای یک توکن bearer در هدر Authorization باشد.',
    'badToken'              => 'توکن دسترسی معتبر نمی باشد.',
    'oldToken'              => 'توکن دسترسی منقضی شده است.',
    'noUserEntity'          => 'برای اعتبار سنجی هویت کاربر بایستی رمز عبور ارائه شود',
    'invalidEmail'          => 'امکان تایید ایمیلی که با آدرس ایمیل ثبت شده یکسان نیست، وجود ندارد.',
    'unableSendEmailToUser' => 'متاسفانه, در ارسال ایمیل مشکلی پیش آمد. ما نتوانستیم ایمیلی را به "{0}" ارسال کنیم.',
    'throttled'             => 'درخواست های بسیار زیادی از این آدرس IP انجام شده است. می توانید بعد از {0} ثانیه دوباره امتحان کنید.',

    'email'           => 'آدرس ایمیل',
    'username'        => 'نام کاربری',
    'password'        => 'رمز عبور',
    'passwordConfirm' => 'رمز عبور (تکرار)',
    'haveAccount'     => 'از قبل حساب کاربری دارید؟',

    // Buttons
    'confirm' => 'تایید',
    'send'    => 'ارسال',

    // Registration
    'register'         => 'ثبت نام',
    'registerDisabled' => 'اکنون امکان ثبت نام وجود ندارد.',
    'registerSuccess'  => 'خوش آمدید!',

    // Login
    'login'              => 'ورود',
    'needAccount'        => 'نیاز به یک حساب کاربری دارید؟',
    'rememberMe'         => 'مرا به خاطر بسپار؟',
    'forgotPassword'     => 'رمز عبور را فراموش کرده اید؟',
    'useMagicLink'       => 'از لینک ورود استفاده کنید',
    'magicLinkSubject'   => 'لینک ورود شما',
    'magicTokenNotFound' => 'تایید لینک ممکن نیست.',
    'magicLinkExpired'   => 'متاسفانه, لینک منقضی شده است.',
    'checkYourEmail'     => 'ایمیلتان را بررسی کنید!',
    'magicLinkDetails'   => 'ما فقط یک لینک ورود به ایمیلتان ارسال کردیم. این لینک فقط برای {0} دقیقه معتبر خواهد بود.',
    'successLogout'      => 'با موفقیت خارج شدید.',

    // Passwords
    'errorPasswordLength'       => 'طول رمز های عبور باید حداقل {0, number} کاراکتر باشد.',
    'suggestPasswordLength'     => 'عبارات عبور -- تا 255 کاراکتر طولانی -- رمزهای عبور امن تری ایجاد کنید که به آسانی به یاد داشته باشید.',
    'errorPasswordCommon'       => 'رمز عبور نباید یک رمز عبور رایج باشد.',
    'suggestPasswordCommon'     => 'رمز عبور با بیش از 65 هزار رمز عبور رایج یا رمزهای عبوری که از طریق هک منتشر شده اند، بررسی شد',
    'errorPasswordPersonal'     => 'رمزهای عبور نباید شامل مشخصات شخصی رمزنگاری شده باشند.',
    'suggestPasswordPersonal'   => 'آدرس ایمیل یا نام کاربری متغیر شما نباید برای رمزهای عبور استفاده شود.',
    'errorPasswordTooSimilar'   => 'رمز عبور بسیار شبیه نام کاربری است',
    'suggestPasswordTooSimilar' => 'از بخش های نام کاربری خود در رمز عبور استفاده نکنید.',
    'errorPasswordPwned'        => 'رمز عبور {0} با توجه به نقض داده ها و دیده شدن {1, number} بارها داخل رمز های عبور {2} به پسورد های ناامن تبدیل شده و در معرض قرار گرفته است.',
    'suggestPasswordPwned'      => '{0} هرگز نباید به عنوان رمز عبور استفاده شود. اگر در هر جایی از آن استفاده می کنید سریعا آن را تغییر دهید.',
    'errorPasswordEmpty'        => 'رمز عبور الزامی است.',
    'passwordChangeSuccess'     => 'رمز عبور با موفقیت تغییر کرد',
    'userDoesNotExist'          => 'رمز عبور تغییر نکرد. کاربر وجود ندارد.',
    'resetTokenExpired'         => 'متاسفانه، توکن بازنشانی شما منقضی شده است.',

    // 2FA
    'email2FATitle'       => 'احراز هویت دو عاملی',
    'confirmEmailAddress' => 'آدرس ایمیل خود را تایید کنید.',
    'emailEnterCode'      => 'تایید ایمیل',
    'emailConfirmCode'    => 'کد شش رقمی را که به آدرس ایمیلتان فرستادیم وارد کنید.',
    'email2FASubject'     => 'کد احراز هویت شما',
    'email2FAMailBody'    => 'کد احراز هویت شما:',
    'invalid2FAToken'     => 'کد صحیح نمی باشد.',
    'need2FA'             => 'شما باید تایید دو مرحله ای را تکمیل کنید.',
    'needVerification'    => 'برای تکمیل فعالسازی حساب کاربری، ایمیل خود را بررسی کنید.',

    // Activate
    'emailActivateTitle'    => 'فعالسازی ایمیل',
    'emailActivateBody'     => 'ما یک ایمیل شامل کدی برای فعالسازی آدرس ایمیلتان به شما ارسال کردیم. کد را کپی و در زیر قرار دهید.',
    'emailActivateSubject'  => 'کد فعالسازی شما',
    'emailActivateMailBody' => 'لطفا برای فعالسازی حساب کاربری و استفاده از سایت از کد زیر استفاده کنید.',
    'invalidActivateToken'  => 'کد صحیح نمی باشد.',

    // Groups
    'unknownGroup' => '{0} گروهی معتبر نیست.',
    'missingTitle' => 'گروه ها باید عنوانی داشته باشند.',

    // Permissions
    'unknownPermission' => '{0} سطح دسترسی مجازی نیست.',
];
