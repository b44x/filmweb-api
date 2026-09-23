<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Mapping;

use NSolutions\Filmweb\Model\PersonRef;
use NSolutions\Filmweb\Support\Data;

/**
 * Maps `[{"id": 87, "name": "Keanu Reeves"}, …]` lists shared by several endpoints.
 *
 * @internal not covered by the backward compatibility promise
 */
final class PersonRefMapper
{
    /**
     * @return list<PersonRef>
     */
    public static function list(Data $data, string $path): array
    {
        return array_map(
            static fn(Data $person): PersonRef => new PersonRef($person->int('id'), $person->string('name')),
            $data->list($path),
        );
    }
}
