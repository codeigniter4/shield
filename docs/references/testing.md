# Testing

## HTTP Feature Testing

When performing [HTTP Feature Testing](https://codeigniter.com/user_guide/testing/feature.html) in your applications, you
will often need to ensure you are logged in to check security, or simply to access protected locations. Shield
provides the `AuthenticationTesting` trait to help you out. Use it within the test class and then you can use
the `actingAs()` method that takes a User instance. This user will be logged in during the test.

```php
<?php

use CodeIgniter\Shield\Test\AuthenticationTesting;
use Tests\Support\TestCase;
use CodeIgniter\Shield\Authentication\Actions\Email2FA;

class ActionsTest extends TestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AuthenticationTesting;

    public function testEmail2FAShow()
    {
        $result = $this->actingAs($this->user)
           ->withSession([
               'auth_action' => Email2FA::class,
           ])->get('/auth/a/show');

        $result->assertStatus(200);
        // Should auto-populate in the form
        $result->assertSee($this->user->email);
    }
}
```
