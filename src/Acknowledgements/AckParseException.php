<?php

namespace LabelTools\PhpCwrExporter\Acknowledgements;

use RuntimeException;

class AckParseException extends RuntimeException
{
    private string $errorCode;
    private array $context;

    public function __construct(string $errorCode, string $message, array $context = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
