<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint\Film;

use NSolutions\Filmweb\Api\Mapping\PersonRefMapper;
use NSolutions\Filmweb\Model\FilmPreview;
use NSolutions\Filmweb\Model\Genre;
use NSolutions\Filmweb\Model\Image;
use NSolutions\Filmweb\Model\ImageKind;
use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Support\Markup;

/**
 * `GET /film/{id}/preview` – titles, genres, countries, duration, synopsis, directors and main cast.
 *
 * @extends FilmEndpoint<FilmPreview>
 */
final readonly class GetFilmPreview extends FilmEndpoint
{
    protected function resource(): string
    {
        return 'preview';
    }

    public function map(Data $data): FilmPreview
    {
        return new FilmPreview(
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
            poster: Image::tryFrom(ImageKind::Poster, $data->nullableString('poster.path')),
            directors: PersonRefMapper::list($data, 'directors'),
            mainCast: PersonRefMapper::list($data, 'mainCast'),
            recommended: $data->bool('siteRecommends'),
        );
    }
}
