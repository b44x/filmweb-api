<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Tests\Unit\Resource;

use NSolutions\Filmweb\Api\ApiClient;
use NSolutions\Filmweb\Api\Endpoint\Person\GetPerson;
use NSolutions\Filmweb\Config;
use NSolutions\Filmweb\Model\Image;
use NSolutions\Filmweb\Resource\PersonResource;
use NSolutions\Filmweb\Support\Data;
use NSolutions\Filmweb\Tests\Fixtures\FakeTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PersonResource::class)]
#[CoversClass(GetPerson::class)]
#[UsesClass(ApiClient::class)]
#[UsesClass(Config::class)]
#[UsesClass(Data::class)]
#[UsesClass(Image::class)]
final class PersonResourceTest extends TestCase
{
    public function testFetchesPerson(): void
    {
        $transport = (new FakeTransport())->withFixture('/person/87/preview', 'person-preview');

        $person = (new PersonResource(new ApiClient($transport)))->get(87);

        self::assertNotNull($person);
        self::assertSame('Keanu Reeves', $person->name);
        self::assertSame('Keanu Charles Reeves', $person->realName);
        self::assertSame('1964-09-02', $person->birthDate?->format('Y-m-d'));
        self::assertSame('Bejrut', $person->birthPlace);
        self::assertSame(186, $person->height);
        self::assertSame('actors', $person->mainProfession);
        self::assertSame([628, 1012, 192133, 7967, 7603, 831520, 792556, 33996, 9913, 9680], $person->knownFor);
        self::assertSame('https://fwcdn.pl/ppo/00/87/87/450015_1.3.jpg', $person->photo?->url());
    }

    public function testReturnsNullForUnknownPerson(): void
    {
        self::assertNull((new PersonResource(new ApiClient(new FakeTransport())))->get(1));
    }
}
