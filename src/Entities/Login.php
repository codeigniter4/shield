<?php

namespace CodeIgniter\Shield\Entities;

use CodeIgniter\Entity\Entity;

class Login extends Entity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date'    => 'datetime',
        'success' => 'boolean',
    ];
}
