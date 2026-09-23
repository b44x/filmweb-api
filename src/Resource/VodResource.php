<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Resource;

use DateTimeImmutable;
use NSolutions\Filmweb\Api\ApiClient;
use NSolutions\Filmweb\Api\Endpoint\Vod\GetFilmVodOffers;
use NSolutions\Filmweb\Api\Endpoint\Vod\GetVodProviders;
use NSolutions\Filmweb\Exception\FilmwebException;
use NSolutions\Filmweb\Model\VodOffer;
use NSolutions\Filmweb\Model\VodProvider;

/**
 * Streaming services and "where to watch" offers.
 *
 * The provider dictionary is fetched once per instance and reused.
 */
final class VodResource
{
    /** @var array<int, VodProvider>|null */
    private ?array $providers = null;

    public function __construct(private readonly ApiClient $client) {}

    /**
     * @return array<int, VodProvider> keyed by provider ID
     *
     * @throws FilmwebException
     */
    public function providers(): array
    {
        return $this->providers ??= $this->client->call(new GetVodProviders()) ?? [];
    }

    /**
     * Offers valid at the given moment (now by default).
     *
     * @return list<VodOffer>
     *
     * @throws FilmwebException
     */
    public function offers(int $filmId, ?DateTimeImmutable $at = null): array
    {
        $at ??= new DateTimeImmutable();
        $offers = $this->client->call(new GetFilmVodOffers($filmId, $this->providers())) ?? [];

        return array_values(array_filter($offers, static fn(VodOffer $offer): bool => $offer->isAvailableAt($at)));
    }
}
