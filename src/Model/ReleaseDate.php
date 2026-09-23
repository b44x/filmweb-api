<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

use DateTimeImmutable;

final readonly class ReleaseDate
{
    public function __construct(
        public DateTimeImmutable $date,
        public ?string $country,
        public bool $cinemaRelease,
        public bool $reissue,
    ) {}
}
