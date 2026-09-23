<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Exception;

use UnexpectedValueException;

/**
 * The response does not match the format this library understands.
 */
final class UnexpectedResponseException extends UnexpectedValueException implements FilmwebException {}
