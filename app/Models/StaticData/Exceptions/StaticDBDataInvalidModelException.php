<?php

namespace App\Services\Support\StaticDBData\Exceptions;

use Exception;
use Throwable;

class StaticDBDataInvalidModelException extends Exception
{
    public function __construct(
        string $staticDBDataClass,
        $value,
        int $code = 0,
        Throwable $previous = null
    ) {
        $message = "invalid model (id not found): {$value}, in StaticDBData class: {$staticDBDataClass}.";
        parent::__construct($message, $code, $previous);
    }
}
