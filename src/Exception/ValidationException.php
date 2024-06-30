<?php

declare(strict_types=1);

namespace Serato\InvoiceQueue\Exception;

use RuntimeException;

class ValidationException extends RuntimeException
{
    /** @var Array<mixed> */
    private $validationErrors;

    /**
     * @param Array<mixed> $errors
     */
    public function __construct(array $errors)
    {
        $this->validationErrors = $errors;

        $message = '';
        foreach ($errors as $error) {
            $message .= "\n\n * Property: " . $error['property'] . "\n * Constraint: " .
                        $error['constraint'] . "\n * Message: " . $error['message'];
        }

        $message = "Data does not confirm to JSON schema. Validation failed with the following errors:" . $message;

        parent::__construct($message);
    }

    /**
     * Returns valdidation errors
     *
     * @return Array<mixed>
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
