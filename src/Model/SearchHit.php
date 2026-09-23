<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

final readonly class SearchHit
{
    /**
     * @param string          $type     e.g. `film`, `serial`, `game`, `person`
     * @param list<PersonRef> $mainCast
     */
    public function __construct(
        public int $id,
        public string $type,
        public string $title,
        public array $mainCast,
    ) {}

    public function titleType(): ?TitleType
    {
        return TitleType::tryFrom($this->type);
    }
}
