<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

final readonly class TitleInfo
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $originalTitle,
        public ?int $year,
        public ?TitleType $type,
        public ?string $subType,
        public ?Image $poster,
    ) {}

    /**
     * Canonical page on filmweb.pl, e.g. `https://www.filmweb.pl/film/Matrix-1999-628`.
     */
    public function url(): string
    {
        $slug = implode('-', array_filter([urlencode($this->title), $this->year === null ? null : (string) $this->year, (string) $this->id]));

        return \sprintf('https://www.filmweb.pl/%s/%s', ($this->type ?? TitleType::Film)->urlSegment(), $slug);
    }
}
