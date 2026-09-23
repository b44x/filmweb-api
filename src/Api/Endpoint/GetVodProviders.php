<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint;

use NSolutions\Filmweb\Api\Endpoint;
use NSolutions\Filmweb\Model\VodProvider;
use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Support\ImageUrls;

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
    public function map(Data $data, ImageUrls $images): array
    {
        $providers = [];

        foreach ($data->items() as $provider) {
            $id = $provider->int('id');
            $providers[$id] = new VodProvider(
                id: $id,
                name: $provider->nullableString('displayName') ?? $provider->string('name'),
                url: $provider->nullableString('link'),
            );
        }

        return $providers;
    }
}
