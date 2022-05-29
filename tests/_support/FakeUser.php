<?php

namespace Tests\Support;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

trait FakeUser
{
    private User $user;

    protected function setUpFakeUser(): void
    {
        $this->user = fake(UserModel::class);
    }
}
