<?php

declare(strict_types=1);

return [
    // Exceptions
    'unknownAuthenticator'  => '{0} is geen geldige authenticator.',
    'unknownUserProvider'   => 'Kan de User Provider niet bepalen.',
    'invalidUser'           => 'Kan de opgegeven gebruiker niet vinden.',
    'bannedUser'            => 'Ik kan je niet aanmelden omdat je momenteel verbannen bent.',
    'logOutBannedUser'      => 'Je bent uitgelogd omdat je verbannen bent.',
    'badAttempt'            => 'Kan u niet aanmelden. Controleer uw gegevens.',
    'noPassword'            => 'Kan een gebruiker zonder wachtwoord niet valideren.',
    'invalidPassword'       => 'Kan u niet aanmelden. Controleer uw wachtwoord.',
    'noToken'               => 'Elk verzoek moet een draagtoken hebben in de {0} header.',
    'badToken'              => 'Het toegangstoken is ongeldig.',
    'oldToken'              => 'Het toegangstoken is verlopen.',
    'noUserEntity'          => 'Gebruikers moet worden opgegeven voor wachtwoordvalidatie.',
    'invalidEmail'          => 'Kan niet controleren of het e-mailadres overeenkomt met het geregistreerde e-mailadres.',
    'unableSendEmailToUser' => 'Sorry, er was een probleem met het verzenden van de e-mail. We konden geen e-mail sturen naar "{0}".',
    'throttled'             => 'Teveel aanvragen vanaf dit IP-adres. U kunt het over {0} seconden opnieuw proberen.',
    'notEnoughPrivilege'    => 'U heeft niet de nodige toestemming om de gewenste handeling uit te voeren.',

    'email'           => 'E-mailadres',
    'username'        => 'Gebruikersnaam',
    'password'        => 'Wachtwoord',
    'passwordConfirm' => 'Herhaal wachtwoord',
    'haveAccount'     => 'Heb je al een account?',

    // Buttons
    'confirm' => 'Bevestigen',
    'send'    => 'Verstuur',

    // Registration
    'register'         => 'Registreren',
    'registerDisabled' => 'Registratie is momenteel niet toegestaan.',
    'registerSuccess'  => 'Welkom aan boord!',

    // Login
    'login'              => 'Inloggen',
    'needAccount'        => 'Heb je een account nodig?',
    'rememberMe'         => 'Herinner je mij nog?',
    'forgotPassword'     => 'Wachtwoord vergeten?',
    'useMagicLink'       => 'Gebruik een aanmeldingslink',
    'magicLinkSubject'   => 'Uw aanmeldlink',
    'magicTokenNotFound' => 'Kan de link niet verifiëren.',
    'magicLinkExpired'   => 'Sorry, uw link is verlopen.',
    'checkYourEmail'     => 'Controleer uw e-mail!',
    'magicLinkDetails'   => 'We hebben je zojuist een e-mail gestuurd met daarin uw aanmeldlink. Deze is slechts geldig voor {0} minuten.',
    'successLogout'      => 'U bent succesvol uitgelogd.',

    // Passwords
    'errorPasswordLength'       => 'Wachtwoorden moeten minstens {0, getal} tekens lang zijn.',
    'suggestPasswordLength'     => 'Wachtwoordzinnen - tot 255 tekens lang - maken veiligere wachtwoorden die gemakkelijk te onthouden zijn.',
    'errorPasswordCommon'       => 'Het wachtwoord mag geen algemeen wachtwoord zijn.',
    'suggestPasswordCommon'     => 'Het wachtwoord is vergeleken met meer dan 65.000 veelgebruikte wachtwoorden of wachtwoorden die via hacks zijn gelekt.',
    'errorPasswordPersonal'     => 'Wachtwoorden mogen geen gehashte persoonlijke informatie bevatten.',
    'suggestPasswordPersonal'   => 'Variaties op uw e-mailadres of gebruikersnaam mogen niet worden gebruikt voor wachtwoorden.',
    'errorPasswordTooSimilar'   => 'Wachtwoord lijkt te veel op de gebruikersnaam.',
    'suggestPasswordTooSimilar' => 'Gebruik geen delen van uw gebruikersnaam in uw wachtwoord.',
    'errorPasswordPwned'        => 'Het wachtwoord {0} is openbaar gemaakt vanwege een datalek en is {1, aantal} keer gezien in {2} gecompromitteerde wachtwoorden.rds.',
    'suggestPasswordPwned'      => '{0} mag nooit als wachtwoord worden gebruikt. Als u het ergens gebruikt, verander het dan onmiddellijk.',
    'errorPasswordEmpty'        => 'Een wachtwoord is vereist.',
    'errorPasswordTooLongBytes' => 'Wachtwoord mag niet langer zijn dan {param} characters.',
    'passwordChangeSuccess'     => 'Uw wachtwoord succesvol veranderd',
    'userDoesNotExist'          => 'Wachtwoord is niet gewijzigd. Gebruiker bestaat niet',
    'resetTokenExpired'         => 'Sorry. Uw resettoken is verlopen.',

    // Email Globals
    'emailInfo'      => 'Informatie over de persoon:',
    'emailIpAddress' => 'IP-adres:',
    'emailDevice'    => 'Apparaat',
    'emailDate'      => 'Datum:',

    // 2FA
    'email2FATitle'       => 'Verificatie met twee factoren',
    'confirmEmailAddress' => 'Bevestig uw e-mailadres.',
    'emailEnterCode'      => 'Bevestig uw e-mail',
    'emailConfirmCode'    => 'Voer de 6-cijferige code in die we zojuist naar uw e-mailadres hebben gestuurd.',
    'email2FASubject'     => 'Uw verificatiecode',
    'email2FAMailBody'    => 'Uw verificatiecode is:',
    'invalid2FAToken'     => 'De code was onjuist.',
    'need2FA'             => 'U moet een twee-factor verificatie voltooien.',
    'needVerification'    => 'Controleer uw e-mail om de activering van uw account te voltooien.',

    // Activate
    'emailActivateTitle'    => 'E-mail activering',
    'emailActivateBody'     => 'We hebben u zojuist een e-mail gestuurd met een code om uw e-mailadres te bevestigen. Kopieer die code en plak hem hieronder.',
    'emailActivateSubject'  => 'Uw activeringscode',
    'emailActivateMailBody' => 'Gebruik onderstaande code om uw account te activeren en de website te gebruiken.',
    'invalidActivateToken'  => 'De code was onjuist.',
    'needActivate'          => 'U moet uw registratie voltooien door de naar uw e-mailadres verzonden code te bevestigen.',
    'activationBlocked'     => 'U moet uw account activeren voordat u inlogt.',

    // Groups
    'unknownGroup' => '{0} is geen geldige groep.',
    'missingTitle' => 'Groepen moeten een titel hebben.',

    // Permissions
    'unknownPermission' => '{0} is geen geldige permissie.',
];
