<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Call to deprecated function random_string\\(\\)\\:
The type \'basic\', \'md5\', and \'sha1\' are deprecated\\. They are not cryptographically secure\\.$#',
	'identifier' => 'function.deprecated',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Actions/Email2FA.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserIdentityModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 2,
	'path' => __DIR__ . '/src/Authentication/Actions/Email2FA.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'identifier' => 'empty.notAllowed',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Actions/Email2FA.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to deprecated function random_string\\(\\)\\:
The type \'basic\', \'md5\', and \'sha1\' are deprecated\\. They are not cryptographically secure\\.$#',
	'identifier' => 'function.deprecated',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Actions/EmailActivator.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserIdentityModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 2,
	'path' => __DIR__ . '/src/Authentication/Actions/EmailActivator.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'identifier' => 'empty.notAllowed',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authentication.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\TokenLoginModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/AccessTokens.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserIdentityModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/AccessTokens.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'identifier' => 'empty.notAllowed',
	'count' => 2,
	'path' => __DIR__ . '/src/Authentication/Authenticators/AccessTokens.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, CodeIgniter\\\\I18n\\\\Time\\|null given on the left side\\.$#',
	'identifier' => 'booleanAnd.leftNotBoolean',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/AccessTokens.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\TokenLoginModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/HmacSha256.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserIdentityModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/HmacSha256.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\TokenLoginModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/JWT.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$credentials \\(array\\{token\\?\\: string\\}\\) of method CodeIgniter\\\\Shield\\\\Authentication\\\\Authenticators\\\\JWT\\:\\:attempt\\(\\) should be contravariant with parameter \\$credentials \\(array\\) of method CodeIgniter\\\\Shield\\\\Authentication\\\\AuthenticatorInterface\\:\\:attempt\\(\\)$#',
	'identifier' => 'method.childParameterType',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/JWT.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$credentials \\(array\\{token\\?\\: string\\}\\) of method CodeIgniter\\\\Shield\\\\Authentication\\\\Authenticators\\\\JWT\\:\\:check\\(\\) should be contravariant with parameter \\$credentials \\(array\\) of method CodeIgniter\\\\Shield\\\\Authentication\\\\AuthenticatorInterface\\:\\:check\\(\\)$#',
	'identifier' => 'method.childParameterType',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/JWT.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\LoginModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\RememberModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserIdentityModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'identifier' => 'empty.notAllowed',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, int\\|string\\|null given\\.$#',
	'identifier' => 'if.condNotBoolean',
	'count' => 3,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$credentials \\(array\\{email\\?\\: string, username\\?\\: string, password\\?\\: string\\}\\) of method CodeIgniter\\\\Shield\\\\Authentication\\\\Authenticators\\\\Session\\:\\:attempt\\(\\) should be contravariant with parameter \\$credentials \\(array\\) of method CodeIgniter\\\\Shield\\\\Authentication\\\\AuthenticatorInterface\\:\\:attempt\\(\\)$#',
	'identifier' => 'method.childParameterType',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$credentials \\(array\\{email\\?\\: string, username\\?\\: string, password\\?\\: string\\}\\) of method CodeIgniter\\\\Shield\\\\Authentication\\\\Authenticators\\\\Session\\:\\:check\\(\\) should be contravariant with parameter \\$credentials \\(array\\) of method CodeIgniter\\\\Shield\\\\Authentication\\\\AuthenticatorInterface\\:\\:check\\(\\)$#',
	'identifier' => 'method.childParameterType',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'identifier' => 'empty.notAllowed',
	'count' => 4,
	'path' => __DIR__ . '/src/Authentication/Passwords/NothingPersonalValidator.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @var with type string is not subtype of type uppercase\\-string\\.$#',
	'identifier' => 'varTag.type',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Passwords/PwnedValidator.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, CodeIgniter\\\\Shield\\\\Entities\\\\User\\|null given on the right side\\.$#',
	'identifier' => 'booleanAnd.rightNotBoolean',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Passwords/ValidationRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'identifier' => 'empty.notAllowed',
	'count' => 3,
	'path' => __DIR__ . '/src/Authorization/Groups.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(int\\|string\\|null\\) of method CodeIgniter\\\\Shield\\\\Collectors\\\\Auth\\:\\:getBadgeValue\\(\\) should be covariant with return type \\(int\\|null\\) of method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\BaseCollector\\:\\:getBadgeValue\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Collectors/Auth.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$id on array\\<string, string\\>\\|object\\.$#',
	'identifier' => 'property.nonObject',
	'count' => 7,
	'path' => __DIR__ . '/src/Commands/Hmac.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$secret2 on array\\<string, string\\>\\|object\\.$#',
	'identifier' => 'property.nonObject',
	'count' => 11,
	'path' => __DIR__ . '/src/Commands/Hmac.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\GroupModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 1,
	'path' => __DIR__ . '/src/Commands/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 9,
	'path' => __DIR__ . '/src/Commands/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to deprecated function random_string\\(\\)\\:
