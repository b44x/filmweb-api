<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

final readonly class ReleaseDates
{
    public function __construct(
        public ?ReleaseDate $worldPremiere,
        public ?ReleaseDate $worldPublicRelease,
        public ?ReleaseDate $countryRelease,
        public ?ReleaseDate $countryLastReissue,
    ) {}
}
