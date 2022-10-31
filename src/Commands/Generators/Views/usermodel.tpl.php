<@php

declare(strict_types=1);

namespace {namespace};

use CodeIgniter\Shield\Models\UserModel;

class {class} extends UserModel
{
    protected function initialize(): void
    {
        // Merge properties with parent
        $this->allowedFields = array_merge($this->allowedFields, [
            // Add here your custom fields
            // 'first_name',
        ]);
    }
}

