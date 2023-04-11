<?php

namespace Nestecha\LaravelJsonApiValidation;

use CloudCreativity\LaravelJsonApi\Validation\Validator as CloudCreativityValidator;

class Validator extends CloudCreativityValidator
{
    public function getTranslator()
    {
        return $this->translator;
    }
}
