<?php

namespace CodeIgniter\Shield\Entities;

use CodeIgniter\Entity\Entity;

class Login extends Entity
{
    protected $casts = [
        'date'    => 'datetime',
        'success' => 'boolean',
    ];
}
