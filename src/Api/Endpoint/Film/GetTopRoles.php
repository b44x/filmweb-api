<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint\Film;

use NSolutions\Filmweb\Model\TopRole;
use NSolutions\Filmweb\Support\Data;

/**
 * `GET /film/{id}/top-roles` – best rated roles (person IDs only).
 *
 * @extends FilmEndpoint<list<TopRole>>
 */
final readonly class GetTopRoles extends FilmEndpoint
{
    protected function resource(): string
    {
        return 'top-roles';
    }

    /**
     * @return list<TopRole>
     */
    public function map(Data $data): array
    {
        return array_map(
            static fn(Data $role): TopRole => new TopRole(
                id: $role->int('id'),
                personId: $role->int('person'),
                profession: $role->nullableString('profession') ?? 'unknown',
                rating: $role->nullableFloat('rate') ?? 0.0,
                votesCount: $role->nullableInt('count') ?? 0,
            ),
            $data->items(),
        );
    }
}
