<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Call to deprecated function random_string\\(\\)\\:
The type \'basic\', \'md5\', and \'sha1\' are deprecated\\. They are not cryptographically secure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Actions/Email2FA.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to deprecated function random_string\\(\\)\\:
The type \'basic\', \'md5\', and \'sha1\' are deprecated\\. They are not cryptographically secure\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Actions/EmailActivator.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to deprecated function random_string\\(\\)\\:
The type \'basic\', \'md5\', and \'sha1\' are deprecated\\. They are not cryptographically secure\\.$#',
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
	'message' => '#^Method CodeIgniter\\\\Shield\\\\Filters\\\\AuthRates\\:\\:before\\(\\) should return CodeIgniter\\\\HTTP\\\\RedirectResponse\\|void but returns CodeIgniter\\\\HTTP\\\\ResponseInterface\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/AuthRates.php',
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
	'message' => '#^Cannot access property \\$id on array\\|object\\.$#',
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
	'message' => '#^Cannot access property \\$id on array\\|object\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Models/UserIdentityModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$id on array\\|object\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/Authentication/AuthHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$id on array\\|object\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/Authentication/Authenticators/AccessTokenAuthenticatorTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$id on array\\|object\\.$#',
	'count' => 6,
	'path' => __DIR__ . '/tests/Authentication/Filters/SessionFilterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$id on array\\|object\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/Authentication/HasAccessTokensTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$id on array\\|object\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/Authorization/AuthorizableTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$id on array\\|object\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/UserModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method CodeIgniter\\\\Shield\\\\Models\\\\UserModel\\:\\:getLastQuery\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/UserTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$active on array\\|object\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/Unit/UserTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$email on array\\|object\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/UserTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$id on array\\|object\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/Unit/UserTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$password_hash on array\\|object\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/UserTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
