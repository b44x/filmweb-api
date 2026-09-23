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
        $slug = $this->year === null
            ? \sprintf('%s-%d', urlencode($this->title), $this->id)
            : \sprintf('%s-%d-%d', urlencode($this->title), $this->year, $this->id);

        return \sprintf('https://www.filmweb.pl/%s/%s', ($this->type ?? TitleType::Film)->urlSegment(), $slug);
    }
}
