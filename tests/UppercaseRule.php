<?php

namespace Nestecha\LaravelJsonApiValidation\Tests;

use Illuminate\Contracts\Validation\Rule;

use function strtoupper;

class UppercaseRule implements Rule
{
    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        return strtoupper($value) === $value;
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return "The :attribute should be uppercase.";
    }
}