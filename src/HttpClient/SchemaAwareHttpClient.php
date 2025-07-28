<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\HttpClient;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Macpaw\SchemaContextBundle\Service\SchemaResolver;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class SchemaAwareHttpClient implements HttpClientInterface
{
    public function __construct(
        private HttpClientInterface $inner,
        private SchemaResolver $schemaResolver,
        private string $schemaRequestHeader
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $schema = $this->schemaResolver->getSchema();
        $baggageHeader = $this->schemaRequestHeader . '=' . $schema;
        $headers = isset($options['headers']) && is_array($options['headers'])
            ? $options['headers']
            : [];

        if (isset($headers['baggage'])) {
            $headers['baggage'] .= ',' . $baggageHeader;
        } else {
            $headers['baggage'] = $baggageHeader;
        }

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
        return new self($wrapped, $this->schemaResolver, $this->schemaRequestHeader);
    }
}
