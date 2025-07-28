<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Tests\HttpClient;

use Macpaw\SchemaContextBundle\HttpClient\SchemaAwareHttpClient;
use Macpaw\SchemaContextBundle\Service\SchemaResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SchemaAwareHttpClientTest extends TestCase
{
    public function testItInjectsSchemaIntoBaggageHeader(): void
    {
        $expectedSchema = 'tenant_42';
        $mockResponse = new MockResponse('OK');
        $schemaRequestHeader = 'X-Schema';

        $mockClient = $this->createMock(HttpClientInterface::class);
        $mockClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.example.com/test',
                $this->callback(function (array $options) use ($expectedSchema, $schemaRequestHeader) {
                    $headers = $options['headers'] ?? [];
                    $baggage = $headers['baggage'] ?? null;

                    if (is_array($baggage)) {
                        $baggage = implode(',', $baggage);
                    }

                    return is_string($baggage) && str_contains($baggage, $schemaRequestHeader . '=' . $expectedSchema);
                })
            )
            ->willReturn($mockResponse);

        $schemaResolver = $this->createMock(SchemaResolver::class);
        $schemaResolver->method('getSchema')->willReturn($expectedSchema);

        $client = new SchemaAwareHttpClient(
            $mockClient,
            $schemaResolver,
            $schemaRequestHeader,
        );

        $response = $client->request('GET', 'https://api.example.com/test');

        self::assertInstanceOf(ResponseInterface::class, $response);
    }
}
