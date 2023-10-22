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

namespace CodeIgniter\Shield\Authentication\Passwords;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Shield\Authentication\Passwords;
use CodeIgniter\Shield\Entities\User;

/**
 * Class ValidationRules
 *
 * Provides auth-related validation rules for CodeIgniter 4.
 *
 * To use, add this class to Config/Validation.php, in the
 * $rulesets array.
 */
class ValidationRules
{
    /**
     * A validation helper method to check if the passed in
     * password will pass all of the validators currently defined.
     *
     * Handy for use in validation, but you will get a slightly
     * better security if this is done manually, since you can
     * personalize based on a specific user at that point.
     *
     * @param string $value  Field value
     * @param string $error1 Error that will be returned (for call without validation data array)
     * @param array  $data   Validation data array
     * @param string $error2 Error that will be returned (for call with validation data array)
     */
    public function strong_password(string $value, ?string &$error1 = null, array $data = [], ?string &$error2 = null): bool
    {
        /** @var Passwords $checker */
        $checker = service('passwords');

        if (function_exists('auth') && auth()->user()) {
            $user = auth()->user();
        } else {
            /** @phpstan-ignore-next-line */
            $user = empty($data) ? $this->buildUserFromRequest() : $this->buildUserFromData($data);
        }

        $result = $checker->check($value, $user);

        if (! $result->isOk()) {
            if (empty($data)) {
                $error1 = $result->reason();
            } else {
                $error2 = $result->reason();
            }
        }

        return $result->isOk();
    }

    /**
     * Returns true if $str is $val or fewer bytes in length.
     */
    public function max_byte(?string $str, string $val): bool
    {
        return is_numeric($val) && $val >= strlen($str ?? '');
    }

    /**
     * Builds a new user instance from the global request.
     *
     * @deprecated This will be removed soon.
     *
     * @see https://github.com/codeigniter4/shield/pull/747#discussion_r1198778666
     */
    protected function buildUserFromRequest(): User
    {
        $fields = $this->prepareValidFields();

        /** @var IncomingRequest $request */
        $request = service('request');

        $data = $request->getPost($fields);

        return new User($data);
    }

    /**
     * Builds a new user instance from assigned data..
     *
     * @param array $data Assigned data
     */
    protected function buildUserFromData(array $data = []): User
    {
        $fields = $this->prepareValidFields();

        $data = array_intersect_key($data, array_fill_keys($fields, null));

        return new User($data);
    }

    /**
     * Prepare valid user fields
     */
    protected function prepareValidFields(): array
    {
        $config = config('Auth');
        $fields = array_merge($config->validFields, $config->personalFields, ['email', 'password']);

        return array_unique($fields);
    }
}
