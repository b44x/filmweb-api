<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

use DateTimeImmutable;

final readonly class Person
{
    /**
     * @param list<int> $knownFor IDs of the titles the person is best known for
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $realName,
        public ?DateTimeImmutable $birthDate,
        public ?string $birthPlace,
        public ?int $height,
        public ?string $mainProfession,
        public array $knownFor,
        public ?Image $photo,
    ) {}
}
