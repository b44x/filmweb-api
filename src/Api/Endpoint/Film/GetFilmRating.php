<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint\Film;

use NSolutions\Filmweb\Model\Rating;
use NSolutions\Filmweb\Support\Data;

/**
 * `GET /film/{id}/rating` – users' rating with the 1–10 vote distribution.
 *
 * @extends FilmEndpoint<Rating>
 */
final readonly class GetFilmRating extends FilmEndpoint
{
    protected function resource(): string
    {
        return 'rating';
    }

    public function map(Data $data): Rating
    {
        $distribution = [];

        foreach (range(1, 10) as $score) {
            $distribution[$score] = $data->nullableInt("countVote{$score}") ?? 0;
        }

        return new Rating(
            average: $data->nullableFloat('rate') ?? 0.0,
            count: $data->nullableInt('count') ?? 0,
            wantToSeeCount: $data->nullableInt('countWantToSee'),
            distribution: $distribution,
        );
    }
}
