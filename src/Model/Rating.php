<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

final readonly class Rating
{
    /**
     * @param array<int, int> $distribution number of votes per score (users' rating only)
     */
    public function __construct(
        public float $average,
        public int $count,
        public ?int $wantToSeeCount = null,
        public array $distribution = [],
    ) {}

    public function rounded(int $precision = 1): float
    {
        return round($this->average, $precision);
    }
}
