<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Exception;

/**
 * An argument passed to the library is invalid (e.g. an empty search query).
 */
final class InvalidArgumentException extends \InvalidArgumentException implements FilmwebException {}
