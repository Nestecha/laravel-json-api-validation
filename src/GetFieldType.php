<?php

namespace Nestecha\LaravelJsonApiValidation;

use Illuminate\Validation\Concerns\FormatsMessages;
use Illuminate\Validation\Validator;

class GetFieldType extends Validator
{
    use FormatsMessages;

    /**
     * @var array
     */
    protected $data;
    /**
     * @var array
     */
    protected $rules;

    public function __construct($data = [], $rules = [])
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public function getType($attribute)
    {
        return $this->getAttributeType($attribute);
    }

    public function hasRule($attribute, $rules)
    {
        return validator($this->data, $this->rules)->hasRule($attribute, $rules);
    }
}