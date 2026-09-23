<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint;

use NSolutions\Filmweb\Model\Genre;
use NSolutions\Filmweb\Model\Preview;
use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Support\ImageUrls;
use NSolutions\Filmweb\Support\Markup;

/**
 * `GET /film/{id}/preview` – titles, genres, countries, duration, synopsis, directors and main cast.
 *
 * @extends FilmEndpoint<Preview>
 */
final readonly class GetFilmPreview extends FilmEndpoint
{
    use MapsPeople;

    protected function resource(): string
    {
        return 'preview';
    }

    public function map(Data $data, ImageUrls $images): Preview
    {
        return new Preview(
            id: $data->nullableInt('id') ?? $this->filmId,
            title: $data->nullableString('title.title'),
            originalTitle: $data->nullableString('originalTitle.title'),
            year: $data->nullableInt('year'),
            duration: $data->nullableInt('duration'),
            genres: array_map(
                static fn(Data $genre): Genre => new Genre($genre->int('id'), $genre->string('name.text')),
                $data->list('genres'),
            ),
            countries: array_map(static fn(Data $country): string => $country->string('code'), $data->list('countries')),
            synopsis: Markup::toPlainText($data->nullableString('plot.synopsis') ?? $data->nullableString('plotOrDescriptionSynopsis')),
            posterUrl: $images->poster($data->nullableString('poster.path')),
            mainCast: $this->people($data, 'mainCast'),
            directors: $this->people($data, 'directors'),
        );
    }
}
