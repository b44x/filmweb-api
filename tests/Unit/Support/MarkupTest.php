<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Tests\Unit\Support;

use NSolutions\Filmweb\Support\Markup;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Markup::class)]
final class MarkupTest extends TestCase
{
    public function testStripsFilmwebTags(): void
    {
        self::assertSame(
            'Neo (Keanu Reeves) spotyka Morfeusza w filmie Matrix.',
            Markup::toPlainText('Neo ([person=87]Keanu Reeves[/person]) spotyka  Morfeusza w filmie [film=628]Matrix[/film].'),
        );
    }

    public function testKeepsNonTagBrackets(): void
    {
        self::assertSame('Rok [1999]', Markup::toPlainText('Rok [1999]'));
        self::assertNull(Markup::toPlainText(null));
    }
}