The type \'basic\', \'md5\', and \'sha1\' are deprecated\\. They are not cryptographically secure\\.$#',
	'identifier' => 'function.deprecated',
	'count' => 1,
	'path' => __DIR__ . '/src/Controllers/MagicLinkController.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\LoginModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 1,
	'path' => __DIR__ . '/src/Controllers/MagicLinkController.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserIdentityModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 2,
	'path' => __DIR__ . '/src/Controllers/MagicLinkController.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function assert\\(\\) with false and \'Config Auth…\' will always evaluate to false\\.$#',
	'identifier' => 'function.impossibleType',
	'count' => 1,
	'path' => __DIR__ . '/src/Controllers/RegisterController.php',
];
$ignoreErrors[] = [
	'message' => '#^Instanceof between null and CodeIgniter\\\\Shield\\\\Models\\\\UserModel will always evaluate to false\\.$#',
	'identifier' => 'instanceof.alwaysFalse',
	'count' => 1,
	'path' => __DIR__ . '/src/Controllers/RegisterController.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$name of function model expects a valid class string, array\\|bool\\|float\\|int\\|object\\|string\\|null given\\.$#',
	'identifier' => 'codeigniter.modelArgumentType',
	'count' => 1,
	'path' => __DIR__ . '/src/Controllers/RegisterController.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Database\\\\ConnectionInterface\\:\\:disableForeignKeyChecks\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Migrations/2020-12-28-223112_create_auth_tables.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Database\\\\ConnectionInterface\\:\\:enableForeignKeyChecks\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Migrations/2020-12-28-223112_create_auth_tables.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\GroupModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 4,
	'path' => __DIR__ . '/src/Entities/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\LoginModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 2,
	'path' => __DIR__ . '/src/Entities/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\PermissionModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 2,
	'path' => __DIR__ . '/src/Entities/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserIdentityModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 19,
	'path' => __DIR__ . '/src/Entities/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'identifier' => 'empty.notAllowed',
	'count' => 7,
	'path' => __DIR__ . '/src/Entities/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\<0, max\\> given\\.$#',
	'identifier' => 'ternary.condNotBoolean',
	'count' => 1,
	'path' => __DIR__ . '/src/Entities/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'identifier' => 'empty.notAllowed',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/AbstractAuthFilter.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\HTTP\\\\RedirectResponse\\|void\\) of method CodeIgniter\\\\Shield\\\\Filters\\\\AbstractAuthFilter\\:\\:before\\(\\) should be covariant with return type \\(CodeIgniter\\\\HTTP\\\\RequestInterface\\|CodeIgniter\\\\HTTP\\\\ResponseInterface\\|string\\|null\\) of method CodeIgniter\\\\Filters\\\\FilterInterface\\:\\:before\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/AbstractAuthFilter.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(void\\) of method CodeIgniter\\\\Shield\\\\Filters\\\\AbstractAuthFilter\\:\\:after\\(\\) should be compatible with return type \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\|null\\) of method CodeIgniter\\\\Filters\\\\FilterInterface\\:\\:after\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/AbstractAuthFilter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Shield\\\\Filters\\\\AuthRates\\:\\:before\\(\\) should return CodeIgniter\\\\HTTP\\\\RedirectResponse\\|void but returns CodeIgniter\\\\HTTP\\\\ResponseInterface\\.$#',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/AuthRates.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\HTTP\\\\RedirectResponse\\|void\\) of method CodeIgniter\\\\Shield\\\\Filters\\\\AuthRates\\:\\:before\\(\\) should be covariant with return type \\(CodeIgniter\\\\HTTP\\\\RequestInterface\\|CodeIgniter\\\\HTTP\\\\ResponseInterface\\|string\\|null\\) of method CodeIgniter\\\\Filters\\\\FilterInterface\\:\\:before\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/AuthRates.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(void\\) of method CodeIgniter\\\\Shield\\\\Filters\\\\AuthRates\\:\\:after\\(\\) should be compatible with return type \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\|null\\) of method CodeIgniter\\\\Filters\\\\FilterInterface\\:\\:after\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/AuthRates.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\HTTP\\\\RedirectResponse\\|void\\) of method CodeIgniter\\\\Shield\\\\Filters\\\\ChainAuth\\:\\:before\\(\\) should be covariant with return type \\(CodeIgniter\\\\HTTP\\\\RequestInterface\\|CodeIgniter\\\\HTTP\\\\ResponseInterface\\|string\\|null\\) of method CodeIgniter\\\\Filters\\\\FilterInterface\\:\\:before\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/ChainAuth.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(void\\) of method CodeIgniter\\\\Shield\\\\Filters\\\\ChainAuth\\:\\:after\\(\\) should be compatible with return type \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\|null\\) of method CodeIgniter\\\\Filters\\\\FilterInterface\\:\\:after\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/ChainAuth.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\HTTP\\\\RedirectResponse\\|void\\) of method CodeIgniter\\\\Shield\\\\Filters\\\\ForcePasswordResetFilter\\:\\:before\\(\\) should be covariant with return type \\(CodeIgniter\\\\HTTP\\\\RequestInterface\\|CodeIgniter\\\\HTTP\\\\ResponseInterface\\|string\\|null\\) of method CodeIgniter\\\\Filters\\\\FilterInterface\\:\\:before\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/ForcePasswordResetFilter.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(void\\) of method CodeIgniter\\\\Shield\\\\Filters\\\\ForcePasswordResetFilter\\:\\:after\\(\\) should be compatible with return type \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\|null\\) of method CodeIgniter\\\\Filters\\\\FilterInterface\\:\\:after\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/ForcePasswordResetFilter.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(void\\) of method CodeIgniter\\\\Shield\\\\Filters\\\\HmacAuth\\:\\:after\\(\\) should be compatible with return type \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\|null\\) of method CodeIgniter\\\\Filters\\\\FilterInterface\\:\\:after\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/HmacAuth.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\|void\\) of method CodeIgniter\\\\Shield\\\\Filters\\\\JWTAuth\\:\\:before\\(\\) should be covariant with return type \\(CodeIgniter\\\\HTTP\\\\RequestInterface\\|CodeIgniter\\\\HTTP\\\\ResponseInterface\\|string\\|null\\) of method CodeIgniter\\\\Filters\\\\FilterInterface\\:\\:before\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/JWTAuth.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(void\\) of method CodeIgniter\\\\Shield\\\\Filters\\\\JWTAuth\\:\\:after\\(\\) should be compatible with return type \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\|null\\) of method CodeIgniter\\\\Filters\\\\FilterInterface\\:\\:after\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/JWTAuth.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\HTTP\\\\RedirectResponse\\|void\\) of method CodeIgniter\\\\Shield\\\\Filters\\\\SessionAuth\\:\\:before\\(\\) should be covariant with return type \\(CodeIgniter\\\\HTTP\\\\RequestInterface\\|CodeIgniter\\\\HTTP\\\\ResponseInterface\\|string\\|null\\) of method CodeIgniter\\\\Filters\\\\FilterInterface\\:\\:before\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/SessionAuth.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(void\\) of method CodeIgniter\\\\Shield\\\\Filters\\\\SessionAuth\\:\\:after\\(\\) should be compatible with return type \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\|null\\) of method CodeIgniter\\\\Filters\\\\FilterInterface\\:\\:after\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/SessionAuth.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'identifier' => 'empty.notAllowed',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/TokenAuth.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Shield\\\\Filters\\\\TokenAuth\\:\\:before\\(\\) should return CodeIgniter\\\\HTTP\\\\RedirectResponse\\|void but returns CodeIgniter\\\\HTTP\\\\ResponseInterface\\.$#',
	'identifier' => 'return.type',
	'count' => 2,
	'path' => __DIR__ . '/src/Filters/TokenAuth.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(CodeIgniter\\\\HTTP\\\\RedirectResponse\\|void\\) of method CodeIgniter\\\\Shield\\\\Filters\\\\TokenAuth\\:\\:before\\(\\) should be covariant with return type \\(CodeIgniter\\\\HTTP\\\\RequestInterface\\|CodeIgniter\\\\HTTP\\\\ResponseInterface\\|string\\|null\\) of method CodeIgniter\\\\Filters\\\\FilterInterface\\:\\:before\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/TokenAuth.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(void\\) of method CodeIgniter\\\\Shield\\\\Filters\\\\TokenAuth\\:\\:after\\(\\) should be compatible with return type \\(CodeIgniter\\\\HTTP\\\\ResponseInterface\\|null\\) of method CodeIgniter\\\\Filters\\\\FilterInterface\\:\\:after\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/TokenAuth.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to deprecated function random_string\\(\\)\\:
