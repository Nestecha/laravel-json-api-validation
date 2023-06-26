<?php

namespace Nestecha\LaravelJsonApiValidation;

use Illuminate\Http\JsonResponse;
use Neomerx\JsonApi\Schema\Error;

class ResponseFactory
{

    /**
     * @param  Error[]  $errors
     * @return JsonResponse
     */
    public function fromErrors(array $errors)
    {
        $result = [];

        foreach ($errors as $error) {
            $result[] = collect(
                [
                    'idx' => $error->getId(),
                    'links' => $error->getLinks(),
                    'status' => $error->getStatus(),
                    'code' => $error->getCode(),
                    'title' => $error->getTitle(),
                    'detail' => $error->getDetail(),
                    'source' => $error->getSource(),
                    'meta' => $error->getMeta()
                ]
            )->reject(
                function ($item)
                {
                    return is_null($item);
                }
            )->toArray();
        }

        return response()->json(
            [
                'errors' => $result
            ],
            422
        );
    }
}
