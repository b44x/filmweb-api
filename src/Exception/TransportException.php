<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Exception;

use RuntimeException;

/**
 * The HTTP request could not be completed (DNS, connection, TLS, timeout).
 */
final class TransportException extends RuntimeException implements FilmwebException {}
