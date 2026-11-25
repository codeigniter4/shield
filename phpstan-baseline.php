<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'rawMessage' => 'Parameter #1 $credentials (array{token?: string}) of method CodeIgniter\\Shield\\Authentication\\Authenticators\\JWT::attempt() should be contravariant with parameter $credentials (array) of method CodeIgniter\\Shield\\Authentication\\AuthenticatorInterface::attempt()',
	'identifier' => 'method.childParameterType',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/JWT.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Parameter #1 $credentials (array{token?: string}) of method CodeIgniter\\Shield\\Authentication\\Authenticators\\JWT::check() should be contravariant with parameter $credentials (array) of method CodeIgniter\\Shield\\Authentication\\AuthenticatorInterface::check()',
	'identifier' => 'method.childParameterType',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/JWT.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Parameter #1 $credentials (array{email?: string, username?: string, password?: string, ...<string, string>}) of method CodeIgniter\\Shield\\Authentication\\Authenticators\\Session::attempt() should be contravariant with parameter $credentials (array) of method CodeIgniter\\Shield\\Authentication\\AuthenticatorInterface::attempt()',
	'identifier' => 'method.childParameterType',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Parameter #1 $credentials (array{email?: string, username?: string, password?: string, ...<string, string>}) of method CodeIgniter\\Shield\\Authentication\\Authenticators\\Session::check() should be contravariant with parameter $credentials (array) of method CodeIgniter\\Shield\\Authentication\\AuthenticatorInterface::check()',
	'identifier' => 'method.childParameterType',
	'count' => 1,
	'path' => __DIR__ . '/src/Authentication/Authenticators/Session.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (int|string|null) of method CodeIgniter\\Shield\\Collectors\\Auth::getBadgeValue() should be covariant with return type (int|null) of method CodeIgniter\\Debug\\Toolbar\\Collectors\\BaseCollector::getBadgeValue()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Collectors/Auth.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Call to function assert() with false and \'Config Auth…\' will always evaluate to false.',
	'identifier' => 'function.impossibleType',
	'count' => 1,
	'path' => __DIR__ . '/src/Controllers/RegisterController.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Instanceof between null and CodeIgniter\\Shield\\Models\\UserModel will always evaluate to false.',
	'identifier' => 'instanceof.alwaysFalse',
	'count' => 1,
	'path' => __DIR__ . '/src/Controllers/RegisterController.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Parameter #1 $name of function model expects a valid class string, array|bool|float|int|object|string|null given.',
	'identifier' => 'codeigniter.modelArgumentType',
	'count' => 1,
	'path' => __DIR__ . '/src/Controllers/RegisterController.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Call to an undefined method CodeIgniter\\Database\\ConnectionInterface::disableForeignKeyChecks().',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Migrations/2020-12-28-223112_create_auth_tables.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Call to an undefined method CodeIgniter\\Database\\ConnectionInterface::enableForeignKeyChecks().',
	'identifier' => 'method.notFound',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Migrations/2020-12-28-223112_create_auth_tables.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (CodeIgniter\\HTTP\\RedirectResponse|void) of method CodeIgniter\\Shield\\Filters\\AbstractAuthFilter::before() should be covariant with return type (CodeIgniter\\HTTP\\RequestInterface|CodeIgniter\\HTTP\\ResponseInterface|string|null) of method CodeIgniter\\Filters\\FilterInterface::before()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/AbstractAuthFilter.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (void) of method CodeIgniter\\Shield\\Filters\\AbstractAuthFilter::after() should be compatible with return type (CodeIgniter\\HTTP\\ResponseInterface|null) of method CodeIgniter\\Filters\\FilterInterface::after()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/AbstractAuthFilter.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Method CodeIgniter\\Shield\\Filters\\AuthRates::before() should return CodeIgniter\\HTTP\\RedirectResponse|void but returns CodeIgniter\\HTTP\\ResponseInterface.',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/AuthRates.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (CodeIgniter\\HTTP\\RedirectResponse|void) of method CodeIgniter\\Shield\\Filters\\AuthRates::before() should be covariant with return type (CodeIgniter\\HTTP\\RequestInterface|CodeIgniter\\HTTP\\ResponseInterface|string|null) of method CodeIgniter\\Filters\\FilterInterface::before()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/AuthRates.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (void) of method CodeIgniter\\Shield\\Filters\\AuthRates::after() should be compatible with return type (CodeIgniter\\HTTP\\ResponseInterface|null) of method CodeIgniter\\Filters\\FilterInterface::after()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/AuthRates.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (CodeIgniter\\HTTP\\RedirectResponse|void) of method CodeIgniter\\Shield\\Filters\\ChainAuth::before() should be covariant with return type (CodeIgniter\\HTTP\\RequestInterface|CodeIgniter\\HTTP\\ResponseInterface|string|null) of method CodeIgniter\\Filters\\FilterInterface::before()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/ChainAuth.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (void) of method CodeIgniter\\Shield\\Filters\\ChainAuth::after() should be compatible with return type (CodeIgniter\\HTTP\\ResponseInterface|null) of method CodeIgniter\\Filters\\FilterInterface::after()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/ChainAuth.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (CodeIgniter\\HTTP\\RedirectResponse|void) of method CodeIgniter\\Shield\\Filters\\ForcePasswordResetFilter::before() should be covariant with return type (CodeIgniter\\HTTP\\RequestInterface|CodeIgniter\\HTTP\\ResponseInterface|string|null) of method CodeIgniter\\Filters\\FilterInterface::before()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/ForcePasswordResetFilter.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (void) of method CodeIgniter\\Shield\\Filters\\ForcePasswordResetFilter::after() should be compatible with return type (CodeIgniter\\HTTP\\ResponseInterface|null) of method CodeIgniter\\Filters\\FilterInterface::after()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/ForcePasswordResetFilter.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (void) of method CodeIgniter\\Shield\\Filters\\HmacAuth::after() should be compatible with return type (CodeIgniter\\HTTP\\ResponseInterface|null) of method CodeIgniter\\Filters\\FilterInterface::after()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/HmacAuth.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (CodeIgniter\\HTTP\\ResponseInterface|void) of method CodeIgniter\\Shield\\Filters\\JWTAuth::before() should be covariant with return type (CodeIgniter\\HTTP\\RequestInterface|CodeIgniter\\HTTP\\ResponseInterface|string|null) of method CodeIgniter\\Filters\\FilterInterface::before()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/JWTAuth.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (void) of method CodeIgniter\\Shield\\Filters\\JWTAuth::after() should be compatible with return type (CodeIgniter\\HTTP\\ResponseInterface|null) of method CodeIgniter\\Filters\\FilterInterface::after()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/JWTAuth.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (CodeIgniter\\HTTP\\RedirectResponse|void) of method CodeIgniter\\Shield\\Filters\\SessionAuth::before() should be covariant with return type (CodeIgniter\\HTTP\\RequestInterface|CodeIgniter\\HTTP\\ResponseInterface|string|null) of method CodeIgniter\\Filters\\FilterInterface::before()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/SessionAuth.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (void) of method CodeIgniter\\Shield\\Filters\\SessionAuth::after() should be compatible with return type (CodeIgniter\\HTTP\\ResponseInterface|null) of method CodeIgniter\\Filters\\FilterInterface::after()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/SessionAuth.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Method CodeIgniter\\Shield\\Filters\\TokenAuth::before() should return CodeIgniter\\HTTP\\RedirectResponse|void but returns CodeIgniter\\HTTP\\ResponseInterface.',
	'identifier' => 'return.type',
	'count' => 2,
	'path' => __DIR__ . '/src/Filters/TokenAuth.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (CodeIgniter\\HTTP\\RedirectResponse|void) of method CodeIgniter\\Shield\\Filters\\TokenAuth::before() should be covariant with return type (CodeIgniter\\HTTP\\RequestInterface|CodeIgniter\\HTTP\\ResponseInterface|string|null) of method CodeIgniter\\Filters\\FilterInterface::before()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/TokenAuth.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (void) of method CodeIgniter\\Shield\\Filters\\TokenAuth::after() should be compatible with return type (CodeIgniter\\HTTP\\ResponseInterface|null) of method CodeIgniter\\Filters\\FilterInterface::after()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/TokenAuth.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Parameter #1 $row (array|CodeIgniter\\Shield\\Entities\\User) of method CodeIgniter\\Shield\\Models\\UserModel::insert() should be contravariant with parameter $row (array<mixed>|object|null) of method CodeIgniter\\Model::insert()',
	'identifier' => 'method.childParameterType',
	'count' => 1,
	'path' => __DIR__ . '/src/Models/UserModel.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Parameter #1 $row (array|CodeIgniter\\Shield\\Entities\\User) of method CodeIgniter\\Shield\\Models\\UserModel::save() should be contravariant with parameter $row (array<mixed>|object) of method CodeIgniter\\BaseModel::save()',
	'identifier' => 'method.childParameterType',
	'count' => 1,
	'path' => __DIR__ . '/src/Models/UserModel.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Parameter #2 $row (array|CodeIgniter\\Shield\\Entities\\User) of method CodeIgniter\\Shield\\Models\\UserModel::update() should be contravariant with parameter $row (array<mixed>|object|null) of method CodeIgniter\\Model::update()',
	'identifier' => 'method.childParameterType',
	'count' => 1,
	'path' => __DIR__ . '/src/Models/UserModel.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Return type (int|string|true) of method CodeIgniter\\Shield\\Models\\UserModel::insert() should be covariant with return type (($returnID is true ? int|string|false : bool)) of method CodeIgniter\\Model::insert()',
	'identifier' => 'method.childReturnType',
	'count' => 1,
	'path' => __DIR__ . '/src/Models/UserModel.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Call to deprecated method __construct() of class CodeIgniter\\HTTP\\Response:
4.5.0 The param $config is no longer used.',
	'identifier' => 'method.deprecated',
	'count' => 3,
	'path' => __DIR__ . '/tests/Unit/PwnedValidatorTest.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Call to an undefined method CodeIgniter\\Shield\\Models\\UserModel::getLastQuery().',
	'identifier' => 'method.notFound',
	'count' => 9,
	'path' => __DIR__ . '/tests/Unit/UserTest.php',
];
$ignoreErrors[] = [
	'rawMessage' => 'Offset 1 does not exist on array{CodeIgniter\\Shield\\Entities\\User}.',
	'identifier' => 'offsetAccess.notFound',
	'count' => 5,
	'path' => __DIR__ . '/tests/Unit/UserTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
