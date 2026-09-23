<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Resource;

use NSolutions\Filmweb\Api\ApiClient;
use NSolutions\Filmweb\Api\Endpoint\Film\GetCriticsRating;
use NSolutions\Filmweb\Api\Endpoint\Film\GetFilmDates;
use NSolutions\Filmweb\Api\Endpoint\Film\GetFilmDescription;
use NSolutions\Filmweb\Api\Endpoint\Film\GetFilmPreview;
use NSolutions\Filmweb\Api\Endpoint\Film\GetFilmRating;
use NSolutions\Filmweb\Api\Endpoint\Film\GetTopRoles;
use NSolutions\Filmweb\Api\Endpoint\Title\GetTitleInfo;
use NSolutions\Filmweb\Exception\FilmwebException;
use NSolutions\Filmweb\Model\CastMember;
use NSolutions\Filmweb\Model\Film;
use NSolutions\Filmweb\Model\FilmPreview;
use NSolutions\Filmweb\Model\Rating;
use NSolutions\Filmweb\Model\ReleaseDates;
use NSolutions\Filmweb\Model\TitleInfo;
use NSolutions\Filmweb\Model\TopRole;

/**
 * Films and series (Filmweb serves both under `/film/{id}/…`).
 *
 * Every method returns `null` (or an empty list) when the title does not exist.
 *
 * @throws FilmwebException on transport or API errors
 */
final readonly class FilmResource
{
    public function __construct(
        private ApiClient $client,
        private PersonResource $people,
    ) {}

    /**
     * Info, preview, both ratings and release dates – 5 requests, stops after the first 404.
     */
    public function get(int $filmId): ?Film
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

    public function info(int $filmId): ?TitleInfo
    {
        return $this->client->call(new GetTitleInfo($filmId));
    }

    public function preview(int $filmId): ?FilmPreview
    {
        return $this->client->call(new GetFilmPreview($filmId));
    }

    public function description(int $filmId): ?string
    {
        return $this->client->call(new GetFilmDescription($filmId));
    }

    public function rating(int $filmId): ?Rating
    {
        return $this->client->call(new GetFilmRating($filmId));
    }

    public function criticsRating(int $filmId): ?Rating
    {
        return $this->client->call(new GetCriticsRating($filmId));
    }

    public function dates(int $filmId): ?ReleaseDates
    {
        return $this->client->call(new GetFilmDates($filmId));
    }

    /**
     * Best rated roles – person IDs only, see {@see cast()} for resolved people.
     *
     * @return list<TopRole>
     */
    public function topRoles(int $filmId): array
    {
        return $this->client->call(new GetTopRoles($filmId)) ?? [];
    }

    /**
     * Best rated roles with people resolved – costs 1 + `$limit` requests.
     *
     * @param int<0, max> $limit
     *
     * @return list<CastMember>
     */
    public function cast(int $filmId, int $limit = 10): array
    {
        return array_map(
            fn(TopRole $role): CastMember => new CastMember($role, $this->people->get($role->personId)),
            \array_slice($this->topRoles($filmId), 0, $limit),
        );
    }
}
