<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Api\Endpoint\Search;

use NSolutions\Filmweb\Api\Endpoint;
use NSolutions\Filmweb\Api\Mapping\PersonRefMapper;
use NSolutions\Filmweb\Exception\InvalidArgumentException;
use NSolutions\Filmweb\Model\SearchHit;
use NSolutions\Filmweb\Support\Data;

/**
 * `GET /live/search?query=…` – the autocomplete search used by filmweb.pl.
 *
 * @implements Endpoint<list<SearchHit>>
 */
final readonly class SearchTitles implements Endpoint
{
    private string $query;

    public function __construct(string $query)
    {
        $this->query = trim($query);

        if ($this->query === '') {
            throw new InvalidArgumentException('Search query must not be empty.');
        }
    }

    public function path(): string
    {
        return '/live/search';
    }

    public function query(): array
    {
        return ['query' => $this->query];
    }

    /**
     * @return list<SearchHit>
     */
    public function map(Data $data): array
    {
        return array_map(
            static fn(Data $hit): SearchHit => new SearchHit(
                id: $hit->int('id'),
                type: $hit->string('type'),
                title: $hit->nullableString('matchedTitle') ?? '',
                mainCast: PersonRefMapper::list($hit, 'filmMainCast'),
            ),
            $data->list('searchHits'),
        );
    }
}
