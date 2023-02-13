<?php

declare(strict_types=1);

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

    protected function initialize(): void
    {
        /** @var Auth $authConfig */
        $authConfig = config('Auth');

        $this->tables = $authConfig->tables;
    }
}
