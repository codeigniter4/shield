<?php

namespace Tests\Support;

use Sparks\Shield\Entities\User;
use Sparks\Shield\Models\UserModel;

trait FakeUser
{
    private User $user;

    protected function setUpFakeUser(): void
    {
        $this->user = fake(UserModel::class);
    }
}
