<?php

declare(strict_types=1);

function replace_file_content(string $path, string $pattern, string $replace): void
{
    $file   = file_get_contents($path);
    $output = preg_replace($pattern, $replace, $file);
    file_put_contents($path, $output);
}

// Main.
chdir(__DIR__ . '/..');

if ($argc !== 2) {
    echo "Usage: php {$argv[0]} <version>" . PHP_EOL;
    echo "E.g.,: php {$argv[0]} 1.0.1" . PHP_EOL;

    exit(1);
}

// Gets version number from argument.
$version      = $argv[1]; // e.g., '4.4.3'
$versionParts = explode('.', $version);
$minor        = $versionParts[0] . '.' . $versionParts[1];

// Creates a branch for release.
system('git switch develop');
system('git switch -c release-' . $version);

// Updates version number in "src/Auth.php".
replace_file_content(
    './src/Auth.php',
    '/const SHIELD_VERSION = \'.*?\';/u',
    "const SHIELD_VERSION = '{$version}';"
);

// Commits
system('git add -u');
system('git commit -m "Prep for ' . $version . ' release"');
