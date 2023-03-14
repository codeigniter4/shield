<@php

declare(strict_types=1);

namespace {namespace};

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class {class} extends ShieldUserModel
{
    protected function initialize(): void
    {
        parent::initialize();

        $this->allowedFields = [
            ...$this->allowedFields,

            // 'first_name',
        ];
    }
}
