<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint;

use NSolutions\Filmweb\Api\Endpoint;
use NSolutions\Filmweb\Model\TitleInfo;
use NSolutions\Filmweb\Model\TitleType;
use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Support\ImageUrls;

/**
 * `GET /title/{id}/info` – works for films, series and games.
 *
 * @implements Endpoint<TitleInfo>
 */
final readonly class GetTitleInfo implements Endpoint
{
    public function __construct(private int $titleId) {}

    public function path(): string
    {
        return "/title/{$this->titleId}/info";
    }

    public function query(): array
    {
        return [];
    }

    public function map(Data $data, ImageUrls $images): TitleInfo
    {
        $type = $data->nullableString('type');

        return new TitleInfo(
            id: $data->nullableInt('id') ?? $this->titleId,
            title: $data->string('title'),
            originalTitle: $data->nullableString('originalTitle'),
            year: $data->nullableInt('year'),
            type: $type === null ? null : TitleType::tryFrom($type),
            subType: $data->nullableString('subType'),
            posterUrl: $images->poster($data->nullableString('posterPath')),
        );
    }
}
