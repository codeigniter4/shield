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

namespace CodeIgniter\Shield\Tests\Authentication\Filters;

use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Filters\AuthRates;
use CodeIgniter\Shield\Tests\TestCase;
use CodeIgniter\Throttle\Throttler;
use Config\Services;

/**
 * @internal
 */
final class AuthRatesTest extends TestCase
{
    public function testAllowsRequestWithinLimit(): void
    {
        $request = $this->createMock(IncomingRequest::class);
        $request->method('getIPAddress')->willReturn('203.0.113.7');

        $throttler = $this->createMock(Throttler::class);
        $throttler->expects($this->once())
            ->method('check')
            ->with(md5('203.0.113.7'), 10, MINUTE, 1)
            ->willReturn(true);
        Services::injectMock('throttler', $throttler);

        $this->assertNull((new AuthRates())->before($request));
    }

    public function testRejectsRequestOverLimit(): void
    {
        $request = $this->createMock(IncomingRequest::class);
        $request->method('getIPAddress')->willReturn('203.0.113.7');

        $throttler = $this->createMock(Throttler::class);
        $throttler->method('check')->willReturn(false);
        $throttler->method('getTokenTime')->willReturn(42);
        Services::injectMock('throttler', $throttler);

        $response = (new AuthRates())->before($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(429, $response->getStatusCode());
        $this->assertSame(lang('Auth.throttled', [42]), $response->getReasonPhrase());
    }

    public function testIgnoresRequestThatIsNotIncomingRequest(): void
    {
        $throttler = $this->createMock(Throttler::class);
        $throttler->expects($this->never())->method('check');
        Services::injectMock('throttler', $throttler);

        $this->assertNull((new AuthRates())->before($this->createStub(CLIRequest::class)));
    }
}
