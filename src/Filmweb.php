<?php

declare(strict_types=1);

namespace NSolutions\Filmweb;

use NSolutions\Filmweb\Api\ApiClient;
use NSolutions\Filmweb\Api\Endpoint;
use NSolutions\Filmweb\Api\Endpoint\Search\SearchTitles;
use NSolutions\Filmweb\Exception\FilmwebException;
use NSolutions\Filmweb\Http\CurlTransport;
use NSolutions\Filmweb\Http\Transport;
use NSolutions\Filmweb\Model\SearchHit;
use NSolutions\Filmweb\Resource\FilmResource;
use NSolutions\Filmweb\Resource\PersonResource;
use NSolutions\Filmweb\Resource\VodResource;
use NSolutions\Filmweb\Support\Data;

/**
 * Entry point of the library.
 *
 * ```php
 * $filmweb = Filmweb::create();
 *
 * $hit    = $filmweb->search('incepcja')[0];
 * $film   = $filmweb->films->get($hit->id);
 * $cast   = $filmweb->films->cast($hit->id, limit: 5);
 * $offers = $filmweb->vod->offers($hit->id);
 * $person = $filmweb->people->get(87);
 * ```
 *
 * Every call may throw a {@see FilmwebException}.
 */
final readonly class Filmweb
{
    public FilmResource $films;
    public PersonResource $people;
    public VodResource $vod;

    public function __construct(private ApiClient $client)
    {
        $this->people = new PersonResource($client);
        $this->films = new FilmResource($client, $this->people);
        $this->vod = new VodResource($client);
    }

    public static function create(?Transport $transport = null, Config $config = new Config()): self
    {
        return new self(new ApiClient($transport ?? new CurlTransport(), $config));
    }

    /**
     * Films, series, games and people matching the query, most relevant first.
     *
     * @return list<SearchHit>
     */
    public function search(string $query): array
    {
        return $this->client->call(new SearchTitles($query)) ?? [];
    }

    /**
     * Runs any endpoint – including your own {@see Endpoint} implementations.
     *
     * @template TResult
     *
     * @param Endpoint<TResult> $endpoint
     *
     * @return TResult|null `null` when Filmweb responds with 404
     */
    public function call(Endpoint $endpoint): mixed
    {
        return $this->client->call($endpoint);
    }

    /**
     * Raw JSON of any API path, e.g. `$filmweb->raw('/film/628/info')?->toArray()`.
     *
     * @param array<string, string> $query
     */
    public function raw(string $path, array $query = []): ?Data
    {
        return $this->client->fetch($path, $query);
    }
}
