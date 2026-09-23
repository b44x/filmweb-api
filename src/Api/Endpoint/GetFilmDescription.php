<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint;

use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Support\ImageUrls;
use NSolutions\Filmweb\Support\Markup;

/**
 * `GET /film/{id}/description` – full description as plain text (markup removed).
 *
 * @extends FilmEndpoint<string|null>
 */
final readonly class GetFilmDescription extends FilmEndpoint
{
    protected function resource(): string
    {
        return 'description';
    }

    public function map(Data $data, ImageUrls $images): ?string
    {
        return Markup::toPlainText($data->nullableString('synopsis'));
    }
}
