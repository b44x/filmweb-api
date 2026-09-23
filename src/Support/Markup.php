<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Support;

/**
 * Filmweb texts contain BBCode-like links, e.g. `[person=87]Keanu Reeves[/person]`.
 */
final class Markup
{
    public static function toPlainText(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $plain = preg_replace('~\[(/?)[a-z]+(?:=[^\]]*)?\]~i', '', $text) ?? $text;

        return trim(preg_replace('/[ \t]{2,}/', ' ', $plain) ?? $plain);
    }
}
