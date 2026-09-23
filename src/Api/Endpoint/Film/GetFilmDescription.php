<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint\Film;

use NSolutions\Filmweb\Support\Data;
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

    public function map(Data $data): ?string
    {
        return Markup::toPlainText($data->nullableString('synopsis'));
    }
}
