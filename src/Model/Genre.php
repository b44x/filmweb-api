<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

final readonly class Genre
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}
}
