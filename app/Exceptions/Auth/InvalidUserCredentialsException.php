<?php

namespace App\Exceptions\Auth;

use RuntimeException;

class InvalidUserCredentialsException extends RuntimeException
{
    public function __construct(
        string $message = 'Invalid user credentials.',
        public readonly int $status = 401,
        public readonly ?array $payload = null,
    ) {
        parent::__construct($message, $status);
    }
}