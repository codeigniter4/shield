<?php

namespace CodeIgniter\Shield\Language\es;

return [
    // Excepciones
    'unknownHandler'        => '{0} no es un handler v�lido.',
    'unknownUserProvider'   => 'No podemos determinar que Proveedor de Usuarios usar.',
    'invalidUser'           => 'No podemos localizar este usuario.',
    'badAttempt'            => 'No puedes entrar. Por favor, comprueba tus creenciales.',
    'noPassword'            => 'No se puede validar un usuario sin una contrase�a.',
    'invalidPassword'       => 'No uedes entrar. Por favor, comprueba tu contrase�a.',
    'noToken'               => 'Cada petici�n debe tenerun token en la Authorizaci�n.',
    'badToken'              => 'Token de acceso no v�lido.',
    'oldToken'              => 'El token de acceso ha caducado.',
    'noUserEntity'          => 'Se debe dar una Entidad de Usuario para validar la contrase�a.',
    'invalidEmail'          => 'No podemos verificar que el email coincida con un email registrado.',
    'unableSendEmailToUser' => 'Lo sentimaos, ha habido un problema al enviar el email. No podemos enviar un email a "{0}".',
    'throttled'             => 'demasiadas peticiones hechas desde esta IP. Puedes intentarlo de nuevo en {0} segundos.',

    'email'           => 'Direcci�n Email',
    'username'        => 'Usuario',
    'password'        => 'Contrase�a',
    'passwordConfirm' => 'Contrase�a (de nuevo)',
    'haveAccount'     => '�Ya tienes una cuenta?',

    //Botones
    'confirm' => 'Confirmar',
    'send'    => 'Enviar',

    // Registro
    'register'         => 'Registro',
    'registerDisabled' => 'Actualmente no se permiten registros.',
    'registerSuccess'  => '�Bienvenido a bordo!',

    // Login
    'login'              => 'Entrar',
    'needAccount'        => '�Necesitas una cuenta?',
    'rememberMe'         => '�Recordarme?',
    'forgotPassword'     => '�Has olvidado tu contrase�a?',
    'useMagicLink'       => 'Recordar contrase�a',
    'magicLinkSubject'   => 'Tu Enlace para Entrar',
    'magicTokenNotFound' => 'No podemos verificar el enlace.',
    'magicLinkExpired'   => 'Lo sentimos, el enlace ha caducado.',
    'checkYourEmail'     => 'Comprueba tu email',
    'magicLinkDetails'   => 'Te hemos enviado un email que contiene un enlace para Entrar. Solo es v�lido durante {0} minutos.',
    'successLogout'      => 'Has salido de forma correcta.',

    // Contrase�as
    'errorPasswordLength'       => 'La contrase�a debe tener al menos {0, number} caracteres.',
    'suggestPasswordLength'     => 'Las claves de acceso, de hasta 255 caracteres, crean contrase�as m�s seguras y f�ciles de recordar.',
    'errorPasswordCommon'       => 'La contrase�a no debe ser una contrase�a com�n.',
    'suggestPasswordCommon'     => 'La contrase�a se compar� con m�s de 65.000 contrase�as de uso com�n o contrase�as que se filtraron a trav�s de hacks.',
    'errorPasswordPersonal'     => 'Las contrase�as no pueden contener informaci�n personal modificada.',
    'suggestPasswordPersonal'   => 'No deben usarse variaciones de tu direcci�n de correo electr�nico o nombre de usuario para contrase�as.',
    'errorPasswordTooSimilar'   => 'La contrase�a es demasiado parecida al usuario.',
    'suggestPasswordTooSimilar' => 'No uses partes de tu usuario en tu contrase�a.',
    'errorPasswordPwned'        => 'La contrase�a {0} ha quedado expuesta debido a una violaci�n de datos y se ha visto comprometida {1, n�mero} veces en {2} contrase�as.',
    'suggestPasswordPwned'      => '{0} no se debe usar nunca como contrase�a. Si la est�s usando en alg�n sitio, c�mbiala inmediatamente.',
    'errorPasswordEmpty'        => 'Se necesita una contrase�a.',
    'passwordChangeSuccess'     => 'Contrase�a modificada correctamente',
    'userDoesNotExist'          => 'No se ha cambiado la contrase�a. No existe el usuario',
    'resetTokenExpired'         => 'Lo sentimos. Tu token de reseteo ha caducado.',

    // 2FA
    'email2FATitle'    	  => 'Authenticaci�n de Doble Factor',
    'confirmEmailAddress' => 'Confirma tu direcci�n de email.',
    'emailEnterCode'   	  => 'Confirma tu Email',
    'emailConfirmCode' 	  => 'teclea el c�digo de 6 d�gitos qu ete hemos enviado a tu direcci�n email.',
    'email2FASubject'  	  => 'Tu c�digo de autenticaci�n',
    'email2FAMailBody'    => 'Tu c�digo de autenticaci�n es:',
    'invalid2FAToken'  	  => 'El token era incorrecto.',
    'need2FA'             => 'Debes completar la verificaci�n de doble factor.',
    'needVerification'    => 'Comprueba tu buz�n para completar la activaci�n de la cuenta.',

    // Activar
    'emailActivateTitle'    => 'Email de Activaci�n',
    'emailActivateBody'     => 'Te enviamos un email con un c�digo, para confirmar tu direcci�n email. Copia ese c�digo y p�galo abajo.',
    'emailActivateSubject'  => 'Tu c�digo de activaci�n',
    'emailActivateMailBody' => 'Por favor, usa el c�digo de abajo para activar tu cuenta y empezar a usar el sitio.',
    'invalidActivateToken'  => 'El c�digo no es correcto.',

    // Grupos
    'unknownGroup' => '{0} no es un grupo v�lido.',
    'missingTitle' => 'Los grupos deben tener un t�tulo.',

    // Permisos
    'unknownPermission' => '{0} no es un permiso v�lido.',
];
