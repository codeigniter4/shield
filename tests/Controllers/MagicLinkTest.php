<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Controllers;

use CodeIgniter\Config\Factories;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Actions\EmailActivator;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Controllers\MagicLinkController;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\InvalidArgumentException;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use ReflectionMethod;
use Tests\Support\FakeUser;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class MagicLinkTest extends TestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use FakeUser;

    protected $namespace;

    protected function setUp(): void
    {
        parent::setUp();

        // Add auth routes
        $routes = service('routes');
        auth()->routes($routes);
        Services::injectMock('routes', $routes);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up any robot user agent set in tests
        unset($_SERVER['HTTP_USER_AGENT']);
    }

    public function testAfterLoggedInNotAllowDisplayMagicLink(): void
    {
        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->post('/login', [
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->get('/login/magic-link');
        $result->assertRedirectTo(config('Auth')->loginRedirect());
    }

    public function testShowValidateErrorsInMagicLink(): void
    {
        $result = $this->post('/login/magic-link', [
            'email' => 'foo@example',
        ]);

        $expected = ['email' => 'The Email Address field must contain a valid email address.'];

        $result->assertSessionHas('errors', $expected);
    }

    /**
     * @see https://github.com/codeigniter4/shield/issues/465
     */
    public function testMagicLinkVerifyPendingRegistrationActivation(): void
    {
        // Enable Register action (Email Activation)
        $config                      = config('Auth');
        $config->actions['register'] = EmailActivator::class;
        Factories::injectMock('config', 'Auth', $config);

        /** @var User $user */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);

        $identities = model(UserIdentityModel::class);

        // Insert User Identity for Email Activation
        $identities->insert([
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_EMAIL_ACTIVATE,
            'secret'  => '123456',
            'name'    => 'register',
            'extra'   => lang('Auth.needVerification'),
        ]);
        // Insert User Identity for Magic link login
        $identities->insert([
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_MAGIC_LINK,
            'secret'  => 'abasdasdf',
            'expires' => Time::now()->addMinutes(60),
        ]);

        $result = $this->get(route_to('verify-magic-link') . '?token=abasdasdf');

        $result->assertRedirectTo(route_to('auth-action-show'));
        $result->assertSessionHas(
            'error',
            lang('Auth.needActivate'),
        );
        $result->assertSessionHas(
            'user',
            [
                'id'                  => $user->id,
                'auth_action'         => 'CodeIgniter\Shield\Authentication\Actions\EmailActivator',
                'auth_action_message' => lang('Auth.needVerification'),
            ],
        );
        $this->assertFalse(auth()->loggedIn());
    }

    public function testBackToLoginLinkOnPage(): void
    {
        $result = $this->get('/login/magic-link');
        $this->assertStringContainsString(lang('Auth.backToLogin'), $result->getBody());
    }

    public function testMagicLinkRedirectsIfNotAllowed(): void
    {
        $config                       = config('Auth');
        $config->allowMagicLinkLogins = false;
        Factories::injectMock('config', 'Auth', $config);

        $result = $this->withSession()->get('/login/magic-link');

        $result->assertStatus(302);
        $result->assertRedirect();
        $result->assertSessionHas(
            'error',
            lang('Auth.magicLinkDisabled'),
        );
    }

    public function testMagicLinkActionRedirectsIfNotAllowed(): void
    {
        $config                       = config('Auth');
        $config->allowMagicLinkLogins = false;
        Factories::injectMock('config', 'Auth', $config);

        $result = $this->withSession()->post('/login/magic-link');

        $result->assertStatus(302);
        $result->assertRedirect();
        $result->assertSessionHas(
            'error',
            lang('Auth.magicLinkDisabled'),
        );
    }

    public function testMagicLinkVerifyRedirectsIfNotAllowed(): void
    {
        $config                       = config('Auth');
        $config->allowMagicLinkLogins = false;
        Factories::injectMock('config', 'Auth', $config);

        $result = $this->withSession()->get('/login/verify-magic-link');

        $result->assertStatus(302);
        $result->assertRedirect();
        $result->assertSessionHas(
            'error',
            lang('Auth.magicLinkDisabled'),
        );
    }

    public function testMagicLinkVerifyReturns404ForRobotUserAgent(): void
    {
        $this->expectException(PageNotFoundException::class);

        /** @var User $user */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);

        $identities = model(UserIdentityModel::class);

        // Insert User Identity for Magic link login
        $identities->insert([
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_MAGIC_LINK,
            'secret'  => 'validtoken123',
            'expires' => Time::now()->addMinutes(60),
        ]);

        // Simulate a robot user agent
        service('superglobals')->setServer('HTTP_USER_AGENT', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');

        $this->get(route_to('verify-magic-link') . '?token=validtoken123');
    }

    public function testMagicCodeShowLoginForm(): void
    {
        $config                 = config('Auth');
        $config->magicLoginMode = '6-numeric';
        Factories::injectMock('config', 'Auth', $config);

        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->post('/login/magic-link', [
            'email' => 'foo@example.com',
        ]);

        // must contain a code input form
        $result->seeElement('#magicCode');
        $result->assertStatus(200);
    }

    public function testMagicCodeShowsOneOFLoginForm(): void
    {
        $config                 = config('Auth');
        $config->magicLoginMode = '15-oneof';
        Factories::injectMock('config', 'Auth', $config);

        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->post('/login/magic-link', [
            'email' => 'foo@example.com',
        ]);

        $result->assertSee('15-character code');
        // must contain a code input form
        $result->seeElement('#magicCode');
        $result->assertStatus(200);
    }

    public function testMagicCodeEmailContainsSixDigitCode(): void
    {
        $config                 = config('Auth');
        $config->magicLoginMode = '6-numeric';
        Factories::injectMock('config', 'Auth', $config);

        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $this->post('/login/magic-link', [
            'email' => 'foo@example.com',
        ]);

        $email = service('email')->archive['body'];

        // Should have sent an email with the link....
        $this->assertStringContainsString(
            lang('Auth.email2FAMailBody'),
            (string) $email,
        );

        $this->assertMatchesRegularExpression(
            '!<h1>[0-9]{6}</h1>!',
            $email,
        );
    }

    public function testValidMagicCodeLogsUserIn(): void
    {
        $config                 = config('Auth');
        $config->magicLoginMode = '6-numeric';
        Factories::injectMock('config', 'Auth', $config);

        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $this->post('/login/magic-link', [
            'email' => 'foo@example.com',
        ]);

        // Extract sent email body & OTP code
        $email = service('email')->archive['body'];
        preg_match('/\d{6}/', (string) $email, $match);
        $code   = $match[0];
        $result = $this->post('/login/verify-magic-link', [
            'magicCode' => $code,
        ]);

        $result->assertStatus(302);
        $result->assertRedirectTo(config('Auth')->loginRedirect());
        $this->assertTrue(auth()->loggedIn());
    }

    public function testInvalidMagicCodeShowsError(): void
    {
        $config                 = config('Auth');
        $config->magicLoginMode = '6-numeric';
        Factories::injectMock('config', 'Auth', $config);

        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $this->post('/login/magic-link', [
            'email' => 'foo@example.com',
        ]);

        $result = $this->post('/login/verify-magic-link', [
            'magicCode' => '000000', // surely invalid
        ]);

        $result->assertStatus(302);
        $result->assertRedirectTo('/login/magic-link');
        $result->assertSessionHas('error', lang('Auth.magicTokenNotFound'));

        $this->assertFalse(auth()->loggedIn());
    }

    public function testClickableMode(): void
    {
        $result = $this->callPrivateMethod('clickable');

        $this->assertSame('magic-link-message', $result['displayMessageView']);
        $this->assertSame('magic-link-email', $result['emailView']);
        $this->assertSame(lang('Auth.magicLinkSubject'), $result['emailSubject']);
        $this->assertSame(20, strlen((string) $result['token']));
    }

    public function testNumericMode(): void
    {
        $config                 = config('Auth');
        $config->magicLoginMode = '6-numeric';
        Factories::injectMock('config', 'Auth', $config);

        $result = $this->callPrivateMethod();

        $this->assertSame('magic-link-code', $result['displayMessageView']);
        $this->assertSame('magic-link-email-code', $result['emailView']);
        $this->assertSame(6, strlen((string) $result['token']));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $result['token']);
    }

    public function testAlnumMode(): void
    {
        $config                 = config('Auth');
        $config->magicLoginMode = '8-alnum';
        Factories::injectMock('config', 'Auth', $config);

        $result = $this->callPrivateMethod();

        $this->assertSame(8, strlen((string) $result['token']));
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{8}$/', $result['token']);
    }

    public function testOneofMode(): void
    {
        $result = $this->callPrivateMethod('4-oneof');

        $this->assertSame('magic-link-code', $result['displayMessageView']);
        $this->assertSame('magic-link-email-code', $result['emailView']);
        $this->assertSame(lang('Auth.magicCodeSubject'), $result['emailSubject']);

        $token = $result['token'];

        $this->assertSame(4, strlen((string) $token));

        $tokens      = [];
        $uniqueCount = 0;

        for ($i = 0; $i < 10; $i++) {
            $newToken = $this->callPrivateMethod('4-oneof')['token'];
            $tokens[] = $newToken;

            if ($newToken !== $token) {
                $uniqueCount++;
            }
        }

        $this->assertGreaterThan(
            0,
            $uniqueCount,
            'Tokens generated in loop should not all be identical.',
        );
    }

    public function testInvalidFormatThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivateMethod('INVALID_FORMAT');
    }

    public function testInvalidLengthThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivateMethod('x-numeric');
    }

    public function testZeroLengthThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivateMethod('0-numeric');
    }

    public function testInvalidTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivateMethod('5-invalid');
    }

    private function callPrivateMethod(?string $mode = null): array
    {
        $controller = new MagicLinkController();

        $method = new ReflectionMethod($controller, 'resolveMode');

        return $method->invoke($controller, $mode);
    }
}
