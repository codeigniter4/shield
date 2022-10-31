<@php

declare(strict_types=1);

namespace {namespace};

use CodeIgniter\Shield\Models\UserModel;

class {class} extends UserModel
{
    protected function initialize(): void
    {
        $this->allowedFields = [
            ...$this->allowedFields,
            // Add here your custom fields
            // 'first_name',
        ];
    }
}

