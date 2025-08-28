<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Tests\Service;

use Macpaw\SchemaContextBundle\Service\BaggageCodec;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BaggageCodecTest extends TestCase
{
    /**
     * @param array<string,string|null> $arrayBaggage
     */
    #[DataProvider('decodeDataProvider')]
    public function testDecode(array $arrayBaggage, string $expectedResult): void
    {
        $resolver = new BaggageCodec();
        $result = $resolver->encode($arrayBaggage);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @param array{string, ?string} $expectedResult
     */
    #[DataProvider('encodeDataProvider')]
    public function testEncode(string $rawBaggage, array $expectedResult): void
    {
        $resolver = new BaggageCodec();
        $result = $resolver->decode($rawBaggage);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @param array<string,string|null> $arrayBaggage
     * @param array<string,string|null> $expectedResult
     */
    #[DataProvider('encodeAndDecodeDataProvider')]
    public function testEncodeAndDecode(array $arrayBaggage, array $expectedResult): void
    {
        $resolver = new BaggageCodec();
        $result = $resolver->decode($resolver->encode($arrayBaggage));

        self::assertSame($expectedResult, $result);
    }

    /**
     * @return iterable<int, array{array<string,string|null>, string}>
     */
    public static function decodeDataProvider(): iterable
    {
        yield [
            [],
            '',
        ];

        yield [
            ['foo' => null],
            'foo',
        ];

        yield [
            [' foo ' => null],
            'foo',
        ];

        yield [
            ['foo' => null, 'bar' => 'baz'],
            'foo,bar=baz',
        ];

        yield [
            [' foo ' => null, ' bar ' => ' baz '],
            'foo,bar=baz',
        ];

        yield [
            ['foo' => null, 'bar' => 'baz', 'X-Schema' => '123'],
            'foo,bar=baz,X-Schema=123',
        ];
    }

    /**
     * @return iterable<int, array{string, array<string,string|null>}>
     */
    public static function encodeDataProvider(): iterable
    {
        yield [
            '',
            [],
        ];

        yield [
            'foo',
            ['foo' => null],
        ];

        yield [
            ' foo ',
            ['foo' => null],
        ];

        yield [
            'foo,bar=baz',
            ['foo' => null, 'bar' => 'baz'],
        ];

        yield [
            ' foo , bar = baz ',
            ['foo' => null, 'bar' => 'baz'],
        ];

        yield [
            'foo,bar=baz,X-Schema=123',
            ['foo' => null, 'bar' => 'baz', 'X-Schema' => '123'],
        ];
    }

    /**
     * @return iterable<int, array{array<string,string|null>, array<string,string|null>}>
     */
    public static function encodeAndDecodeDataProvider(): iterable
    {
        yield [
            [],
            [],
        ];

        yield [
            ['foo' => null],
            ['foo' => null],
        ];

        yield [
            [' foo ' => null],
            ['foo' => null],
        ];

        yield [
            ['foo' => null, 'bar' => 'baz'],
            ['foo' => null, 'bar' => 'baz'],
        ];

        yield [
            [' foo ' => null, ' bar ' => ' baz '],
            ['foo' => null, 'bar' => 'baz'],
        ];

        yield [
            ['foo' => null, 'bar' => 'baz', 'X-Schema' => '123'],
            ['foo' => null, 'bar' => 'baz', 'X-Schema' => '123'],
        ];
    }
}
