<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Tests\Unit\Support;

use NSolutions\Filmweb\Support\ImageUrls;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImageUrls::class)]
final class ImageUrlsTest extends TestCase
{
    public function testReplacesSizePlaceholder(): void
    {
        $urls = new ImageUrls('https://fwcdn.pl/');

        self::assertSame('https://fwcdn.pl/fpo/06/28/628/7685907_1.6.jpg', $urls->poster('/06/28/628/7685907_1.$.jpg'));
        self::assertSame('https://fwcdn.pl/fpo/06/28/628/7685907_1.3.jpg', $urls->poster('/06/28/628/7685907_1.$.jpg', 3));
        self::assertNull($urls->poster(null));
        self::assertNull($urls->poster(''));
    }
}
