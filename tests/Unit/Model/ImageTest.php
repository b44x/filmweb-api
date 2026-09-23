<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Tests\Unit\Model;

use NSolutions\Filmweb\Model\Image;
use NSolutions\Filmweb\Model\ImageKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Image::class)]
final class ImageTest extends TestCase
{
    public function testBuildsCdnUrlForTheRequestedSize(): void
    {
        $poster = new Image(ImageKind::Poster, '/06/28/628/7685907_1.$.jpg');

        self::assertSame('https://fwcdn.pl/fpo/06/28/628/7685907_1.3.jpg', $poster->url());
        self::assertSame('https://fwcdn.pl/fpo/06/28/628/7685907_1.6.jpg', $poster->url(6));
        self::assertSame('"https:\/\/fwcdn.pl\/fpo\/06\/28\/628\/7685907_1.3.jpg"', json_encode($poster));
        self::assertSame('https://fwcdn.pl/ppo/00/87/87/450015_1.3.jpg', (string) new Image(ImageKind::Person, '/00/87/87/450015_1.$.jpg'));
    }

    public function testTryFromIgnoresMissingPaths(): void
    {
        self::assertNull(Image::tryFrom(ImageKind::Poster, null));
        self::assertNull(Image::tryFrom(ImageKind::Poster, ''));
        self::assertNotNull(Image::tryFrom(ImageKind::Poster, '/x.$.jpg'));
    }
}
