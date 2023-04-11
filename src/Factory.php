<?php

namespace Nestecha\LaravelJsonApiValidation;

use CloudCreativity\LaravelJsonApi\Contracts\Validation\ValidatorInterface;
use CloudCreativity\LaravelJsonApi\Factories\Factory as CloudCreativityFactory;
use Illuminate\Contracts\Translation\Translator as IlluminateTranslator;

class Factory extends CloudCreativityFactory
{
    public function createErrorTranslator(): Translator
    {
        return new Translator(
            $this->container->make(IlluminateTranslator::class)
        );
    }

    public function createValidator(
        array    $data,
        array    $rules,
        array    $messages = [],
        array    $customAttributes = [],
        \Closure $callback = null
    ): ValidatorInterface
    {
        $translator = $this->createErrorTranslator();

        return new Validator(
            $this->makeValidator($data, $rules, $messages, $customAttributes),
            $translator,
            $callback
        );
    }
}
