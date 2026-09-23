<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint\Film;

use NSolutions\Filmweb\Model\Rating;
use NSolutions\Filmweb\Support\Data;

/**
 * `GET /film/{id}/critics/rating` – critics' average rating.
 *
 * @extends FilmEndpoint<Rating>
 */
final readonly class GetCriticsRating extends FilmEndpoint
{
    protected function resource(): string
    {
        return 'critics/rating';
    }

    public function map(Data $data): Rating
    {
        return new Rating(
            average: $data->nullableFloat('rate') ?? 0.0,
            count: $data->nullableInt('count') ?? 0,
        );
    }
}
