<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

final readonly class TitleInfo
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $originalTitle,
        public ?int $year,
        public ?TitleType $type,
        public ?string $subType,
        public ?string $posterUrl,
    ) {}
}
