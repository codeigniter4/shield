<?php declare(strict_types = 1);

$ignoreErrors = [];
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
	'message' => '#^Method CodeIgniter\\\\Shield\\\\Filters\\\\AuthRates\\:\\:before\\(\\) should return CodeIgniter\\\\HTTP\\\\RedirectResponse\\|void but returns CodeIgniter\\\\HTTP\\\\ResponseInterface\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Filters/AuthRates.php',
];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\Shield\\\\Filters\\\\TokenAuth\\:\\:before\\(\\) should return CodeIgniter\\\\HTTP\\\\RedirectResponse\\|void but returns CodeIgniter\\\\HTTP\\\\ResponseInterface\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Filters/TokenAuth.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
