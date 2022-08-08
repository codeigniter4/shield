<?php

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
