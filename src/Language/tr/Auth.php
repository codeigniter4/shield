<?php

declare(strict_types=1);

return [
    // Exceptions
    'unknownAuthenticator'  => '{0} geçerli bir kimlik doğrulayıcı değil.',
    'unknownUserProvider'   => 'Kullanılacak Kullanıcı Sağlayıcı belirlenemiyor.',
    'invalidUser'           => 'Belirtilen kullanıcı bulunamadı.',
    'bannedUser'            => '(To be translated) Can not log you in as you are currently banned.',
    'logOutBannedUser'      => '(To be translated) You have been logged out because you have been banned.',
    'badAttempt'            => 'Oturumunuz açılamıyor. Lütfen kimlik bilgilerinizi kontrol edin.',
    'noPassword'            => 'Parola olmadan bir kullanıcı doğrulanamaz.',
    'invalidPassword'       => 'Oturumunuz açılamıyor. Lütfen şifrenizi kontrol edin.',
    'noToken'               => 'Her istediğin başlığında {0} bearer anahtar belirteci olmalıdır.',
    'badToken'              => 'Erişim anahtarı geçersiz.',
    'oldToken'              => 'Erişim anahtarının süresi doldu.',
    'noUserEntity'          => 'Parola doğrulaması için Kullanıcı Varlığı sağlanmalıdır.',
    'invalidEmail'          => 'E-posta adresinin kayıtlı e-posta ile eşleştiği doğrulanamıyor.',
    'unableSendEmailToUser' => 'Üzgünüz, e-posta gönderilirken bir sorun oluştu. "{0}" adresine e-posta gönderemedik.',
    'throttled'             => 'Bu IP adresinden çok fazla istek yapıldı. {0} saniye sonra tekrar deneyebilirsiniz.',
    'notEnoughPrivilege'    => 'İstediğiniz işlemi gerçekleştirmek için gerekli izne sahip değilsiniz.',

    'email'           => 'E-posta Adresi',
    'username'        => 'Kullanıcı Adı',
    'password'        => 'Şifre',
    'passwordConfirm' => 'Şifre (tekrar)',
    'haveAccount'     => 'Zaten hesabınız var mı?',

    // Buttons
    'confirm' => 'Onayla',
    'send'    => 'Gönder',

    // Registration
    'register'         => 'Kayıt Ol',
    'registerDisabled' => 'Kayıt işlemine şu anda izin verilmiyor.',
    'registerSuccess'  => 'Gemiye Hoşgeldiniz!',

    // Login
    'login'              => 'Giriş',
    'needAccount'        => 'Bir hesaba mı ihtiyacınız var?',
    'rememberMe'         => 'Beni hatırla?',
    'forgotPassword'     => 'Şifrenizi mı unuttunuz?',
    'useMagicLink'       => 'Giriş Bağlantısı Kullanın',
    'magicLinkSubject'   => 'Giriş Bağlantınız',
    'magicTokenNotFound' => 'Bağlantı doğrulanamıyor.',
    'magicLinkExpired'   => 'Üzgünüm, bağlantının süresi doldu.',
    'checkYourEmail'     => 'E-postanı kontrol et!',
    'magicLinkDetails'   => 'Az önce size içinde bir Giriş bağlantısı olan bir e-posta gönderdik. Bağlantı {0} dakika için geçerlidir.',
    'successLogout'      => 'Başarıyla çıkış yaptınız.',

    // Passwords
    'errorPasswordLength'       => 'Şifre en az {0, number} karakter uzunluğunda olmalıdır.',
    'suggestPasswordLength'     => 'En fazla 255 karakter uzunluğundaki geçiş ifadeleri, hatırlaması kolay, daha güvenli şifreler oluşturur.',
    'errorPasswordCommon'       => 'Şifre genel bir şifre olmamalıdır.',
    'suggestPasswordCommon'     => 'Şifre, yaygın olarak kullanılan 65 binden fazla şifre veya bilgisayar korsanlığı yoluyla sızdırılmış şifreler açısından kontrol edildi.',
    'errorPasswordPersonal'     => 'Parolalar, yeniden oluşturulmuş kişisel bilgileri içeremez.',
    'suggestPasswordPersonal'   => 'E-posta adresiniz veya kullanıcı adınızdaki varyasyonlar, şifreler için kullanılmamalıdır.',
    'errorPasswordTooSimilar'   => 'Şifre, kullanıcı adınıza çok benziyor.',
    'suggestPasswordTooSimilar' => 'Kullanıcı adınızın bazı kısımlarını şifrenizde kullanmayın.',
    'errorPasswordPwned'        => '{0} şifresi, bir veri ihlali nedeniyle açığa çıktı ve güvenliği ihlal edilmiş şifrelerin {2} tanesinde {1, sayı} kez görüldü.',
    'suggestPasswordPwned'      => '{0} asla şifre olarak kullanılmamalıdır. Herhangi bir yerde kullanıyorsanız hemen değiştirin.',
    'errorPasswordEmpty'        => 'Şifre gerekli.',
    'errorPasswordTooLongBytes' => '(To be translated) Password cannot exceed {param} bytes in length.',
    'passwordChangeSuccess'     => 'Şifre başarıyla değiştirildi.',
    'userDoesNotExist'          => 'Şifre değiştirilmedi. Kullanıcı yok.',
    'resetTokenExpired'         => 'Üzgünüz. Sıfırlama anahtarınızın süresi doldu.',

    // Email Globals
    'emailInfo'      => 'Kişi hakkında bazı bilgiler:',
    'emailIpAddress' => 'IP Adresi:',
    'emailDevice'    => 'Cihaz:',
    'emailDate'      => 'Tarih:',

    // 2FA
    'email2FATitle'       => 'İki Faktörlü Kimlik Doğrulama',
    'confirmEmailAddress' => 'E-Posta adresini onayla.',
    'emailEnterCode'      => 'E-posta adresinizi onaylayın.',
    'emailConfirmCode'    => 'Az önce e-posta adresinize gönderdiğimiz 6 haneli kodu girin.',
    'email2FASubject'     => 'Kimlik doğrulama kodunuz',
    'email2FAMailBody'    => 'Kimlik doğrulama kodunuz:',
    'invalid2FAToken'     => 'Kod yanlış.',
    'need2FA'             => 'İki faktörlü doğrulamayı tamamlamanız gerekir.',
    'needVerification'    => 'Hesap aktivasyonunu tamamlamak için e-postanızı kontrol edin.',

    // Activate
    'emailActivateTitle'    => 'E-Posta Aktivasyonu',
    'emailActivateBody'     => 'Az önce size e-posta adresinizi doğrulamak için bir kod içeren bir e-posta gönderdik. Bu kodu kopyalayın ve aşağıya yapıştırın.',
    'emailActivateSubject'  => 'Aktivasyon kodunuz',
    'emailActivateMailBody' => 'Hesabınızı etkinleştirmek ve siteyi kullanmaya başlamak için lütfen aşağıdaki kodu kullanın.',
    'invalidActivateToken'  => 'Kod yanlıştı.',
    'needActivate'          => 'E-posta adresinize gönderilen kodu onaylayarak kaydınızı tamamlamanız gerekmektedir.',
    'activationBlocked'     => '(to be translated) You must activate your account before logging in.',

    // Groups
    'unknownGroup' => '{0} geçerli bir grup değil.',
    'missingTitle' => 'Grupların bir başlığı olmalıdır.',

    // Permissions
    'unknownPermission' => '{0} geçerli bir izin değil.',
];
