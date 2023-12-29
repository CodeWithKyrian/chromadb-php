<?php

declare(strict_types=1);

namespace Codewithkyrian\Web3\Exceptions;

use Exception;

class ChromaDBException extends Exception
{
    /**
     * Creates a new Exception instance.
     *
     * @param array{message: string|array<int, string>, code: string|int|null} $contents
     */
    public function __construct(private readonly array $contents)
    {
        $message = ($contents['message'] ?: (string)$this->contents['code']) ?: 'Unknown error';

        if (is_array($message)) {
            $message = implode("\n", $message);
        }

        parent::__construct($message);
    }

    /**
     * Returns the error message.
     */
    public function getErrorMessage(): string
    {
        return $this->getMessage();
    }

    /**
     * Returns the error code.
     */
    public function getErrorCode(): string|int|null
    {
        return $this->contents['code'];
    }
}