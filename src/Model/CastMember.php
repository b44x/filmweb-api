<?php

declare(strict_types=1);

namespace NSolutions\Filmweb\Model;

/**
 * {@see TopRole} with the person's details resolved.
 */
final readonly class CastMember
{
    public function __construct(
        public TopRole $role,
        public ?Person $person,
    ) {}
}
