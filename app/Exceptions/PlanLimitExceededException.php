<?php

namespace App\Exceptions;

use RuntimeException;

class PlanLimitExceededException extends RuntimeException
{
    public function __construct(
        string $message = 'Your plan limit has been reached.',
        public readonly string $limitType = 'orders',
        public readonly ?string $upgradeUrl = null,
    ) {
        parent::__construct($message);
    }
}
