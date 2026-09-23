<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint;

use NSolutions\Filmweb\Model\PersonRef;
use NSolutions\Filmweb\Support\Data;

trait MapsPeople
{
    /**
     * @return list<PersonRef>
     */
    private function people(Data $data, string $path): array
    {
        return array_map(
            static fn(Data $person): PersonRef => new PersonRef($person->int('id'), $person->string('name')),
            $data->list($path),
        );
    }
}
