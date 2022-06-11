<?php

namespace Tests\Authentication\Authenticators;

use CodeIgniter\Shield\Authentication\Authentication;
use CodeIgniter\Shield\Authentication\AuthenticationException;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\LogicException;
use CodeIgniter\Shield\Models\RememberModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Result;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\Mock\MockEvents;
use Config\Services;
use Tests\Support\FakeUser;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class SessionAuthenticatorTest extends TestCase
{
    use DatabaseTestTrait;
    use FakeUser;

    protected Session $auth;
    protected $namespace;
    protected $events;

    protected function setUp(): void
    {
        parent::setUp();

        $config = new Auth();
        $auth   = new Authentication($config);
        $auth->setProvider(model(UserModel::class));

        /** @var Session $authenticator */
        $authenticator = $auth->factory('session');
        $this->auth    = $authenticator;

        $this->events = new MockEvents();
        Services::injectMock('events', $this->events);

        $this->db->table('auth_identities')->truncate();
    }

    public function testLoggedInFalse(): void
    {
        $this->assertFalse($this->auth->loggedIn());
    }

    public function testLoggedInTrue(): void
    {
        $_SESSION['user']['id'] = $this->user->id;

        $this->assertTrue($this->auth->loggedIn());

        $authUser = $this->auth->getUser();
        $this->assertInstanceOf(User::class, $authUser);
        $this->assertSame($this->user->id, $authUser->id);
    }

    public function testLoggedInAsDeletedUserWithSessionData(): void
    {
        $_SESSION['user']['id'] = $this->user->id;

        // Delete the user.
        $users = model(UserModel::class);
        $users->delete($this->user->id);

        $loggedIn = $this->auth->loggedIn();

        $this->assertFalse($loggedIn);
        $this->assertNull(session()->get('user'));
    }

    public function testLoggedInWithRememberCookie(): void
    {
        unset($_SESSION['user']);
        // Set Cookie.prefix
        $cookiePrefix = 'prefix_';
        setting('Cookie.prefix', $cookiePrefix);

        $this->user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret']);

        // Insert remember-me token.
        /** @var RememberModel $rememberModel */
        $rememberModel = model(RememberModel::class);
        $selector      = 'selector';
        $validator     = 'validator';
        $expires       = date('Y-m-d H:i:s', time() + setting('Auth.sessionConfig')['rememberLength']);
        $rememberModel->rememberUser($this->user, $selector, hash('sha256', $validator), $expires);

        // Set Cookie value for remember-me.
        $token                = $selector . ':' . $validator;
        $cookieName           = $cookiePrefix . setting('Auth.sessionConfig')['rememberCookieName'];
        $_COOKIE[$cookieName] = $token;

        $this->assertTrue($this->auth->loggedIn());

        $authUser = $this->auth->getUser();

        $this->assertInstanceOf(User::class, $authUser);
        $this->assertSame($this->user->id, $authUser->id);

        // Forget Cookie.prefix
        setting()->forget('Cookie.prefix');
    }

    public function testLoggedInAsDeletedUserWithRememberCookie(): void
    {
        unset($_SESSION['user']);

        $this->user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret']);

        // Insert remember-me token.
        /** @var RememberModel $rememberModel */
        $rememberModel = model(RememberModel::class);
        $selector      = 'selector';
        $validator     = 'validator';
        $expires       = date('Y-m-d H:i:s', time() + setting('Auth.sessionConfig')['rememberLength']);
        $rememberModel->rememberUser($this->user, $selector, hash('sha256', $validator), $expires);

        // Set Cookie value for remember-me.
        $token               = $selector . ':' . $validator;
        $_COOKIE['remember'] = $token;

        // Delete the user.
        $users = model(UserModel::class);
        $users->delete($this->user->id);

        $loggedIn = $this->auth->loggedIn();

        $this->assertFalse($loggedIn);
    }

    public function testLoginNoRemember(): void
    {
        $this->user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret']);

        $this->auth->login($this->user);

        $this->assertSame($this->user->id, $_SESSION['user']['id']);

        $this->dontSeeInDatabase('auth_remember_tokens', [
            'user_id' => $this->user->id,
        ]);
    }

    public function testLoginWithRemember(): void
    {
        $this->user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret']);

        $this->auth->remember()->login($this->user);

        $this->assertSame($this->user->id, $_SESSION['user']['id']);

        $this->seeInDatabase('auth_remember_tokens', [
            'user_id' => $this->user->id,
        ]);

        // Cookie should have been set
        $response = service('response');
        $this->assertNotNull($response->getCookie('remember'));
    }

    public function testLogout(): void
    {
        $this->user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret']);
        $this->auth->remember()->login($this->user);

        $this->seeInDatabase('auth_remember_tokens', ['user_id' => $this->user->id]);

        $this->auth->logout();

        $this->assertArrayNotHasKey('user', $_SESSION);
        $this->dontSeeInDatabase('auth_remember_tokens', ['user_id' => $this->user->id]);
    }

    public function testLoginByIdBadUser(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage(lang('Auth.invalidUser'));

        $this->auth->loginById(123);
    }

    public function testLoginById(): void
    {
        $this->user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret']);

        $this->auth->loginById($this->user->id);

        $this->assertSame($this->user->id, $_SESSION['user']['id']);

        $this->dontSeeInDatabase('auth_remember_tokens', ['user_id' => $this->user->id]);
    }

    public function testLoginByIdRemember(): void
    {
        $this->user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret']);

        $this->auth->remember()->loginById($this->user->id);

        $this->assertSame($this->user->id, $_SESSION['user']['id']);

        $this->seeInDatabase('auth_remember_tokens', ['user_id' => $this->user->id]);
    }

    public function testForgetCurrentUser(): void
    {
        $this->user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret']);
        $this->auth->remember()->loginById($this->user->id);
        $this->assertSame($this->user->id, $_SESSION['user']['id']);

        $this->seeInDatabase('auth_remember_tokens', ['user_id' => $this->user->id]);

        $this->auth->forget();

        $this->dontSeeInDatabase('auth_remember_tokens', ['user_id' => $this->user->id]);
    }

    public function testForgetAnotherUser(): void
    {
        fake(RememberModel::class, ['user_id' => $this->user->id]);

        $this->seeInDatabase('auth_remember_tokens', ['user_id' => $this->user->id]);

        $this->auth->forget($this->user);

        $this->dontSeeInDatabase('auth_remember_tokens', ['user_id' => $this->user->id]);
    }

    public function testCheckNoPassword(): void
    {
        $result = $this->auth->check([
            'email' => 'johnsmith@example.com',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.badAttempt'), $result->reason());
    }

    public function testCheckCannotFindUser(): void
    {
        $result = $this->auth->check([
            'email'    => 'johnsmith@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.badAttempt'), $result->reason());
    }

    public function testCheckBadPassword(): void
    {
        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->auth->check([
            'email'    => $this->user->email,
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.invalidPassword'), $result->reason());
    }

    public function testCheckSuccess(): void
    {
        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->auth->check([
            'email'    => $this->user->email,
            'password' => 'secret123',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOK());

        $foundUser = $result->extraInfo();
        $this->assertSame($this->user->id, $foundUser->id);
    }

    public function testAttemptCannotFindUser(): void
    {
        $result = $this->auth->attempt([
            'email'    => 'johnsmith@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.badAttempt'), $result->reason());

        // A login attempt should have always been recorded
        $this->seeInDatabase('auth_logins', [
            'identifier' => 'johnsmith@example.com',
            'success'    => 0,
        ]);
    }

    public function testAttemptSuccess(): void
    {
        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $this->assertArrayNotHasKey('user', $_SESSION);

        $result = $this->auth->attempt([
            'email'    => $this->user->email,
            'password' => 'secret123',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOK());

        $foundUser = $result->extraInfo();
        $this->assertSame($this->user->id, $foundUser->id);

        $this->assertArrayHasKey('id', $_SESSION['user']);
        $this->assertSame($this->user->id, $_SESSION['user']['id']);

        // A login attempt should have been recorded
        $this->seeInDatabase('auth_logins', [
            'identifier' => $this->user->email,
            'success'    => 1,
        ]);
    }

    public function testAttemptUserHavingSessionDataAttemptsAgain(): void
    {
        $_SESSION['user']['id'] = '999';

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'The user has User Info in Session, so already logged in or in pending login state.'
        );

        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $this->auth->attempt([
            'email'    => $this->user->email,
            'password' => 'secret123',
        ]);
    }

    public function testAttemptCaseInsensitive(): void
    {
        $this->user->createEmailIdentity([
            'email'    => 'FOO@example.com',
            'password' => 'secret123',
        ]);

        $this->assertArrayNotHasKey('user', $_SESSION);

        $result = $this->auth->attempt([
            'email'    => 'foo@example.COM',
            'password' => 'secret123',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOK());

        $foundUser = $result->extraInfo();
        $this->assertSame($this->user->id, $foundUser->id);

        $this->assertArrayHasKey('id', $_SESSION['user']);
        $this->assertSame($this->user->id, $_SESSION['user']['id']);

        // A login attempt should have been recorded
        $this->seeInDatabase('auth_logins', [
            'identifier' => 'foo@example.COM',
            'success'    => 1,
        ]);
    }

    public function testAttemptUsernameOnly(): void
    {
        /** @var User $user */
        $user = fake(UserModel::class, ['username' => 'foorog']);
        $user->createEmailIdentity([
            'email'    => 'FOO@example.com',
            'password' => 'secret123',
        ]);

        $this->assertArrayNotHasKey('user', $_SESSION);

        $result = $this->auth->attempt([
            'username' => 'fooROG',
            'password' => 'secret123',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOK());

        $foundUser = $result->extraInfo();
        $this->assertSame($user->id, $foundUser->id);

        $this->assertArrayHasKey('id', $_SESSION['user']);
        $this->assertSame($user->id, $_SESSION['user']['id']);

        // A login attempt should have been recorded
        $this->seeInDatabase('auth_logins', [
            'identifier' => 'fooROG',
            'success'    => 1,
        ]);
    }
}
