<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Entities;

class Login extends Entity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date'    => 'datetime',
        'success' => 'int_bool',
    ];
}
