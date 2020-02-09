<?php

namespace Nestecha\LaravelJsonApiValidation\Exception;

use CloudCreativity\LaravelJsonApi\Validation\Validator;
use Illuminate\Validation\ValidationException;

class JsonApiValidationException extends ValidationException
{
    /**
     * The validator instance.
     *
     * @var Validator
     */
    public $validator;

    public function errors()
    {
        return $this->validator->getErrors();
    }
}