The type \'basic\', \'md5\', and \'sha1\' are deprecated\\. They are not cryptographically secure\\.$#',
	'identifier' => 'function.deprecated',
	'count' => 1,
	'path' => __DIR__ . '/src/Models/TokenLoginModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to deprecated function random_string\\(\\)\\:
The type \'basic\', \'md5\', and \'sha1\' are deprecated\\. They are not cryptographically secure\\.$#',
	'identifier' => 'function.deprecated',
	'count' => 1,
	'path' => __DIR__ . '/src/Models/UserIdentityModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\GroupModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 1,
	'path' => __DIR__ . '/src/Models/UserModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserIdentityModel\\:\\:class is discouraged\\.$#',
	'identifier' => 'codeigniter.factoriesClassConstFetch',
	'count' => 1,
	'path' => __DIR__ . '/src/Models/UserModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'identifier' => 'empty.notAllowed',
	'count' => 2,
	'path' => __DIR__ . '/src/Models/UserModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$row \\(array\\|CodeIgniter\\\\Shield\\\\Entities\\\\User\\) of method CodeIgniter\\\\Shield\\\\Models\\\\UserModel\\:\\:insert\\(\\) should be contravariant with parameter \\$row \\(array\\<int\\|string, bool\\|float\\|int\\|object\\|string\\|null\\>\\|object\\|null\\) of method CodeIgniter\\\\Model\\:\\:insert\\(\\)$#',
	'identifier' => 'method.childParameterType',
	'count' => 1,
	'path' => __DIR__ . '/src/Models/UserModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$row \\(array\\|CodeIgniter\\\\Shield\\\\Entities\\\\User\\) of method CodeIgniter\\\\Shield\\\\Models\\\\UserModel\\:\\:save\\(\\) should be contravariant with parameter \\$row \\(array\\<int\\|string, bool\\|float\\|int\\|object\\|string\\|null\\>\\|object\\) of method CodeIgniter\\\\BaseModel\\:\\:save\\(\\)$#',
	'identifier' => 'method.childParameterType',
	'count' => 1,
	'path' => __DIR__ . '/src/Models/UserModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$row \\(array\\|CodeIgniter\\\\Shield\\\\Entities\\\\User\\) of method CodeIgniter\\\\Shield\\\\Models\\\\UserModel\\:\\:update\\(\\) should be contravariant with parameter \\$row \\(array\\<int\\|string, bool\\|float\\|int\\|object\\|string\\|null\\>\\|object\\|null\\) of method CodeIgniter\\\\Model\\:\\:update\\(\\)$#',
	'identifier' => 'method.childParameterType',
	'count' => 1,
	'path' => __DIR__ . '/src/Models/UserModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(int\\|string\\|true\\) of method CodeIgniter\\\\Shield\\\\Models\\\\UserModel\\:\\:insert\\(\\) should be covariant with return type \\(\\(\\$returnID is true \\? int\\|string\\|false \\: bool\\)\\) of method CodeIgniter\\\\Model\\:\\:insert\\(\\)$#',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Models/UserModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertInstanceOf\\(\\) with \'CodeIgniter\\\\\\\\Shield\\\\\\\\Result\' and CodeIgniter\\\\Shield\\\\Result will always evaluate to true\\.$#',
	'identifier' => 'method.alreadyNarrowedType',
	'count' => 3,
	'path' => __DIR__ . '/tests/Authentication/Authenticators/JWTAuthenticatorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertInstanceOf\\(\\) with \'CodeIgniter\\\\\\\\Shield\\\\\\\\Result\' and CodeIgniter\\\\Shield\\\\Result will always evaluate to true\\.$#',
	'identifier' => 'method.alreadyNarrowedType',
	'count' => 8,
	'path' => __DIR__ . '/tests/Authentication/Authenticators/SessionAuthenticatorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Implicit array creation is not allowed \\- variable \\$users might not exist\\.$#',
	'identifier' => 'variable.implicitArray',
	'count' => 1,
	'path' => __DIR__ . '/tests/Authentication/ForcePasswordResetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$users might not be defined\\.$#',
	'identifier' => 'variable.undefined',
	'count' => 1,
	'path' => __DIR__ . '/tests/Authentication/ForcePasswordResetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertInstanceOf\\(\\) with \'CodeIgniter\\\\\\\\Shield\\\\\\\\Entities\\\\\\\\AccessToken\' and CodeIgniter\\\\Shield\\\\Entities\\\\AccessToken will always evaluate to true\\.$#',
	'identifier' => 'method.alreadyNarrowedType',
	'count' => 1,
	'path' => __DIR__ . '/tests/Authentication/HasAccessTokensTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, string\\|null given\\.$#',
	'identifier' => 'ternary.condNotBoolean',
	'count' => 2,
	'path' => __DIR__ . '/tests/Language/AbstractTranslationTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertIsString\\(\\) with string will always evaluate to true\\.$#',
	'identifier' => 'method.alreadyNarrowedType',
	'count' => 6,
	'path' => __DIR__ . '/tests/Unit/Authentication/JWT/JWTManagerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Shield\\\\Models\\\\UserModel\\:\\:getLastQuery\\(\\)\\.$#',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/UserTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertInstanceOf\\(\\) with \'CodeIgniter\\\\\\\\Shield\\\\\\\\Entities\\\\\\\\UserIdentity\' and CodeIgniter\\\\Shield\\\\Entities\\\\UserIdentity will always evaluate to true\\.$#',
	'identifier' => 'method.alreadyNarrowedType',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/UserTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
