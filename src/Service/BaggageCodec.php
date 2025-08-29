<?php

declare(strict_types=1);

namespace Macpaw\SchemaContextBundle\Service;

class BaggageCodec
{
    /**
     * @param array<string,string|null> $baggage
     */
    public function encode(array $baggage): string
    {
        $parts = [];

        foreach ($baggage as $key => $value) {
            if ($value === null) {
                $parts[] = trim($key);
            } else {
                $parts[] = trim($key) . '=' . trim($value);
            }
        }

        return implode(',', $parts);
    }

    /**
     * @return array<string,string|null>
     */
    public function decode(string $baggage): array
    {
        $result = [];
        foreach (explode(',', $baggage) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            if (str_contains($part, '=')) {
                [$key, $value] = explode('=', $part, 2);
                $result[trim($key)] = trim($value);
            } else {
                $result[$part] = null;
            }
        }

        return $result;
    }
}
