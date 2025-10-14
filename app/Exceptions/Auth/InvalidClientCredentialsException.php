<?php

namespace App\Exceptions\Auth;

use RuntimeException;

class InvalidClientCredentialsException extends RuntimeException
{
    public function __construct(
        string $message = 'Invalid client credentials.',
        public readonly int $status = 400,
        public readonly ?array $payload = null,
    ) {
        parent::__construct($message, $status);
    }
}