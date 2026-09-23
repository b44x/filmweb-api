<?php

declare(strict_types=1);

namespace NSolutions\Filmweb;

use DateTimeImmutable;
use NSolutions\Filmweb\Api\ApiClient;
use NSolutions\Filmweb\Api\Endpoint;
use NSolutions\Filmweb\Api\Endpoint\GetCriticsRating;
use NSolutions\Filmweb\Api\Endpoint\GetFilmDates;
use NSolutions\Filmweb\Api\Endpoint\GetFilmDescription;
use NSolutions\Filmweb\Api\Endpoint\GetFilmPreview;
use NSolutions\Filmweb\Api\Endpoint\GetFilmRating;
use NSolutions\Filmweb\Api\Endpoint\GetFilmVodOffers;
use NSolutions\Filmweb\Api\Endpoint\GetPerson;
use NSolutions\Filmweb\Api\Endpoint\GetTitleInfo;
use NSolutions\Filmweb\Api\Endpoint\GetTopRoles;
use NSolutions\Filmweb\Api\Endpoint\GetVodProviders;
use NSolutions\Filmweb\Api\Endpoint\Search;
use NSolutions\Filmweb\Exception\FilmwebException;
use NSolutions\Filmweb\Http\CurlTransport;
use NSolutions\Filmweb\Http\Transport;
use NSolutions\Filmweb\Model\CastMember;
use NSolutions\Filmweb\Model\Film;
use NSolutions\Filmweb\Model\Person;
use NSolutions\Filmweb\Model\Preview;
use NSolutions\Filmweb\Model\Rating;
use NSolutions\Filmweb\Model\ReleaseDates;
use NSolutions\Filmweb\Model\SearchHit;
use NSolutions\Filmweb\Model\TitleInfo;
use NSolutions\Filmweb\Model\TopRole;
use NSolutions\Filmweb\Model\VodOffer;
use NSolutions\Filmweb\Model\VodProvider;
use NSolutions\Filmweb\Support\Data;

/**
 * Entry point of the library – a thin, typed facade over {@see ApiClient}.
 *
 * ```php
 * $filmweb = Filmweb::create();
 * $hits = $filmweb->search('matrix');
 * $film = $filmweb->film($hits[0]->id);
 * ```
 *
 * Methods return `null` when the title does not exist and throw {@see FilmwebException} on errors.
 */
final readonly class Filmweb
{
    public function __construct(private ApiClient $client) {}

    public static function create(?Transport $transport = null, Config $config = new Config()): self
    {
        return new self(new ApiClient($transport ?? new CurlTransport(), $config));
    }

    /**
     * Runs any endpoint – including your own {@see Endpoint} implementations.
     *
     * @template TResult
     *
     * @param Endpoint<TResult> $endpoint
     *
     * @return TResult|null
     */
    public function call(Endpoint $endpoint): mixed
    {
        return $this->client->call($endpoint);
    }

    /**
     * Raw access to any API path, e.g. `$filmweb->raw('/film/628/info')?->toArray()`.
     *
     * @param array<string, string> $query
     */
    public function raw(string $path, array $query = []): ?Data
    {
        return $this->client->fetch($path, $query);
    }

    /**
     * @return list<SearchHit>
     */
    public function search(string $query): array
    {
        return $this->call(new Search($query)) ?? [];
    }

    /**
     * Info, preview, both ratings and release dates in one call (5 requests).
     */
    public function film(int $filmId): ?Film
    {
        $info = $this->info($filmId);

        return $info === null ? null : new Film(
            info: $info,
            preview: $this->preview($filmId),
            rating: $this->rating($filmId),
            criticsRating: $this->criticsRating($filmId),
            dates: $this->dates($filmId),
        );
    }

    public function info(int $titleId): ?TitleInfo
    {
        return $this->call(new GetTitleInfo($titleId));
    }

    public function preview(int $filmId): ?Preview
    {
        return $this->call(new GetFilmPreview($filmId));
    }

    public function description(int $filmId): ?string
    {
        return $this->call(new GetFilmDescription($filmId));
    }

    public function rating(int $filmId): ?Rating
    {
        return $this->call(new GetFilmRating($filmId));
    }

    public function criticsRating(int $filmId): ?Rating
    {
        return $this->call(new GetCriticsRating($filmId));
    }

    public function dates(int $filmId): ?ReleaseDates
    {
        return $this->call(new GetFilmDates($filmId));
    }

    /**
     * Best rated roles – person IDs only; see {@see topCast()} for resolved people.
     *
     * @return list<TopRole>
     */
    public function topRoles(int $filmId): array
    {
        return $this->call(new GetTopRoles($filmId)) ?? [];
    }

    /**
     * Best rated roles with people resolved (1 + $limit requests).
     *
     * @return list<CastMember>
     */
    public function topCast(int $filmId, int $limit = 10): array
    {
        return array_map(
            fn(TopRole $role): CastMember => new CastMember($role, $this->person($role->personId)),
            \array_slice($this->topRoles($filmId), 0, max(0, $limit)),
        );
    }

    public function person(int $personId): ?Person
    {
        return $this->call(new GetPerson($personId));
    }

    /**
     * @return array<int, VodProvider> keyed by provider ID
     */
    public function vodProviders(): array
    {
        return $this->call(new GetVodProviders()) ?? [];
    }

    /**
     * Streaming and rental offers available at the given moment (now by default).
     *
     * @return list<VodOffer>
     */
    public function whereToWatch(int $filmId, ?DateTimeImmutable $at = null): array
    {
        $at ??= new DateTimeImmutable();
        $offers = $this->call(new GetFilmVodOffers($filmId, $this->vodProviders())) ?? [];

        return array_values(array_filter($offers, static fn(VodOffer $offer): bool => $offer->isAvailableAt($at)));
    }
}
