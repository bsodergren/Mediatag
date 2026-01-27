<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp;

final readonly class GrepError
{
    public function __construct(
        public ?string $file, // null for global pattern errors
        public ?int $errorCode,
        public ?string $errorMessage
    ) {}
}
