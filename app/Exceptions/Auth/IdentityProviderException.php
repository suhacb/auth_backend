<?php

namespace App\Exceptions\Auth;

use RuntimeException;

class IdentityProviderException extends RuntimeException
{
    public function __construct(
        string $message = 'Identity provider error.',
        public readonly int $status = 500,
        public readonly ?array $payload = null,
    ) {
        parent::__construct($message, $status);
    }
}