<?php
declare(strict_types=1);

namespace Serato\InvoiceQueue;

use JsonSchema\SchemaStorage;
use JsonSchema\Validator;
use JsonSchema\Constraints\Factory;
use Serato\InvoiceQueue\Exception\JsonEncodeException;
use Serato\InvoiceQueue\Exception\JsonDecodeException;
use Exception;

/**
 * ** Invoice Validator **
 *
 * Functionality for validating invoice data against a JSON schema.
 */
class InvoiceValidator
{
    /** @var string */
    private $schemaFilePath;

    /** @var array */
    private $schemaObjects;

    /** @var array */
    private $validators;

    public function __construct()
    {
        $schemaPath = realpath(__DIR__ . '/../resources/invoice_schema.json');
        if (!$schemaPath || !file_exists($schemaPath)) {
            # This should never happen :-)
            throw new Exception('Unable to load JSON schema file');
        }
        
        $this->schemaFilePath = 'file://' . $schemaPath;
    }

    /**
     * Validates an array against the schema.
     *
     * @param array $data
     * @param string|null $definition
     * @return bool
     *
     * @throws JsonEncodeException
     */
    public function validateArray(array $data, ?string $definition = null): bool
    {
        # It might seem silly to call json_encode() here and json_decode() in self::validateString
        # But this is the simplest way to ensure that we correctly transpose array hashes into stdclass
        # objects that the JSON schema validator requires.
        $json = json_encode($data);
        if ($json === false) {
            throw new JsonEncodeException;
        }
        
        return $this->validateString($json, $definition);
    }

    /**
     * Validates a JSON string against the schema.
     *
     * @param string $json
     * @param string|null $definition
     * @return bool
     *
     * @throws JsonDecodeException
     */
    public function validateString(string $json, ?string $definition = null): bool
    {
        $obj = json_decode($json);
        if ($obj === null) {
            throw new JsonDecodeException;
        }

        $this->getValidator($definition)->validate($obj, $this->getSchemaObject($definition));
        return $this->getValidator($definition)->isValid();
    }

    /**
     * Returns error from the most recent self::validate execution
     *
     * @param string|null $definition
     * @return array
     */
    public function getErrors(?string $definition): array
    {
        return $this->getValidator($definition)->getErrors();
    }

    /**
     * Returns a Validator instance using a schema object with an optional
     * reference to a definition within the source schema document.
     *
     * @param string|null $ref
     * @return Validator
     */
    private function getValidator(?string $ref): Validator
    {
        if ($ref === null) {
            $ref = '';
        }
        if (!isset($this->validators[$ref])) {
            $schemaStorage = new SchemaStorage();
            $schemaStorage->addSchema('data://', $this->getSchemaObject($ref));
            $this->validators[$ref] = new Validator(new Factory($schemaStorage));
        }
        return $this->validators[$ref];
    }

    /**
     * Returns a schema object with an optional reference to a definition
     * within the source schema document.
     *
     * @param string|null $ref
     * @return object
     */
    private function getSchemaObject(?string $ref)
    {
        if ($ref === null) {
            $ref = '';
        }
        if (!isset($this->schemaObjects[$ref])) {
            $this->schemaObjects[$ref] = (object)['$ref' => $this->schemaFilePath];
            if ($ref !== '') {
                $this->schemaObjects[$ref] = (object)['$ref' => $this->schemaFilePath . '#definitions/' . $ref];
            }
        }
        return $this->schemaObjects[$ref];
    }
}
