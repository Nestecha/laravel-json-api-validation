<?php

namespace Nestecha\LaravelJsonApiValidation\Tests;

use CloudCreativity\LaravelJsonApi\LaravelJsonApi;
use Neomerx\JsonApi\Schema\Error;
use Nestecha\LaravelJsonApiValidation\Exception\JsonApiValidationException;
use Nestecha\LaravelJsonApiValidation\JsonApiValidator;
use Nestecha\LaravelJsonApiValidation\LaravelJsonApiValidationServiceProvider;
use Orchestra\Testbench\TestCase;

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
            $this->assertEquals(['pointer' => '/data/attributes/title', 'value' => ''], $error->getSource());
            $this->assertEquals(['failed' => ['rule' => 'required']], $error->getMeta());
        }
    }

    /** @test */
    public function the_error_should_contain_the_value_of_the_field_erroring_out_in_the_source()
    {
        try {
            $this->validator->validateAsJsonApi(['title' => 'lorem 1234'], ['title' => 'max:3']);
        } catch (JsonApiValidationException $exception) {
            /** @var Error $error */
            $error = $exception->errors()[0];

            $this->assertEquals(['pointer' => '/data/attributes/title', 'value' => 'lorem 1234'], $error->getSource());
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

    /** @test */
    public function custom_error_messages_should_change_the_detail_but_not_the_code()
    {
        $validator = new JsonApiValidator();

        config(['json-api-validation' => ['required' => ['code' => 'ERROR_CODE_REQUIRED']]]);

        try {
            $validator->validateAsJsonApi([], ['title' => 'required'], ['required' => 'The :attribute is custom.']);
        } catch (JsonApiValidationException $exception) {
            /** @var Error $error */
            $error = $exception->errors()[0];

            $this->assertEquals('ERROR_CODE_REQUIRED', $error->getCode());
            $this->assertEquals('The title is custom.', $error->getDetail());
        }
    }

    /** @test */
    public function custom_error_attributes_should_change_the_field_translation_but_not_the_error_code()
    {
        $validator = new JsonApiValidator('custom-validation-name');

        config(['custom-validation-name' => ['required' => ['code' => 'ERROR_CODE_REQUIRED']]]);

        try {
            $validator->validateAsJsonApi([], ['title' => 'required'], [], ['title' => 'Taytle']);
        } catch (JsonApiValidationException $exception) {
            /** @var Error $error */
            $error = $exception->errors()[0];

            $this->assertEquals('ERROR_CODE_REQUIRED', $error->getCode());
            $this->assertEquals('The Taytle field is required.', $error->getDetail());
        }
    }

    /** @test */
    public function a_custom_rule_is_named_as_kebab_case_in_the_failed_meta()
    {
        $validator = new JsonApiValidator();

        try {
            $validator->validateAsJsonApi(['title' => 'lowercase'], ['title' => [new UppercaseRule()]]);
        } catch (JsonApiValidationException $exception) {
            /** @var Error $error */
            $error = $exception->errors()[0];

            $this->assertEquals('uppercase-rule', $error->getMeta()['failed']['rule']);
        }
    }

    /** @test */
    public function a_custom_rule_error_code_in_config_uses_kebab_name()
    {
        $validator = new JsonApiValidator();

        config(['json-api-validation' => ['uppercase-rule' => ['code' => 'ERROR_CODE_UPPERCASE']]]);

        try {
            $validator->validateAsJsonApi(['title' => 'lowercase'], ['title' => [new UppercaseRule()]]);
        } catch (JsonApiValidationException $exception) {
            /** @var Error $error */
            $error = $exception->errors()[0];

            $this->assertEquals('ERROR_CODE_UPPERCASE', $error->getCode());
        }
    }

    /** @test */
    public function a_custom_rule_should_return_its_message_in_the_detail()
    {
        $validator = new JsonApiValidator();

        try {
            $validator->validateAsJsonApi(['title' => 'lowercase'], ['title' => [new UppercaseRule()]]);
        } catch (JsonApiValidationException $exception) {
            /** @var Error $error */
            $error = $exception->errors()[0];

            $this->assertEquals("The title should be uppercase.", $error->getDetail());
        }
    }
}
