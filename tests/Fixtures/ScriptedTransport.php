<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Tests\Fixtures;

use LogicException;
use NSolutions\Filmweb\Exception\TransportException;
use NSolutions\Filmweb\Http\Response;
use NSolutions\Filmweb\Http\Transport;

/**
 * Returns (or throws) the queued outcomes one by one, regardless of the URL.
 */
final class ScriptedTransport implements Transport
{
    public int $calls = 0;

    /**
     * @param list<Response|TransportException> $outcomes
     */
    public function __construct(private array $outcomes) {}

    public function get(string $url, array $headers = []): Response
    {
        ++$this->calls;
        $outcome = array_shift($this->outcomes) ?? throw new LogicException('No more scripted responses.');

        return $outcome instanceof Response ? $outcome : throw $outcome;
    }
}
