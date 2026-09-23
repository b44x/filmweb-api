<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api;

use NSolutions\Filmweb\Exception\UnexpectedResponseException;
use NSolutions\Filmweb\Support\Data;

/**
 * A single GET endpoint of the Filmweb API: describes the request and maps the JSON response.
 *
 * Implement it to support endpoints not covered by the library and run it
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
     *
     * @throws UnexpectedResponseException when the payload does not have the expected shape
     */
    public function map(Data $data): mixed;
}
