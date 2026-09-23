<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Resource;

use NSolutions\Filmweb\Api\ApiClient;
use NSolutions\Filmweb\Api\Endpoint\Person\GetPerson;
use NSolutions\Filmweb\Exception\FilmwebException;
use NSolutions\Filmweb\Model\Person;

/**
 * People: actors, directors, crew.
 */
final readonly class PersonResource
{
    /**
     * @internal use {@see \NSolutions\Filmweb\Filmweb} to obtain resources
     */
    public function __construct(private ApiClient $client) {}

    /**
     * @throws FilmwebException
     */
    public function get(int $personId): ?Person
    {
        return $this->client->call(new GetPerson($personId));
    }
}
