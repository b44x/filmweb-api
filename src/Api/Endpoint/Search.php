<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint;

use InvalidArgumentException;
use NSolutions\Filmweb\Api\Endpoint;
use NSolutions\Filmweb\Model\SearchHit;
use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Support\ImageUrls;

/**
 * `GET /live/search?query=…` – the autocomplete search used by filmweb.pl.
 *
 * @implements Endpoint<list<SearchHit>>
 */
final readonly class Search implements Endpoint
{
    use MapsPeople;

    public function __construct(private string $query)
    {
        if (trim($query) === '') {
            throw new InvalidArgumentException('Search query must not be empty.');
        }
    }

    public function path(): string
    {
        return '/live/search';
    }

    public function query(): array
    {
        return ['query' => trim($this->query)];
    }

    /**
     * @return list<SearchHit>
     */
    public function map(Data $data, ImageUrls $images): array
    {
        return array_map(
            fn(Data $hit): SearchHit => new SearchHit(
                id: $hit->int('id'),
                type: $hit->string('type'),
                title: $hit->nullableString('matchedTitle') ?? '',
                mainCast: $this->people($hit, 'filmMainCast'),
            ),
            $data->list('searchHits'),
        );
    }
}
