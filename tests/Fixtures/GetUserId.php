<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Tests\Fixtures;

use NSolutions\Filmweb\Api\Endpoint;
use NSolutions\Filmweb\Support\Data;

/**
 * Example of a user-defined endpoint (the one documented in README).
 *
 * @implements Endpoint<int>
 */
final readonly class GetUserId implements Endpoint
{
    public function __construct(private string $username) {}

    public function path(): string
    {
        return '/users/' . rawurlencode($this->username) . '/id';
    }

    public function query(): array
    {
        return [];
    }

    public function map(Data $data): int
    {
        return $data->int('userId');
    }
}
