<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

enum TitleType: string
{
    case Film = 'film';
    case Serial = 'serial';
    case Game = 'game';

    /**
     * URL segment used by filmweb.pl, e.g. `/serial/Gra+o+tron-2011-476848`.
     */
    public function urlSegment(): string
    {
        return match ($this) {
            self::Film => 'film',
            self::Serial => 'serial',
            self::Game => 'videogame',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Film => 'film',
            self::Serial => 'serial',
            self::Game => 'gra',
        };
    }
}
