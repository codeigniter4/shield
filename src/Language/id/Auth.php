<?php

declare(strict_types=1);

return [
    // Exceptions
    'unknownAuthenticator'  => '{0} bukan otentikator yang sah.',
    'unknownUserProvider'   => 'Tidak dapat menentukan Penyedia Pengguna yang akan digunakan.',
    'invalidUser'           => 'Tidak dapat menemukan pengguna yang spesifik.',
    'bannedUser'            => '(To be translated) Can not log you in as you are currently banned.',
    'logOutBannedUser'      => '(To be translated) You have been logged out because you have been banned.',
    'badAttempt'            => 'Anda tidak dapat masuk. Harap periksa kredensial Anda.',
    'noPassword'            => 'Tidak dapat memvalidasi pengguna tanpa kata sandi.',
    'invalidPassword'       => 'Anda tidak dapat masuk. Harap periksa kata sandi Anda.',
    'noToken'               => 'Setiap permintaan harus memiliki token pembawa di header {0}.',
    'badToken'              => 'Akses token tidak sah.',
    'oldToken'              => 'Akses token sudah tidak berlaku.',
    'noUserEntity'          => 'Entitas Pengguna harus disediakan untuk validasi kata sandi.',
    'invalidEmail'          => 'Tidak dapat memverifikasi alamat email yang cocok dengan email yang tercatat.',
    'unableSendEmailToUser' => 'Maaf, ada masalah saat mengirim email. Kami tidak dapat mengirim email ke "{0}".',
    'throttled'             => 'Terlalu banyak permintaan yang dibuat dari alamat IP ini. Anda dapat mencoba lagi dalam {0} detik.',
    'notEnoughPrivilege'    => 'Anda tidak memiliki izin yang diperlukan untuk melakukan operasi yang diinginkan.',

    'email'           => 'Alamat Email',
    'username'        => 'Nama Pengguna',
    'password'        => 'Kata Sandi',
    'passwordConfirm' => 'Kata Sandi (lagi)',
    'haveAccount'     => 'Sudah punya akun?',

    // Buttons
    'confirm' => 'Konfirmasi',
    'send'    => 'Kirim',

    // Registration
    'register'         => 'Registrasi',
    'registerDisabled' => 'Registrasi saat ini tidak diperbolehkan.',
    'registerSuccess'  => 'Selamat bergabung!',

    // Login
    'login'              => 'Masuk',
    'needAccount'        => 'Butuh Akun?',
    'rememberMe'         => 'Ingat saya?',
    'forgotPassword'     => 'Lupa kata sandi?',
    'useMagicLink'       => 'Gunakan tautan masuk',
    'magicLinkSubject'   => 'Tautan masuk Anda',
    'magicTokenNotFound' => 'Tidak dapat memverifikasi tautan.',
    'magicLinkExpired'   => 'Maaf, tautan sudah tidak berlaku.',
    'checkYourEmail'     => 'Periksa email Anda!',
    'magicLinkDetails'   => 'Kami baru saja mengirimi Anda email dengan tautan Masuk di dalamnya. Ini hanya berlaku selama {0} menit.',
    'successLogout'      => 'Anda telah berhasil keluar.',

    // Passwords
    'errorPasswordLength'       => 'Kata sandi harus setidaknya terdiri dari {0, number} karakter.',
    'suggestPasswordLength'     => 'Kata sandi dapat dibuat mencapai 255 karakter, Disarankan buat kata sandi yang aman dan mudah diingat.',
    'errorPasswordCommon'       => 'Kata sandi tidak boleh menggunakan sandi yang sudah umum.',
    'suggestPasswordCommon'     => 'Kata sandi yang digunakan lebih dari 65 ribu kali pada umumnya dan mudah diretas orang lain.',
    'errorPasswordPersonal'     => 'Kata sandi tidak boleh berisi informasi pribadi.',
    'suggestPasswordPersonal'   => 'Variasi pada alamat email atau nama pengguna Anda tidak boleh digunakan untuk kata sandi.',
    'errorPasswordTooSimilar'   => 'Kata sandi mirip dengan nama pengguna.',
    'suggestPasswordTooSimilar' => 'Jangan gunakan bagian dari nama pengguna Anda dalam kata sandi Anda.',
    'errorPasswordPwned'        => 'Kata sandi {0} telah bocor karena pelanggaran data dan telah dilihat {1, number} kali dalam {2} sandi yang disusupi.',
    'suggestPasswordPwned'      => '{0} tidak boleh digunakan sebagai kata sandi. Jika Anda menggunakannya di mana saja, segera ubah.',
    'errorPasswordEmpty'        => 'Kata sandi wajib diisi.',
    'errorPasswordTooLongBytes' => '(To be translated) Password cannot exceed {param} bytes in length.',
    'passwordChangeSuccess'     => 'Kata sandi berhasil diubah',
    'userDoesNotExist'          => 'Kata sandi tidak diubah. User tidak ditemukan',
    'resetTokenExpired'         => 'Maaf, token setel ulang Anda sudah habis waktu.',

    // Email Globals
    'emailInfo'      => 'Beberapa informasi tentang seseorang:',
    'emailIpAddress' => 'Alamat IP:',
    'emailDevice'    => 'Perangkat:',
    'emailDate'      => 'Tanggal:',

    // 2FA
    'email2FATitle'       => 'Otentikasi Dua Faktor',
    'confirmEmailAddress' => 'Alamat email konfirmasi Anda.',
    'emailEnterCode'      => 'Konfirmasi email Anda',
    'emailConfirmCode'    => 'Masukkan kode 6 digit yang baru saja kami kirimkan ke alamat email Anda.',
    'email2FASubject'     => 'Kode otentikasi Anda',
    'email2FAMailBody'    => 'Kode otentikasi Anda adalah:',
    'invalid2FAToken'     => 'Kode tidak sesuai.',
    'need2FA'             => 'Anda harus menyelesaikan verifikasi otentikasi dua faktor.',
    'needVerification'    => 'Periksa email Anda untuk menyelesaikan verifikasi akun.',

    // Activate
    'emailActivateTitle'    => 'Aktivasi Email',
    'emailActivateBody'     => 'Kami baru saja mengirim email kepada Anda dengan kode untuk mengonfirmasi alamat email Anda. Salin kode itu dan tempel di bawah ini.',
    'emailActivateSubject'  => 'Kode aktivasi Anda',
    'emailActivateMailBody' => 'Silahkan gunakan kode dibawah ini untuk mengaktivasi akun Anda.',
    'invalidActivateToken'  => 'Kode tidak sesuai.',
    'needActivate'          => 'Anda harus menyelesaikan registrasi Anda dengan mengonfirmasi kode yang dikirim ke alamat email Anda.',
    'activationBlocked'     => '(to be translated) You must activate your account before logging in.',

    // Groups
    'unknownGroup' => '{0} bukan grup yang sah.',
    'missingTitle' => 'Grup-grup diharuskan mempunyai judul.',

    // Permissions
    'unknownPermission' => '{0} bukan izin yang sah.',
];
