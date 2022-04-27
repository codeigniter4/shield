<?php

namespace Tests\Support;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

trait FakeUser
{
    /**
     * @var User
     */
    private $user;

    protected function setUpFakeUser()
    {
        $this->user = fake(UserModel::class);
    }
}
