<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\HttpClient;

use Macpaw\SchemaContextBundle\Service\BaggageCodec;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Macpaw\SchemaContextBundle\Service\BaggageSchemaResolver;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class BaggageAwareHttpClient implements HttpClientInterface
{
    public function __construct(
        private HttpClientInterface $inner,
        private BaggageSchemaResolver $baggageSchemaResolver,
        private BaggageCodec $baggageCodec,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $headers = isset($options['headers']) && is_array($options['headers'])
            ? $options['headers']
            : [];

        $baggage = isset($headers['baggage'])
            ? $this->baggageCodec->decode($headers['baggage'])
            : [];

        $baggage = [
            ...$baggage,
            ...($this->baggageSchemaResolver->getBaggage() ?? [])
        ];

        $headers['baggage'] = $this->baggageCodec->encode($baggage);
        $options['headers'] = $headers;

        return $this->inner->request($method, $url, $options);
    }

    public function stream(iterable|ResponseInterface $responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->inner->stream($responses, $timeout);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function withOptions(array $options): static
    {
        $wrapped = $this->inner->withOptions($options);

        /** @phpstan-ignore-next-line */
        return new self($wrapped, $this->baggageSchemaResolver, $this->baggageCodec);
    }
}
