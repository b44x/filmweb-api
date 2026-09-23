<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Http;

final readonly class Response
{
    public function __construct(
        public int $status,
        public string $body,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }
}
