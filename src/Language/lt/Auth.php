<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

return [
    // Exceptions
    'unknownAuthenticator'  => '{0} nėra galiojantis autentifikatorius.',
    'unknownUserProvider'   => 'Nepavyksta nustatyti, kokį naudotojų šaltinį naudoti.',
    'invalidUser'           => 'Nepavyksta rasti nurodyto naudotojo.',
    'bannedUser'            => 'Negalite prisijungti, nes esate užblokuotas.',
    'logOutBannedUser'      => 'Buvote atjungti, nes esate užblokuotas.',
    'badAttempt'            => 'Nepavyko jūsų prijungti. Patikrinkite prisijungimo duomenis.',
    'noPassword'            => 'Negalima patvirtinti naudotojo be slaptažodžio.',
    'invalidPassword'       => 'Nepavyko jūsų prijungti. Patikrinkite slaptažodį.',
    'noToken'               => 'Kiekviena užklausa turi turėti prieigos raktą (bearer token) antraštėje {0}.',
    'badToken'              => 'Prieigos raktas neteisingas.',
    'oldToken'              => 'Prieigos rakto galiojimas pasibaigęs.',
    'noUserEntity'          => 'Slaptažodžio patikrinimui turi būti pateiktas naudotojo objektas.',
    'invalidEmail'          => 'Nepavyksta patvirtinti, kad el. pašto adresas „{0}“ atitinka įrašytą adresą.',
    'unableSendEmailToUser' => 'Atsiprašome, įvyko klaida siunčiant el. laišką. Nepavyko išsiųsti laiško adresu „{0}“.',
    'throttled'             => 'Per daug užklausų iš šio IP adreso. Galite pamėginti iš naujo po {0} sekundžių.',
    'notEnoughPrivilege'    => 'Neturite pakankamų teisių šiai operacijai atlikti.',
    // JWT Exceptions
    'invalidJWT'     => 'Raktas neteisingai suformuotas.',
    'expiredJWT'     => 'Rakto galiojimas pasibaigęs.',
    'beforeValidJWT' => 'Raktas dar negalioja.',

    'email'           => 'El. pašto adresas',
    'username'        => 'Naudotojo vardas',
    'password'        => 'Slaptažodis',
    'passwordConfirm' => 'Slaptažodis (pakartoti)',
    'haveAccount'     => 'Jau turite paskyrą?',
    'token'           => 'Kodas',

    // Buttons
    'confirm' => 'Patvirtinti',
    'send'    => 'Siųsti',

    // Registration
    'register'         => 'Registruotis',
    'registerDisabled' => 'Šiuo metu registracija neleidžiama.',
    'registerSuccess'  => 'Sveiki atvykę!',

    // Login
    'login'              => 'Prisijungimas',
    'needAccount'        => 'Reikia paskyros?',
    'rememberMe'         => 'Atsiminti mane?',
    'forgotPassword'     => 'Pamiršote slaptažodį?',
    'useMagicLink'       => 'Naudoti prisijungimo nuorodą',
    'magicLinkSubject'   => 'Jūsų prisijungimo nuoroda',
    'magicTokenNotFound' => 'Nepavyksta patvirtinti nuorodos.',
    'magicLinkExpired'   => 'Deja, nuorodos galiojimas baigėsi.',
    'checkYourEmail'     => 'Patikrinkite savo el. paštą!',
    'magicLinkDetails'   => 'Ką tik išsiuntėme jums el. laišką su prisijungimo nuoroda. Ji galios tik {0} min.',
    'magicLinkDisabled'  => 'Prisijungimo nuorodų naudojimas šiuo metu neleidžiamas.',
    'successLogout'      => 'Sėkmingai atsijungėte.',
    'backToLogin'        => 'Grįžti į prisijungimą',

    // Passwords
    'errorPasswordLength'       => 'Slaptažodis turi būti bent {0, number} ženklų ilgio.',
    'suggestPasswordLength'     => 'Slaptažodžių frazės (iki 255 ženklų ilgio) yra saugesni ir lengviau įsimenami slaptažodžiai.',
    'errorPasswordCommon'       => 'Slaptažodis neturi būti vienas iš dažniausiai naudojamų slaptažodžių.',
    'suggestPasswordCommon'     => 'Slaptažodis buvo patikrintas lyginant jį su daugiau nei 65 tūkst. įprastai naudojamų slaptažodžių ir slaptažodžių, kurie buvo paviešinti nulaužus sistemas.',
    'errorPasswordPersonal'     => 'Slaptažodyje neturi būti asmeninės informacijos.',
    'suggestPasswordPersonal'   => 'Slaptažodyje neturi būti naudojami menkai pakeisti el. pašto adreso arba naudotojo vardo variantai.',
    'errorPasswordTooSimilar'   => 'Slaptažodis pernelyg panašus į naudotojo vardą.',
    'suggestPasswordTooSimilar' => 'Nenaudokite naudotojo vardo dalių slaptažodyje.',
    'errorPasswordPwned'        => 'Slaptažodis {0} buvo paviešintas dėl duomenų nutekėjimo ir aptiktas {1, number} kartus {2} nutekėjusių duomenų rinkiniuose.',
    'suggestPasswordPwned'      => '{0} neturi būti naudojamas kaip slaptažodis. Jei jį naudojate kur nors, nedelsdami jį pakeiskite.',
    'errorPasswordEmpty'        => 'Reikia slaptažodžio.',
    'errorPasswordTooLongBytes' => 'Slaptažodis neturi būti ilgesnis nei {param} baitų(-ai).',
    'passwordChangeSuccess'     => 'Slaptažodis sėkmingai pakeistas',
    'userDoesNotExist'          => 'Slaptažodis nepakeistas. Tokio naudotojo nėra.',
    'resetTokenExpired'         => 'Deja, jūsų slaptažodžio atkūrimo rakto galiojimas baigėsi.',

    // Email Globals
    'emailInfo'      => 'Šiek tiek informacijos apie asmenį:',
    'emailIpAddress' => 'IP adresas:',
    'emailDevice'    => 'Įrenginys:',
    'emailDate'      => 'Data:',

    // 2FA
    'email2FATitle'       => 'Dviejų veiksnių autentifikacija',
    'confirmEmailAddress' => 'Patvirtinkite savo el. pašto adresą.',
    'emailEnterCode'      => 'Patvirtinkite savo el. paštą',
    'emailConfirmCode'    => 'Įrašykite 6 ženklų kodą, kurį ką tik išsiuntėme jums el. paštu.',
    'email2FASubject'     => 'Jūsų autentifikacijos kodas',
    'email2FAMailBody'    => 'Jūsų autentifikacijos kodas yra:',
    'invalid2FAToken'     => 'Kodas buvo neteisingas.',
    'need2FA'             => 'Turite užbaigti dviejų veiksnių patvirtinimą.',
    'needVerification'    => 'Norėdami užbaigti paskyros aktyvavimą, patikrinkite savo el. pašto dėžutę.',

    // Activate
    'emailActivateTitle'    => 'Aktyvavimas el. paštu',
    'emailActivateBody'     => 'Ką tik išsiuntėme jums el. laišką su kodu el. pašto adresui patvirtinti. Nukopijuokite šį kodą ir įterpkite jį žemiau.',
    'emailActivateSubject'  => 'Jūsų aktyvavimo kodas',
    'emailActivateMailBody' => 'Prašome naudoti žemiau esantį kodą paskyros aktyvavimui. Tuomet galėsite pradėti naudotis mūsų svetaine.',
    'invalidActivateToken'  => 'Kodas buvo neteisingas.',
    'needActivate'          => 'Turite baigti registraciją panaudodami kodą, išsiųstą jums el. pašto adresu.',
    'activationBlocked'     => 'Prieš prisijungdami turite aktyvuoti paskyrą.',

    // Groups
    'unknownGroup' => '{0} nėra galiojanti grupė.',
    'missingTitle' => 'Grupė turi turėti pavadinimą.',

    // Permissions
    'unknownPermission' => '{0} nėra galiojantis leidimas.',
];
