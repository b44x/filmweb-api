<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

/**
 * Image category – maps to a directory on the Filmweb CDN.
 */
enum ImageKind: string
{
    case Poster = 'fpo';
    case Person = 'ppo';
}
