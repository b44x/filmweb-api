<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint\Vod;

use NSolutions\Filmweb\Api\Endpoint;
use NSolutions\Filmweb\Model\VodProvider;
use NSolutions\Filmweb\Support\Data;

/**
 * `GET /vod/providers/list` – dictionary of streaming services (Netflix, HBO Max…).
 *
 * @implements Endpoint<array<int, VodProvider>>
 */
final readonly class GetVodProviders implements Endpoint
{
    public function path(): string
    {
        return '/vod/providers/list';
    }

    public function query(): array
    {
        return [];
    }

    /**
     * @return array<int, VodProvider> keyed by provider ID
     */
    public function map(Data $data): array
    {
        $providers = [];

        foreach ($data->items() as $item) {
            $provider = new VodProvider(
                id: $item->int('id'),
                name: $item->nullableString('displayName') ?? $item->string('name'),
                url: $item->nullableString('link'),
            );
            $providers[$provider->id] = $provider;
        }

        return $providers;
    }
}
