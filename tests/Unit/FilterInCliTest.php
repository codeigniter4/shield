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

namespace Tests\Unit;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\Shield\Filters\AuthRates;
use CodeIgniter\Shield\Filters\ChainAuth;
use CodeIgniter\Shield\Filters\SessionAuth;
use CodeIgniter\Shield\Filters\TokenAuth;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class FilterInCliTest extends TestCase
{
    /**
     * @dataProvider provideWhenInCliDoNothing
     */
    public function testWhenInCliDoNothing(FilterInterface $filter): void
    {
        $clirequest = $this->createMock(CLIRequest::class);

        $clirequest->expects($this->never())
            ->method('getHeaderLine');

        $filter->before($clirequest);
    }

    public static function provideWhenInCliDoNothing(): iterable
    {
        yield from [
            [new AuthRates()],
            [new ChainAuth()],
            [new SessionAuth()],
            [new TokenAuth()],
        ];
    }
}
