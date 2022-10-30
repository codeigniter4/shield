<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\Shield\Filters\AuthRates;
use CodeIgniter\Shield\Filters\ChainAuth;
use CodeIgniter\Shield\Filters\SessionAuth;
use CodeIgniter\Shield\Filters\TokenAuth;
use Generator;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class FilterInCliTest extends TestCase
{
    /**
     * @dataProvider filterProvider
     */
    public function testWhenInCliDoNothing(FilterInterface $filter): void
    {
        $clirequest = $this->createMock(CLIRequest::class);

        $clirequest->expects($this->never())
            ->method('getHeaderLine');

        $filter->before($clirequest);
    }

    public function filterProvider(): Generator
    {
        yield from [
            [new AuthRates()],
            [new ChainAuth()],
            [new SessionAuth()],
            [new TokenAuth()],
        ];
    }
}
