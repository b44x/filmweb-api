<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint;

use NSolutions\Filmweb\Api\Endpoint;

/**
 * Base for `GET /film/{id}/{resource}` endpoints.
 *
 * @template TResult
 *
 * @implements Endpoint<TResult>
 */
abstract readonly class FilmEndpoint implements Endpoint
{
    final public function __construct(protected int $filmId) {}

    /**
     * Resource name after the film ID, e.g. `rating` or `critics/rating`.
     */
    abstract protected function resource(): string;

    final public function path(): string
    {
        return "/film/{$this->filmId}/{$this->resource()}";
    }

    public function query(): array
    {
        return [];
    }
}
