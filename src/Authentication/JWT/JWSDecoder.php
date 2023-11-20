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

namespace CodeIgniter\Shield\Authentication\JWT;

use CodeIgniter\Shield\Authentication\JWT\Adapters\FirebaseAdapter;
use stdClass;

class JWSDecoder
{
    private JWSAdapterInterface $jwsAdapter;

    /**
     * @var string The key group. The array key of Config\AuthJWT::$keys.
     */
    protected $keyset = 'default';

    public function __construct(?JWSAdapterInterface $jwsAdapter = null)
    {
        $this->jwsAdapter = $jwsAdapter ?? new FirebaseAdapter();
    }

    /**
     * Returns payload of the JWT
     *
     * @param string $keyset The key group. The array key of Config\AuthJWT::$keys.
     */
    public function decode(string $encodedToken, $keyset = 'default'): stdClass
    {
        return $this->jwsAdapter->decode($encodedToken, $keyset);
    }
}
