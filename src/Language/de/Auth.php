<?php

namespace CodeIgniter\Shield\Language\de;

return [
    // Exceptions
    'unknownAuthenticator'  => '{0} ist kein gültiger Authentifikator.',
    'unknownUserProvider'   => 'Der zu verwendende User Provider konnte nicht ermittelt werden.',
    'invalidUser'           => 'Der angegebene Benutzer kann nicht gefunden werden.',
    'badAttempt'            => 'Sie konnten nicht angemeldet werden. Bitte überprüfen Sie Ihre Anmeldedaten.',
    'noPassword'            => 'Kann einen Benutzer ohne Passwort nicht validieren.',
    'invalidPassword'       => 'Sie können nicht angemeldet werden. Bitte überprüfen Sie Ihr Passwort.',
    'noToken'               => 'Jede Anfrage muss ein Überbringer-Token im Authorization-Header enthalten.',
    'badToken'              => 'Das Zugriffstoken ist ungültig.',
    'oldToken'              => 'Das Zugriffstoken ist abgelaufen.',
    'noUserEntity'          => 'Die Benutzerentität muss für die Passwortüberprüfung angegeben werden.',
    'invalidEmail'          => 'Es konnte nicht überprüft werden, ob die E-Mail-Adresse mit der gespeicherten übereinstimmt.',
    'unableSendEmailToUser' => 'Leider gab es ein Problem beim Senden der E-Mail. Wir konnten keine E-Mail an "{0}" senden.',
    'throttled'             => 'Es wurden zu viele Anfragen von dieser IP-Adresse gestellt. Sie können es in {0} Sekunden erneut versuchen.',

    'email'           => 'E-Mail-Adresse',
    'username'        => 'Benutzername',
    'password'        => 'Passwort',
    'passwordConfirm' => 'Passwort (erneut)',
    'haveAccount'     => 'Haben Sie bereits ein Konto?',

    // Buttons
    'confirm' => 'Bestätigen',
    'send'    => 'Senden',

    // Registration
    'register'         => 'Registrieren',
    'registerDisabled' => 'Die Registrierung ist derzeit nicht erlaubt.',
    'registerSuccess'  => 'Willkommen an Bord!',

    // Login
    'login'              => 'Anmelden',
    'needAccount'        => 'Brauchen Sie ein Konto?',
    'rememberMe'         => 'Angemeldet bleiben',
    'forgotPassword'     => 'Passwort vergessen?',
    'useMagicLink'       => 'Einen Login-Link verwenden',
    'magicLinkSubject'   => 'Ihr Login-Link',
    'magicTokenNotFound' => 'Der Link konnte nicht verifiziert werden.',
    'magicLinkExpired'   => 'Sorry, der Link ist abgelaufen.',
    'checkYourEmail'     => 'Prüfen Sie Ihre E-Mail!',
    'magicLinkDetails'   => 'Wir haben Ihnen gerade eine E-Mail mit einem Login-Link geschickt. Er ist nur für {0} Minuten gültig.',
    'successLogout'      => 'Sie haben sich erfolgreich abgemeldet.',

    // Passwords
    'errorPasswordLength'       => 'Passwörter müssen mindestens {0, number} Zeichen lang sein.',
    'suggestPasswordLength'     => 'Passphrasen - bis zu 255 Zeichen lang - ergeben sicherere Passwörter, die leicht zu merken sind.',
    'errorPasswordCommon'       => 'Das Passwort darf kein allgemeines Passwort sein.',
    'suggestPasswordCommon'     => 'Das Passwort wurde mit über 65-tausend häufig verwendeten Passwörtern oder Passwörtern, die durch Hacks bekannt geworden sind, abgeglichen.',
    'errorPasswordPersonal'     => 'Passwörter dürfen keine gehashten persönlichen Informationen enthalten.',
    'suggestPasswordPersonal'   => 'Variationen Ihrer E-Mail-Adresse oder Ihres Benutzernamens sollten nicht für Passwörter verwendet werden.',
    'errorPasswordTooSimilar'   => 'Das Passwort ist dem Benutzernamen zu ähnlich.',
    'suggestPasswordTooSimilar' => 'Verwenden Sie keine Teile Ihres Benutzernamens in Ihrem Passwort.',
    'errorPasswordPwned'        => 'Das Passwort {0} wurde aufgrund einer Datenschutzverletzung aufgedeckt und wurde {1, number} Mal in {2} kompromittierten Passwörtern gesehen.',
    'suggestPasswordPwned'      => '{0} sollte niemals als Passwort verwendet werden. Wenn Sie es irgendwo verwenden, ändern Sie es sofort.',
    'errorPasswordEmpty'        => 'Ein Passwort ist erforderlich.',
    'passwordChangeSuccess'     => 'Passwort erfolgreich geändert',
    'userDoesNotExist'          => 'Passwort wurde nicht geändert. Der Benutzer existiert nicht',
    'resetTokenExpired'         => 'Tut mir leid. Ihr Reset-Token ist abgelaufen.',

    // 2FA
    'email2FATitle'       => 'Zwei-Faktor-Authentifizierung',
    'confirmEmailAddress' => 'Bestätigen Sie Ihre E-Mail-Adresse.',
    'emailEnterCode'      => 'Bestätigen Sie Ihre E-Mail',
    'emailConfirmCode'    => 'Geben Sie den 6-stelligen Code ein, den wir gerade an Ihre E-Mail-Adresse geschickt haben.',
    'email2FASubject'     => 'Ihr Authentifizierungscode',
    'email2FAMailBody'    => 'Ihr Authentifizierungscode lautet:',
    'invalid2FAToken'     => 'Der Code war falsch.',
    'need2FA'             => 'Sie müssen eine Zwei-Faktor-Verifizierung durchführen.',
    'needVerification'    => 'Überprüfen Sie Ihre E-Mail, um die Kontoaktivierung abzuschließen.',

    // Activate
    'emailActivateTitle'    => 'E-Mail-Aktivierung',
    'emailActivateBody'     => 'Wir haben Ihnen gerade eine E-Mail mit einem Code zur Bestätigung Ihrer E-Mail-Adresse geschickt. Kopieren Sie diesen Code und fügen Sie ihn unten ein.',
    'emailActivateSubject'  => 'Ihr Aktivierungscode',
    'emailActivateMailBody' => 'Bitte verwenden Sie den unten stehenden Code, um Ihr Konto zu aktivieren und die Website zu nutzen.',
    'invalidActivateToken'  => 'Der Code war falsch.',

    // Groups
    'unknownGroup' => '{0} ist eine ungültige Gruppe.',
    'missingTitle' => 'Gruppen müssen einen Titel haben.',

    // Permissions
    'unknownPermission' => '{0} ist keine gültige Berechtigung.',
];
