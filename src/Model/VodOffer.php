<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

use DateTimeImmutable;

/**
 * Where a film can be watched. Prices are in PLN, `null` when the option is not offered.
 */
final readonly class VodOffer
{
    public function __construct(
        public int $providerId,
        public ?VodProvider $provider,
        public string $url,
        public ?DateTimeImmutable $availableFrom,
        public ?DateTimeImmutable $availableUntil,
        public ?float $buyPrice,
        public ?float $rentPrice,
        public bool $subscription,
        public bool $free,
    ) {}

    public function isAvailableAt(DateTimeImmutable $moment): bool
    {
        return ($this->availableFrom === null || $this->availableFrom <= $moment)
            && ($this->availableUntil === null || $this->availableUntil > $moment);
    }
}
