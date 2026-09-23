<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

enum TitleType: string
{
    case Film = 'film';
    case Serial = 'serial';
    case Game = 'game';

    public function label(): string
    {
        return match ($this) {
            self::Film => 'film',
            self::Serial => 'serial',
            self::Game => 'gra',
        };
    }
}
