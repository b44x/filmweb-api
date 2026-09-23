<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint\Film;

use NSolutions\Filmweb\Model\ReleaseDate;
use NSolutions\Filmweb\Model\ReleaseDates;
use NSolutions\Filmweb\Support\Data;

/**
 * `GET /film/{id}/dates` – world premiere and release dates in the locale's country.
 *
 * @extends FilmEndpoint<ReleaseDates>
 */
final readonly class GetFilmDates extends FilmEndpoint
{
    protected function resource(): string
    {
        return 'dates';
    }

    public function map(Data $data): ReleaseDates
    {
        return new ReleaseDates(
            worldPremiere: self::releaseDate($data->nullable('worldReleaseDate')),
            worldPublicRelease: self::releaseDate($data->nullable('worldPublicReleaseDate')),
            countryRelease: self::releaseDate($data->nullable('countryPublicReleaseDate')),
            countryLastReissue: self::releaseDate($data->nullable('countryReissueLastPublicDate')),
        );
    }

    private static function releaseDate(?Data $release): ?ReleaseDate
    {
        $date = $release?->nullableDateInt('dateInt');

        return $release === null || $date === null ? null : new ReleaseDate(
            date: $date,
            country: $release->nullableString('country'),
            cinemaRelease: $release->bool('cinemasRelease'),
            reissue: $release->bool('reissue'),
        );
    }
}
