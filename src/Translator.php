<?php

namespace Nestecha\LaravelJsonApiValidation;

use CloudCreativity\LaravelJsonApi\Document\Error\Translator as CloudCreativityTranslator;

class Translator extends CloudCreativityTranslator
{
    public function get($key, array $replace = [], $locale = null)
    {
        return $this->translator->get($key, $replace, $locale);
    }
}
