<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Tests\HttpClient;

use Macpaw\SchemaContextBundle\HttpClient\BaggageAwareHttpClient;
use Macpaw\SchemaContextBundle\Service\BaggageCodec;
use Macpaw\SchemaContextBundle\Service\BaggageSchemaResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class BaggageAwareHttpClientTest extends TestCase
{
    /**
     * @param array<string,string|null> $arrayBaggage
     */
    #[DataProvider('baggageHeaderDataProvider')]
    public function testItInjectsSchemaIntoBaggageHeader(
        string $requestBaggage,
        array $arrayBaggage,
        string $expectedSentBaggage
    ): void {
        $environmentSchema = 'default';
        $environmentName = 'dev';
        $schemaOverridableEnvironments = ['dev', 'test'];
        $baggageSchemaResolver = new BaggageSchemaResolver(
            $environmentSchema,
            $environmentName,
            $schemaOverridableEnvironments
        );

        $mockClient = $this->createMock(HttpClientInterface::class);
        $mockClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.example.com/test',
                $this->callback(function (array $options) use ($expectedSentBaggage) {
                    $headers = $options['headers'] ?? [];
                    $baggage = $headers['baggage'] ?? null;

                    return $baggage === $expectedSentBaggage;
                })
            )
            ->willReturn(new MockResponse('OK'));

        $baggageSchemaResolver->setBaggage($arrayBaggage);
        $baggageCodec = new BaggageCodec();

        $client = new BaggageAwareHttpClient(
            $mockClient,
            $baggageSchemaResolver,
            $baggageCodec,
        );

        $response = $client->request('GET', 'https://api.example.com/test', [
            'headers' => [
                'baggage' => $requestBaggage,
            ],
        ]);

        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @return iterable<int, array{string, array<string,string|null>, string}>
     */
    public static function baggageHeaderDataProvider(): iterable
    {
        yield [
            '',
            [],
            '',
        ];

        yield [
            '',
            [
                'X-Schema' => 'tenant_42',
            ],
            'X-Schema=tenant_42',
        ];

        yield [
            '',
            [
                'X-Schema' => 'tenant_42',
                'foo' => null,
                'bar' => 'baz',
            ],
            'X-Schema=tenant_42,foo,bar=baz',
        ];

        yield [
            'test = 123',
            [
                'X-Schema' => 'tenant_42',
                'foo' => null,
                'bar' => 'baz',
            ],
            'test=123,X-Schema=tenant_42,foo,bar=baz',
        ];
    }
}
