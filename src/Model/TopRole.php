<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

/**
 * A role in a film, ranked by users' rating of the performance.
 */
final readonly class TopRole
{
    public function __construct(
        public int $id,
        public int $personId,
        public string $profession,
        public float $rating,
        public int $votesCount,
    ) {}
}
