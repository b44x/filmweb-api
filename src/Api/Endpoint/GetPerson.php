<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint;

use NSolutions\Filmweb\Api\Endpoint;
use NSolutions\Filmweb\Model\Person;
use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Support\ImageUrls;

/**
 * `GET /person/{id}/preview` – name, birth date and place, height, best known titles.
 *
 * @implements Endpoint<Person>
 */
final readonly class GetPerson implements Endpoint
{
    public function __construct(private int $personId) {}

    public function path(): string
    {
        return "/person/{$this->personId}/preview";
    }

    public function query(): array
    {
        return [];
    }

    public function map(Data $data, ImageUrls $images): Person
    {
        $knownFor = $data->get('filmsKnownFor');

        return new Person(
            id: $data->nullableInt('id') ?? $this->personId,
            name: $data->string('name'),
            realName: $data->nullableString('info.realName'),
            birthDate: $data->nullableDateInt('info.birthDateInt'),
            birthPlace: $data->nullableString('birthplace.cityName'),
            height: $data->nullableInt('info.height'),
            mainProfession: $data->nullableString('mainProfession'),
            knownFor: \is_array($knownFor) ? array_values(array_filter($knownFor, \is_int(...))) : [],
            photoUrl: $images->person($data->nullableString('poster.path')),
        );
    }
}
