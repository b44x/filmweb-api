<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Support;

use DateTimeImmutable;
use DateTimeZone;
use NSolutions\Filmweb\Exception\UnexpectedResponseException;

/**
 * Typed, read-only accessor for decoded JSON objects.
 *
 * Keys may be dot-separated paths (`plot.synopsis`). Missing keys and `null`
 * resolve to `null` in `nullable*()` getters; type mismatches throw.
 */
final readonly class Data
{
    /**
     * @param array<array-key, mixed> $values
     */
    public function __construct(private array $values) {}

    public static function of(mixed $value): self
    {
        if (!\is_array($value)) {
            throw new UnexpectedResponseException(\sprintf('Expected a JSON object or list, got %s.', get_debug_type($value)));
        }

        return new self($value);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(): array
    {
        return $this->values;
    }

    public function get(string $path): mixed
    {
        $value = $this->values;

        foreach (explode('.', $path) as $key) {
            if (!\is_array($value) || !\array_key_exists($key, $value)) {
                return null;
            }

            $value = $value[$key];
        }

        return $value;
    }

    public function string(string $path): string
    {
        return $this->nullableString($path) ?? throw $this->missing($path);
    }

    public function nullableString(string $path): ?string
    {
        $value = $this->get($path);

        return match (true) {
            $value === null, $value === '' => null,
            \is_string($value), \is_int($value), \is_float($value) => (string) $value,
            default => throw $this->typeError($path, 'string', $value),
        };
    }

    public function int(string $path): int
    {
        return $this->nullableInt($path) ?? throw $this->missing($path);
    }

    public function nullableInt(string $path): ?int
    {
        $value = $this->get($path);

        return match (true) {
            $value === null => null,
            \is_int($value) => $value,
            \is_string($value) && is_numeric($value) => (int) $value,
            default => throw $this->typeError($path, 'int', $value),
        };
    }

    public function nullableFloat(string $path): ?float
    {
        $value = $this->get($path);

        return match (true) {
            $value === null => null,
            \is_int($value), \is_float($value) => (float) $value,
            \is_string($value) && is_numeric($value) => (float) $value,
            default => throw $this->typeError($path, 'float', $value),
        };
    }

    public function bool(string $path): bool
    {
        $value = $this->get($path);

        return match (true) {
            $value === null => false,
            \is_bool($value) => $value,
            \is_int($value) => $value !== 0,
            default => throw $this->typeError($path, 'bool', $value),
        };
    }

    /**
     * Filmweb "date int" format: `20100708` → 2010-07-08.
     */
    public function nullableDateInt(string $path): ?DateTimeImmutable
    {
        $value = $this->nullableInt($path);

        if ($value === null || $value <= 0) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Ymd', \sprintf('%08d', $value), new DateTimeZone('UTC'));

        return $date === false ? null : $date;
    }

    /**
     * ISO-8601 date-time without offset (Filmweb returns UTC), e.g. `2021-04-27T14:19:39`.
     */
    public function nullableDateTime(string $path): ?DateTimeImmutable
    {
        $value = $this->nullableString($path);

        if ($value === null) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:s', substr($value, 0, 19), new DateTimeZone('UTC'));

        return $date === false ? throw $this->typeError($path, 'date-time', $value) : $date;
    }

    /**
     * Items of a top-level JSON list.
     *
     * @return list<self>
     */
    public function items(): array
    {
        return array_map(self::of(...), array_values($this->values));
    }

    public function nullable(string $path): ?self
    {
        $value = $this->get($path);

        return $value === null ? null : self::of($value);
    }

    /**
     * @return list<self>
     */
    public function list(string $path): array
    {
        $value = $this->get($path);

        return $value === null ? [] : self::of($value)->items();
    }

    private function missing(string $path): UnexpectedResponseException
    {
        return new UnexpectedResponseException(\sprintf('Missing required field "%s".', $path));
    }

    private function typeError(string $path, string $expected, mixed $value): UnexpectedResponseException
    {
        return new UnexpectedResponseException(\sprintf('Expected %s at "%s", got %s.', $expected, $path, get_debug_type($value)));
    }
}
