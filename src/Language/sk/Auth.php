<?php

namespace CodeIgniter\Shield\Language\sk;

return [
    // Exceptions
    'unknownAuthenticator'  => '{0} nie je platný autentifikátor.',
    'unknownUserProvider'   => 'Nie je možné určiť poskytovateľa používateľa, ktorý sa má použiť.',
    'invalidUser'           => 'Nie je možné nájsť zadaného používateľa.',
    'badAttempt'            => 'Prihlásenie zlyhalo. Skontrolujte svoje prihlasovacie údaje.',
    'noPassword'            => 'Nie je možné overiť používateľa bez hesla.',
    'invalidPassword'       => 'Prihlásenie zlyhalo. Skontrolujte svoje heslo.',
    'noToken'               => 'Každá požiadavka musí mať v hlavičke Autorizácia nosný token',
    'badToken'              => 'Prístupový token je neplatný.',
    'oldToken'              => 'Platnosť prístupového tokenu vypršala.',
    'noUserEntity'          => 'Na overenie hesla je potrebné zadať entitu používateľa.',
    'invalidEmail'          => 'Nie je možné overiť, či sa e-mailová adresa zhoduje so zaznamenaným e-mailom.',
    'unableSendEmailToUser' => 'Ľutujeme, pri odosielaní e-mailu sa vyskytol problém. Nepodarilo sa nám odoslať e-mail na adresu „{0}".',
    'throttled'             => 'Z tejto adresy IP bolo odoslaných príliš veľa žiadostí. Môžete to skúsiť znova o {0} sekúnd.',

    'email'           => 'Emailová adresa',
    'username'        => 'Používateľské meno',
    'password'        => 'Heslo',
    'passwordConfirm' => 'Heslo (znova)',
    'haveAccount'     => 'Máte už účet?',

    // Buttons
    'confirm' => 'Potvrdiť',
    'send'    => 'Odoslať',

    // Registration
    'register'         => 'Registrácia',
    'registerDisabled' => 'Registrácia momentálne nie je povolená.',
    'registerSuccess'  => 'Vitajte na palube!',

    // Login
    'login'              => 'Prihlásenie',
    'needAccount'        => 'Potrebujete účet?',
    'rememberMe'         => 'Zapamätať si ma?',
    'forgotPassword'     => 'Zabudli ste heslo?',
    'useMagicLink'       => 'Použiť odkaz na prihlásenie',
    'magicLinkSubject'   => 'Váš odkaz na prihlásenie',
    'magicTokenNotFound' => 'Odkaz sa nepodarilo overiť.',
    'magicLinkExpired'   => 'Ľutujeme, platnosť odkazu vypršala.',
    'checkYourEmail'     => 'Skontrolujte e-mail',
    'magicLinkDetails'   => 'Práve sme vám poslali e-mail s odkazom na prihlásenie. Platí iba {0} minút.',
    'successLogout'      => 'Úspešne ste sa odhlásili.',

    // Passwords
    'errorPasswordLength'       => 'Heslá musia mať aspoň {0, number} znakov.',
    'suggestPasswordLength'     => 'Heslové frázy – až 255 znakov – vytvárajú bezpečnejšie heslá, ktoré sa dajú ľahko zapamätať.',
    'errorPasswordCommon'       => 'Heslo nesmie byť bežné heslo.',
    'suggestPasswordCommon'     => 'Heslo bolo skontrolované oproti viac ako 65 tisícom bežne používaných hesiel alebo hesiel, ktoré unikli prostredníctvom hackerov.',
    'errorPasswordPersonal'     => 'Heslá nemôžu obsahovať opätovne hašované osobné údaje.',
    'suggestPasswordPersonal'   => 'Variácie vašej e-mailovej adresy alebo používateľského mena by sa nemali používať ako heslá.',
    'errorPasswordTooSimilar'   => 'Heslo je príliš podobné používateľskému menu.',
    'suggestPasswordTooSimilar' => 'Vo svojom hesle nepoužívajte časti svojho používateľského mena.',
    'errorPasswordPwned'        => 'Heslo {0} bolo odhalené z dôvodu porušenia ochrany údajov a bolo videné {1, number}-krát z {2} prelomených hesiel.',
    'suggestPasswordPwned'      => '{0} by sa nikdy nemalo používať ako heslo. Ak ho niekde používate, okamžite ho zmeňte.',
    'errorPasswordEmpty'        => 'Vyžaduje sa heslo.',
    'passwordChangeSuccess'     => 'Heslo bolo úspešne zmenené',
    'userDoesNotExist'          => 'Heslo nebolo zmenené. Používateľ neexistuje',
    'resetTokenExpired'         => 'Prepáčte. Platnosť vášho resetovacieho tokenu vypršala.',

    // 2FA
    'email2FATitle'       => 'Dvojfaktorová autentifikácia',
    'confirmEmailAddress' => 'Potvrďte svoju e-mailovú adresu.',
    'emailEnterCode'      => 'Potvrďte svoj e-mail',
    'emailConfirmCode'    => 'Zadajte 6-miestny kód, ktorý sme vám práve poslali na vašu e-mailovú adresu.',
    'email2FASubject'     => 'Váš overovací kód',
    'email2FAMailBody'    => 'Váš overovací kód je:',
    'invalid2FAToken'     => 'Kód bol nesprávny.',
    'need2FA'             => 'Musíte vykonať dvojfaktorové overenie.',
    'needVerification'    => 'Ak chcete dokončiť aktiváciu účtu, skontrolujte svoj e-mail.',

    // Activate
    'emailActivateTitle'    => 'E-mailová aktivácia',
    'emailActivateBody'     => 'Práve sme vám poslali e-mail s kódom na potvrdenie vašej e-mailovej adresy. Skopírujte tento kód a vložte ho nižšie.',
    'emailActivateSubject'  => 'Váš aktivačný kód',
    'emailActivateMailBody' => 'Pomocou nižšie uvedeného kódu aktivujte svoj účet a môžete začať používať stránku.',
    'invalidActivateToken'  => 'Kód bol nesprávny',

    // Groups
    'unknownGroup' => '{0} nie je platná skupina.',
    'missingTitle' => 'Skupiny musia mať názov.',

    // Permissions
    'unknownPermission' => '{0} nie je platným povolením.',
];
