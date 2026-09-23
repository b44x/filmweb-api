<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

final readonly class Preview
{
    /**
     * @param list<Genre>     $genres
     * @param list<string>    $countries ISO 3166-1 alpha-2 codes, e.g. `["US", "GB"]`
     * @param list<PersonRef> $mainCast
     * @param list<PersonRef> $directors
     */
    public function __construct(
        public int $id,
        public ?string $title,
        public ?string $originalTitle,
        public ?int $year,
        public ?int $duration,
        public array $genres,
        public array $countries,
        public ?string $synopsis,
        public ?string $posterUrl,
        public array $mainCast,
        public array $directors,
    ) {}
}
