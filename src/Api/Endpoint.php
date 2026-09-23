<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api;

use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Support\ImageUrls;

/**
 * A single GET endpoint of the Filmweb API: knows its path and how to map the JSON response.
 *
 * Implement it to support endpoints not covered by the library and run them
 * with {@see \NSolutions\Filmweb\Filmweb::call()}.
 *
 * @template-covariant TResult
 */
interface Endpoint
{
    /**
     * Path relative to the API base URL, e.g. `/film/628/rating`.
     */
    public function path(): string;

    /**
     * @return array<string, string>
     */
    public function query(): array;

    /**
     * @return TResult
     */
    public function map(Data $data, ImageUrls $images): mixed;
}
