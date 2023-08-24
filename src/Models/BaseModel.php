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
