<?php

use Neomerx\JsonApi\Document\Error as JsonError;
use Nestecha\LaravelJsonApiValidation\ResponseFactory;
use Orchestra\Testbench\TestCase;

class CreateResponseTest extends TestCase
{
    /** @test */
    public function it_renders_a_json_api_error_as_compliant_json_api_errors()
    {
        $error = new JsonError(
            'ERROR_UNIQUE_IDENTIFIER',
            null,
            422,
            'ERROR_CODE',
            "Unprocessable Entity",
            "The title field is required.",
            ['pointer' => "/data/attributes/title"],
            ['failed' => ['rule' => 'required']]
        );

        $factory = new ResponseFactory();
        $result = $factory->fromErrors([$error]);

        $expectedData = [
            'errors' => [
                [
                    'idx' => 'ERROR_UNIQUE_IDENTIFIER',
                    'status' => '422',
                    'code' => 'ERROR_CODE',
                    'title' => "Unprocessable Entity",
                    'detail' => "The title field is required.",
                    'source' => ['pointer' => "/data/attributes/title"],
                    'meta' => ['failed' => ['rule' => 'required']]
                ]
            ]
        ];
        $this->assertEquals(response()->json($expectedData, 422), $result);
    }
}