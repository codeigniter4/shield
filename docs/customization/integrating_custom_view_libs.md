# Integrating Custom View Libraries

If your application uses a different method to convert view files to HTML than CodeIgniter's built-in `view()` helper, you can easily integrate your system anywhere that a view is rendered within Shield.

All controllers and actions use the `CodeIgniter\Shield\Traits\Viewable` trait which provides a simple `view()` method that takes the same arguments as the `view()` helper. This allows you to extend the Action or Controller and only change the single method of rendering the view, leaving all of the logic untouched so your app will not need to maintain Shield logic when it doesn't need to change it.

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use Acme\Themes\Traits\Themeable;
use CodeIgniter\Shield\Controllers\LoginController;

class MyLoginController extends LoginController
{
    use Themable;

    protected function view(string $view, array $data = [], array $options = []): string
    {
        return $this->themedView($view, $data, $options);
    }
}
```
