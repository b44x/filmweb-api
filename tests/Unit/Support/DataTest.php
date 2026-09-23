<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Tests\Unit\Support;

use NSolutions\Filmweb\Exception\UnexpectedResponseException;
use NSolutions\Filmweb\Support\Data;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Data::class)]
final class DataTest extends TestCase
{
    public function testReadsTypedValuesByPath(): void
    {
        $data = new Data(['id' => '628', 'rate' => 7, 'plot' => ['synopsis' => 'Neo'], 'empty' => '', 'cast' => [['id' => 1], ['id' => 2]]]);

        self::assertSame(628, $data->int('id'));
        self::assertSame(7.0, $data->nullableFloat('rate'));
        self::assertSame('Neo', $data->string('plot.synopsis'));
        self::assertNull($data->nullableString('empty'));
        self::assertNull($data->nullableString('plot.missing.deeper'));
        self::assertSame(2, $data->list('cast')[1]->int('id'));
        self::assertSame([], $data->list('missing'));
        self::assertSame('Neo', $data->nullable('plot')?->string('synopsis'));
    }

    public function testReadsDatesAndFlags(): void
    {
        $data = new Data(['day' => 20100730, 'zero' => 0, 'start' => '2026-07-07T22:00:00', 'yes' => true, 'one' => 1]);

        self::assertSame('2010-07-30', $data->nullableDateInt('day')?->format('Y-m-d'));
        self::assertNull($data->nullableDateInt('zero'));
        self::assertSame('2026-07-07T22:00:00+00:00', $data->nullableDateTime('start')?->format(DATE_ATOM));
        self::assertNull($data->nullableDateTime('missing'));
        self::assertTrue($data->bool('yes'));
        self::assertTrue($data->bool('one'));
        self::assertFalse($data->bool('missing'));
        self::assertSame(2, (new Data([['id' => 1], ['id' => 2]]))->items()[1]->int('id'));
    }

    public function testThrowsOnMissingRequiredField(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('Missing required field "title"');

        (new Data([]))->string('title');
    }

    public function testThrowsOnTypeMismatch(): void
    {
        $this->expectException(UnexpectedResponseException::class);

        (new Data(['id' => ['nested']]))->nullableInt('id');
    }

    public function testRejectsScalarPayload(): void
    {
        $this->expectException(UnexpectedResponseException::class);

        Data::of('text');
    }
}
