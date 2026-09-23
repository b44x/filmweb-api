<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint;

use NSolutions\Filmweb\Model\ReleaseDate;
use NSolutions\Filmweb\Model\ReleaseDates;
use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Support\ImageUrls;

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

    public function map(Data $data, ImageUrls $images): ReleaseDates
    {
        return new ReleaseDates(
            worldPremiere: $this->date($data->nullable('worldReleaseDate')),
            worldPublicRelease: $this->date($data->nullable('worldPublicReleaseDate')),
            countryRelease: $this->date($data->nullable('countryPublicReleaseDate')),
            countryLastReissue: $this->date($data->nullable('countryReissueLastPublicDate')),
        );
    }

    private function date(?Data $release): ?ReleaseDate
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
