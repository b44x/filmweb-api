<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint;

use NSolutions\Filmweb\Api\Endpoint;
use NSolutions\Filmweb\Model\VodOffer;
use NSolutions\Filmweb\Model\VodProvider;
use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Support\ImageUrls;

/**
 * `GET /vod/film/{id}/providers/list` – streaming/rental offers of a film.
 *
 * @implements Endpoint<list<VodOffer>>
 */
final readonly class GetFilmVodOffers implements Endpoint
{
    /**
     * @param array<int, VodProvider> $providers dictionary from {@see GetVodProviders}
     */
    public function __construct(
        private int $filmId,
        private array $providers = [],
    ) {}

    public function path(): string
    {
        return "/vod/film/{$this->filmId}/providers/list";
    }

    public function query(): array
    {
        return [];
    }

    /**
     * @return list<VodOffer>
     */
    public function map(Data $data, ImageUrls $images): array
    {
        return array_map($this->offer(...), $data->items());
    }

    private function offer(Data $offer): VodOffer
    {
        $payments = $offer->list('payments');
        $providerId = $offer->int('vodProvider');

        return new VodOffer(
            providerId: $providerId,
            provider: $this->providers[$providerId] ?? null,
            url: $offer->string('link'),
            availableFrom: $offer->nullableDateTime('start'),
            availableUntil: $offer->nullableDateTime('end'),
            buyPrice: $this->lowestPrice($payments, 'buy'),
            rentPrice: $this->lowestPrice($payments, 'rent'),
            subscription: $this->anyPayment($payments, 'subscription'),
            free: $this->anyPayment($payments, 'free'),
        );
    }

    /**
     * Prices are sent in grosze (1/100 PLN).
     *
     * @param list<Data> $payments
     */
    private function lowestPrice(array $payments, string $flag): ?float
    {
        $prices = [];

        foreach ($payments as $payment) {
            $price = $payment->nullableInt('price');

            if ($price !== null && $payment->bool($flag)) {
                $prices[] = $price;
            }
        }

        return $prices === [] ? null : min($prices) / 100;
    }

    /**
     * @param list<Data> $payments
     */
    private function anyPayment(array $payments, string $flag): bool
    {
        foreach ($payments as $payment) {
            if ($payment->bool($flag)) {
                return true;
            }
        }

        return false;
    }
}
