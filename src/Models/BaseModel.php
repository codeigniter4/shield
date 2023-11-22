<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Shield\Models;

use CodeIgniter\Model;
use CodeIgniter\Shield\Config\Auth;

abstract class BaseModel extends Model
{
    use CheckQueryReturnTrait;

    /**
     * Auth Table names
     */
    protected array $tables;

    protected Auth $authConfig;

    public function __construct()
    {
        $this->authConfig = config('Auth');

        if ($this->authConfig->DBGroup !== null) {
            $this->DBGroup = $this->authConfig->DBGroup;
        }

        parent::__construct();
    }

    protected function initialize(): void
    {
        $this->tables = $this->authConfig->tables;
    }
}
