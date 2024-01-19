<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Call to deprecated function random_string\\(\\)\\:
The type \'basic\', \'md5\', and \'sha1\' are deprecated\\. They are not cryptographically secure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Actions/Email2FA.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Authentication/Actions/Email2FA.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserIdentityModel\\:\\:class is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Authentication/Actions/Email2FA.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to deprecated function random_string\\(\\)\\:
The type \'basic\', \'md5\', and \'sha1\' are deprecated\\. They are not cryptographically secure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Actions/EmailActivator.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserIdentityModel\\:\\:class is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Authentication/Actions/EmailActivator.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authentication.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/src/Authentication/Authenticators/AccessTokens.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, CodeIgniter\\\\I18n\\\\Time\\|null given on the left side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/AccessTokens.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\TokenLoginModel\\:\\:class is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/AccessTokens.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserIdentityModel\\:\\:class is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/AccessTokens.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$credentials \\(array\\{token\\?\\: string\\}\\) of method CodeIgniter\\\\Shield\\\\Authentication\\\\Authenticators\\\\JWT\\:\\:attempt\\(\\) should be contravariant with parameter \\$credentials \\(array\\) of method CodeIgniter\\\\Shield\\\\Authentication\\\\AuthenticatorInterface\\:\\:attempt\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/JWT.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$credentials \\(array\\{token\\?\\: string\\}\\) of method CodeIgniter\\\\Shield\\\\Authentication\\\\Authenticators\\\\JWT\\:\\:check\\(\\) should be contravariant with parameter \\$credentials \\(array\\) of method CodeIgniter\\\\Shield\\\\Authentication\\\\AuthenticatorInterface\\:\\:check\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/JWT.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\TokenLoginModel\\:\\:class is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/JWT.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an elseif condition, string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, CodeIgniter\\\\Shield\\\\Entities\\\\UserIdentity\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in an if condition, int\\|string\\|null given\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$credentials \\(array\\{email\\?\\: string, username\\?\\: string, password\\?\\: string\\}\\) of method CodeIgniter\\\\Shield\\\\Authentication\\\\Authenticators\\\\Session\\:\\:attempt\\(\\) should be contravariant with parameter \\$credentials \\(array\\) of method CodeIgniter\\\\Shield\\\\Authentication\\\\AuthenticatorInterface\\:\\:attempt\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$credentials \\(array\\{email\\?\\: string, username\\?\\: string, password\\?\\: string\\}\\) of method CodeIgniter\\\\Shield\\\\Authentication\\\\Authenticators\\\\Session\\:\\:check\\(\\) should be contravariant with parameter \\$credentials \\(array\\) of method CodeIgniter\\\\Shield\\\\Authentication\\\\AuthenticatorInterface\\:\\:check\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\LoginModel\\:\\:class is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\RememberModel\\:\\:class is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserIdentityModel\\:\\:class is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method CodeIgniter\\\\Shield\\\\Result\\:\\:isOK\\(\\) with incorrect case\\: isOk$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Passwords.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Passwords.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Passwords/CompositionValidator.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/src/Authentication/Passwords/NothingPersonalValidator.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method CodeIgniter\\\\Shield\\\\Result\\:\\:isOK\\(\\) with incorrect case\\: isOk$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Authentication/Passwords/ValidationRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Passwords/ValidationRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in &&, CodeIgniter\\\\Shield\\\\Entities\\\\User\\|null given on the right side\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Passwords/ValidationRules.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/src/Authorization/Groups.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(int\\|string\\|null\\) of method CodeIgniter\\\\Shield\\\\Collectors\\\\Auth\\:\\:getBadgeValue\\(\\) should be covariant with return type \\(int\\|null\\) of method CodeIgniter\\\\Debug\\\\Toolbar\\\\Collectors\\\\BaseCollector\\:\\:getBadgeValue\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Collectors/Auth.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Controllers/ActionController.php',
];
$ignoreErrors[] = [
	'message' => '#^Instanceof between CodeIgniter\\\\Shield\\\\Authentication\\\\Actions\\\\ActionInterface and CodeIgniter\\\\Shield\\\\Authentication\\\\Actions\\\\ActionInterface will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Controllers/ActionController.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to deprecated function random_string\\(\\)\\:
The type \'basic\', \'md5\', and \'sha1\' are deprecated\\. They are not cryptographically secure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Controllers/MagicLinkController.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserIdentityModel\\:\\:class is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Controllers/MagicLinkController.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\LoginModel\\:\\:class is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Controllers/MagicLinkController.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function assert\\(\\) with false and \'Config Auth…\' will always evaluate to false\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Controllers/RegisterController.php',
];
$ignoreErrors[] = [
	'message' => '#^Instanceof between null and CodeIgniter\\\\Shield\\\\Models\\\\UserModel will always evaluate to false\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Controllers/RegisterController.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$name of function model expects a valid class string, array\\|bool\\|float\\|int\\|object\\|string\\|null given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Controllers/RegisterController.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Database\\\\ConnectionInterface\\:\\:disableForeignKeyChecks\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Migrations/2020-12-28-223112_create_auth_tables.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Database\\\\ConnectionInterface\\:\\:enableForeignKeyChecks\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Migrations/2020-12-28-223112_create_auth_tables.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value \\(bool\\|int\\|string\\) of method CodeIgniter\\\\Shield\\\\Entities\\\\Cast\\\\IntBoolCast\\:\\:set\\(\\) should be contravariant with parameter \\$value \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\BaseCast\\:\\:set\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Entities/Cast/IntBoolCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value \\(bool\\|int\\|string\\) of method CodeIgniter\\\\Shield\\\\Entities\\\\Cast\\\\IntBoolCast\\:\\:set\\(\\) should be contravariant with parameter \\$value \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\CastInterface\\:\\:set\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Entities/Cast/IntBoolCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value \\(int\\) of method CodeIgniter\\\\Shield\\\\Entities\\\\Cast\\\\IntBoolCast\\:\\:get\\(\\) should be contravariant with parameter \\$value \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\BaseCast\\:\\:get\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Entities/Cast/IntBoolCast.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value \\(int\\) of method CodeIgniter\\\\Shield\\\\Entities\\\\Cast\\\\IntBoolCast\\:\\:get\\(\\) should be contravariant with parameter \\$value \\(array\\|bool\\|float\\|int\\|object\\|string\\|null\\) of method CodeIgniter\\\\Entity\\\\Cast\\\\CastInterface\\:\\:get\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Entities/Cast/IntBoolCast.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type array\\<string, class\\-string\\> of property CodeIgniter\\\\Shield\\\\Entities\\\\Entity\\:\\:\\$castHandlers is not the same as PHPDoc type array\\<string, string\\> of overridden property CodeIgniter\\\\Entity\\\\Entity\\:\\:\\$castHandlers\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Entities/Entity.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Entities/Group.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 8,
	'path' => __DIR__ . '/src/Entities/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, int\\<0, max\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Entities/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserIdentityModel\\:\\:class is discouraged\\.$#',
	'count' => 19,
	'path' => __DIR__ . '/src/Entities/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\LoginModel\\:\\:class is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Entities/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserModel\\:\\:class is discouraged\\.$#',
	'count' => 9,
	'path' => __DIR__ . '/src/Commands/User.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/AbstractAuthFilter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Shield\\\\Filters\\\\AuthRates\\:\\:before\\(\\) should return CodeIgniter\\\\HTTP\\\\RedirectResponse\\|void but returns CodeIgniter\\\\HTTP\\\\ResponseInterface\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/AuthRates.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method CodeIgniter\\\\HTTP\\\\ResponseInterface\\:\\:setJSON\\(\\) with incorrect case\\: setJson$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Filters/TokenAuth.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/TokenAuth.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Shield\\\\Filters\\\\TokenAuth\\:\\:before\\(\\) should return CodeIgniter\\\\HTTP\\\\RedirectResponse\\|void but returns CodeIgniter\\\\HTTP\\\\ResponseInterface\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Filters/TokenAuth.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to deprecated function random_string\\(\\)\\:
The type \'basic\', \'md5\', and \'sha1\' are deprecated\\. They are not cryptographically secure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Models/TokenLoginModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to deprecated function random_string\\(\\)\\:
The type \'basic\', \'md5\', and \'sha1\' are deprecated\\. They are not cryptographically secure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Models/UserIdentityModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Models/UserModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserIdentityModel\\:\\:class is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Models/UserModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Return type \\(int|string|true\\) of method CodeIgniter\\\\Shield\\\\Models\\\\UserModel\\:\\:insert\\(\\) should be covariant with return type \\(\\(\\$returnID is true \\? int|string|false \\: bool\\) of method CodeIgniter\\\\Model\\:\\:insert\\(\\)\\$#',
	'count' => 4,
	'path' => __DIR__ . '/src/Models/UserModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertInstanceOf\\(\\) with \'CodeIgniter\\\\\\\\Shield\\\\\\\\Result\' and CodeIgniter\\\\Shield\\\\Result will always evaluate to true\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/Authentication/Authenticators/JWTAuthenticatorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertInstanceOf\\(\\) with \'CodeIgniter\\\\\\\\Shield\\\\\\\\Result\' and CodeIgniter\\\\Shield\\\\Result will always evaluate to true\\.$#',
	'count' => 8,
	'path' => __DIR__ . '/tests/Authentication/Authenticators/SessionAuthenticatorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Construct empty\\(\\) is not allowed\\. Use more strict comparison\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Authentication/Filters/AbstractFilterTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Implicit array creation is not allowed \\- variable \\$users might not exist\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Authentication/ForcePasswordResetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$users might not be defined\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Authentication/ForcePasswordResetTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertInstanceOf\\(\\) with \'CodeIgniter\\\\\\\\Shield\\\\\\\\Entities\\\\\\\\AccessToken\' and CodeIgniter\\\\Shield\\\\Entities\\\\AccessToken will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Authentication/HasAccessTokensTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Only booleans are allowed in a ternary operator condition, string\\|null given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/Language/AbstractTranslationTestCase.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertIsString\\(\\) with string will always evaluate to true\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/tests/Unit/Authentication/JWT/JWTManagerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Shield\\\\Models\\\\UserModel\\:\\:getLastQuery\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/UserTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method PHPUnit\\\\Framework\\\\Assert\\:\\:assertInstanceOf\\(\\) with \'CodeIgniter\\\\\\\\Shield\\\\\\\\Entities\\\\\\\\UserIdentity\' and CodeIgniter\\\\Shield\\\\Entities\\\\UserIdentity will always evaluate to true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/UserTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\TokenLoginModel\\:\\:class is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/HmacSha256.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\UserIdentityModel\\:\\:class is discouraged\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/HmacSha256.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\GroupModel\\:\\:class is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Authorization/Traits/Authorizable.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to function model with CodeIgniter\\\\Shield\\\\Models\\\\PermissionModel\\:\\:class is discouraged\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Authorization/Traits/Authorizable.php',
];
return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
