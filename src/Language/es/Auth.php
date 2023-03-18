<?php

declare(strict_types=1);

return [
    // Excepciones
    'unknownAuthenticator'  => '{0} no es un handler válido.',
    'unknownUserProvider'   => 'No podemos determinar que Proveedor de Usuarios usar.',
    'invalidUser'           => 'No podemos localizar este usuario.',
    'bannedUser'            => '(To be translated) Can not log you in as you are currently banned.',
    'logOutBannedUser'      => '(To be translated) You have been logged out because you have been banned.',
    'badAttempt'            => 'No puedes entrar. Por favor, comprueba tus creenciales.',
    'noPassword'            => 'No se puede validar un usuario sin una contraseña.',
    'invalidPassword'       => 'No uedes entrar. Por favor, comprueba tu contraseña.',
    'noToken'               => 'Cada petición debe tenerun token en la {0}.',
    'badToken'              => 'Token de acceso no válido.',
    'oldToken'              => 'El token de acceso ha caducado.',
    'noUserEntity'          => 'Se debe dar una Entidad de Usuario para validar la contraseña.',
    'invalidEmail'          => 'No podemos verificar que el email coincida con un email registrado.',
    'unableSendEmailToUser' => 'Lo sentimaos, ha habido un problema al enviar el email. No podemos enviar un email a "{0}".',
    'throttled'             => 'Demasiadas peticiones hechas desde esta IP. Puedes intentarlo de nuevo en {0} segundos.',
    'notEnoughPrivilege'    => 'No tiene los permisos necesarios para realizar la operación deseada.',

    'email'           => 'Dirección Email',
    'username'        => 'Usuario',
    'password'        => 'Contraseña',
    'passwordConfirm' => 'Contraseña (de nuevo)',
    'haveAccount'     => '¿Ya tienes una cuenta?',

    // Botones
    'confirm' => 'Confirmar',
    'send'    => 'Enviar',

    // Registro
    'register'         => 'Registro',
    'registerDisabled' => 'Actualmente no se permiten registros.',
    'registerSuccess'  => '¡Bienvenido a bordo!',

    // Login
    'login'              => 'Entrar',
    'needAccount'        => '¿Necesitas una cuenta?',
    'rememberMe'         => '¿Recordarme?',
    'forgotPassword'     => '¿Has olvidado tu contraseña?',
    'useMagicLink'       => 'Recordar contraseña',
    'magicLinkSubject'   => 'Tu Enlace para Entrar',
    'magicTokenNotFound' => 'No podemos verificar el enlace.',
    'magicLinkExpired'   => 'Lo sentimos, el enlace ha caducado.',
    'checkYourEmail'     => 'Comprueba tu email',
    'magicLinkDetails'   => 'Te hemos enviado un email que contiene un enlace para Entrar. Solo es válido durante {0} minutos.',
    'successLogout'      => 'Has salido de forma correcta.',

    // Contraseñas
    'errorPasswordLength'       => 'La contraseña debe tener al menos {0, number} caracteres.',
    'suggestPasswordLength'     => 'Las claves de acceso, de hasta 255 caracteres, crean contraseñas más seguras y fáciles de recordar.',
    'errorPasswordCommon'       => 'La contraseña no debe ser una contraseña común.',
    'suggestPasswordCommon'     => 'La contraseña se comparó con más de 65.000 contraseñas de uso común o contraseñas que se filtraron a través de hacks.',
    'errorPasswordPersonal'     => 'Las contraseñas no pueden contener información personal modificada.',
    'suggestPasswordPersonal'   => 'No deben usarse variaciones de tu dirección de correo electrónico o nombre de usuario para contraseñas.',
    'errorPasswordTooSimilar'   => 'La contraseña es demasiado parecida al usuario.',
    'suggestPasswordTooSimilar' => 'No uses partes de tu usuario en tu contraseña.',
    'errorPasswordPwned'        => 'La contraseña {0} ha quedado expuesta debido a una violación de datos y se ha visto comprometida {1, número} veces en {2} contraseñas.',
    'suggestPasswordPwned'      => '{0} no se debe usar nunca como contraseña. Si la estás usando en algún sitio, cámbiala inmediatamente.',
    'errorPasswordEmpty'        => 'Se necesita una contraseña.',
    'errorPasswordTooLongBytes' => '(To be translated) Password cannot exceed {param} bytes in length.',
    'passwordChangeSuccess'     => 'Contraseña modificada correctamente',
    'userDoesNotExist'          => 'No se ha cambiado la contraseña. No existe el usuario',
    'resetTokenExpired'         => 'Lo sentimos. Tu token de reseteo ha caducado.',

    // Email Globals
    'emailInfo'      => 'Algunos datos sobre la persona:',
    'emailIpAddress' => 'Dirección IP:',
    'emailDevice'    => 'Dispositivo:',
    'emailDate'      => 'Fecha:',

    // 2FA
    'email2FATitle'       => 'Authenticación de Doble Factor',
    'confirmEmailAddress' => 'Confirma tu dirección de email.',
    'emailEnterCode'      => 'Confirma tu Email',
    'emailConfirmCode'    => 'teclea el código de 6 dígitos qu ete hemos enviado a tu dirección email.',
    'email2FASubject'     => 'Tu código de autenticación',
    'email2FAMailBody'    => 'Tu código de autenticación es:',
    'invalid2FAToken'     => 'El token era incorrecto.',
    'need2FA'             => 'Debes completar la verificación de doble factor.',
    'needVerification'    => 'Comprueba tu buzón para completar la activación de la cuenta.',

    // Activar
    'emailActivateTitle'    => 'Email de Activación',
    'emailActivateBody'     => 'Te enviamos un email con un código, para confirmar tu dirección email. Copia ese código y pégalo abajo.',
    'emailActivateSubject'  => 'Tu código de activación',
    'emailActivateMailBody' => 'Por favor, usa el código de abajo para activar tu cuenta y empezar a usar el sitio.',
    'invalidActivateToken'  => 'El código no es correcto.',
    'needActivate'          => '(To be translated) You must complete your registration by confirming the code sent to your email address.',
    'activationBlocked'     => '(to be translated) You must activate your account before logging in.',

    // Grupos
    'unknownGroup' => '{0} no es un grupo válido.',
    'missingTitle' => 'Los grupos deben tener un título.',

    // Permisos
    'unknownPermission' => '{0} no es un permiso válido.',
];
