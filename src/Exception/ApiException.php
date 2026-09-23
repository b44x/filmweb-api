<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Exception;

use RuntimeException;

/**
 * Filmweb answered with an unexpected HTTP status (e.g. 429, 5xx).
 */
final class ApiException extends RuntimeException implements FilmwebException
{
    public static function fromStatus(int $status, string $url): self
    {
        return new self(\sprintf('Filmweb responded with HTTP %d for %s.', $status, $url), $status);
    }

    public function status(): int
    {
        return $this->getCode();
    }
}
