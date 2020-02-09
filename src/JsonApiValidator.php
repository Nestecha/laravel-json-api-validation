<?php

namespace Nestecha\LaravelJsonApiValidation;

use CloudCreativity\LaravelJsonApi\Factories\Factory;
use CloudCreativity\LaravelJsonApi\LaravelJsonApi;
use Illuminate\Validation\ValidationException;
use Neomerx\JsonApi\Document\Error;
use Nestecha\LaravelJsonApiValidation\Exception\JsonApiValidationException;

class JsonApiValidator
{
    /**
     * @var string
     */
    private $configName;

    public function __construct(string $configName = 'json-api-validation')
    {
        $this->configName = $configName;
    }

    /**
     * @param  array  $data
     * @param  array  $rules
     * @throws JsonApiValidationException
     */
    public function validateAsJsonApi(array $data, array $rules)
    {
        $originalValidationFailuresSetting = LaravelJsonApi::$validationFailures;
        LaravelJsonApi::showValidatorFailures();

        /** @var Factory $factory */
        $factory = app(Factory::class);
        $configName = $this->configName;

        $validator = $factory->createValidator(
            $data,
            $rules,
            [],
            [],
            function ($field, $message, $failures) use ($configName)
            {
                $errorCode = null;
                if (!empty(config("{$configName}.{$failures['rule']}.code"))) {
                    $errorCode = config("{$configName}.{$failures['rule']}.code");
                }

                $fieldType = (new GetFieldType())->getType($field);
                if (!empty(config("{$configName}.{$failures['rule']}.$fieldType.code"))) {
                    $errorCode = config("{$configName}.{$failures['rule']}.$fieldType.code");
                }

                return new Error(
                    null,
                    null,
                    422,
                    $errorCode,
                    "Unprocessable Entity",
                    $message,
                    ['pointer' => "/data/attributes/$field"],
                    ['failed' => $failures]
                );
            }
        );

        try {
            $validator->validate();
        } catch (ValidationException $validationException) {
            throw new JsonApiValidationException($validator);
        } finally {
            LaravelJsonApi::$validationFailures = $originalValidationFailuresSetting;
        }
    }
}