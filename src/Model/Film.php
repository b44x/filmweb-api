<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

/**
 * Aggregate of the most useful data about a title, see {@see \NSolutions\Filmweb\Filmweb::film()}.
 */
final readonly class Film
{
    public function __construct(
        public TitleInfo $info,
        public ?Preview $preview,
        public ?Rating $rating,
        public ?Rating $criticsRating,
        public ?ReleaseDates $dates,
    ) {}
}
