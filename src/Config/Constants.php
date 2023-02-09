<?php

declare(strict_types=1);

use CodeIgniter\Shield\Config\Auth as ShieldAuth;

define('SHIELD_TABLES', (new ShieldAuth())->tables);
