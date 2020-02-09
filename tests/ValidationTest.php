<?php

namespace Nestecha\LaravelJsonApiValidation\Tests;

use CloudCreativity\LaravelJsonApi\LaravelJsonApi;
use Illuminate\Http\Request;
use Neomerx\JsonApi\Document\Error;
use Nestecha\LaravelJsonApiValidation\Exception\JsonApiValidationException;
use Nestecha\LaravelJsonApiValidation\JsonApiValidator;
use Orchestra\Testbench\TestCase;
use Nestecha\LaravelJsonApiValidation\LaravelJsonApiValidationServiceProvider;

class ValidationTest extends TestCase
{
    protected $validator;

    public function setUp(): void
    {
        parent::setUp();

        $this->validator = new JsonApiValidator();
    }

    protected function getPackageProviders($app)
    {
        return [LaravelJsonApiValidationServiceProvider::class];
    }

    /** @test */
    public function validates_throws_a_json_api_validation_exception()
    {
        $this->expectException(JsonApiValidationException::class);

        $this->validator->validateAsJsonApi([], ['title' => 'required']);
    }

    /** @test */
    public function the_exception_contains_json_api_compliant_errors()
    {
        try {
            $this->validator->validateAsJsonApi([], ['title' => 'required']);
        } catch (JsonApiValidationException $exception) {
            /** @var Error $error */
            $error = $exception->errors()[0];

            $this->assertEquals(null, $error->getId());
            $this->assertEquals(null, $error->getLinks());
            $this->assertEquals(422, $error->getStatus());
            $this->assertEquals('Unprocessable Entity', $error->getTitle());
            $this->assertEquals('The title field is required.', $error->getDetail());
            $this->assertEquals(['pointer' => '/data/attributes/title'], $error->getSource());
            $this->assertEquals(['failed' => ['rule' => 'required']], $error->getMeta());
        }
    }

    /** @test */
    public function laravel_json_api_should_go_back_to_the_original_validation_failures_setting()
    {
        LaravelJsonApi::$validationFailures = false;

        try {
            $this->validator->validateAsJsonApi([], ['title' => 'required']);
        } catch (JsonApiValidationException $exception) {
            $this->assertEquals(false, LaravelJsonApi::$validationFailures);
        }

        LaravelJsonApi::$validationFailures = true;

        try {
            $this->validator->validateAsJsonApi([], ['title' => 'required']);
        } catch (JsonApiValidationException $exception) {
            $this->assertEquals(true, LaravelJsonApi::$validationFailures);
        }
    }

    /** @test */
    public function error_should_contain_the_error_code_if_defined_in_config()
    {
        config(['json-api-validation' => ['required' => ['code' => 'ERROR_CODE_REQUIRED']]]);

        try {
            $this->validator->validateAsJsonApi([], ['title' => 'required']);
        } catch (JsonApiValidationException $exception) {
            /** @var Error $error */
            $error = $exception->errors()[0];

            $this->assertEquals('ERROR_CODE_REQUIRED', $error->getCode());
        }

        config(['json-api-validation' => ['max' => ['string' => ['code' => 'ERROR_CODE_MAX_STRING']]]]);

        try {
            $this->validator->validateAsJsonApi(['title' => 'more_than_two_characters'], ['title' => 'max:2']);
        } catch (JsonApiValidationException $exception) {
            /** @var Error $error */
            $error = $exception->errors()[0];

            $this->assertEquals('ERROR_CODE_MAX_STRING', $error->getCode());
        }
    }

    /** @test */
    public function config_name_can_be_changed_in_the_constructor_of_the_validator()
    {
        $validator = new JsonApiValidator('custom-validation-name');

        config(['custom-validation-name' => ['required' => ['code' => 'ERROR_CODE_REQUIRED']]]);

        try {
            $validator->validateAsJsonApi([], ['title' => 'required']);
        } catch (JsonApiValidationException $exception) {
            /** @var Error $error */
            $error = $exception->errors()[0];

            $this->assertEquals('ERROR_CODE_REQUIRED', $error->getCode());
        }
    }
}