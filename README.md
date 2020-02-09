# Creates JSON API compliant responses for errors

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nestecha/laravel-json-api-validation.svg?style=flat-square)](https://packagist.org/packages/nestecha/laravel-json-api-validation)
[![Build Status](https://img.shields.io/travis/nestecha/laravel-json-api-validation/master.svg?style=flat-square)](https://travis-ci.org/nestecha/laravel-json-api-validation)
[![Quality Score](https://img.shields.io/scrutinizer/g/nestecha/laravel-json-api-validation.svg?style=flat-square)](https://scrutinizer-ci.com/g/nestecha/laravel-json-api-validation)
[![Total Downloads](https://img.shields.io/packagist/dt/nestecha/laravel-json-api-validation.svg?style=flat-square)](https://packagist.org/packages/nestecha/laravel-json-api-validation)

This package helps returning JSON API compliant errors while using the native Laravel validation logic.
Also, it lets you add unique codes to your validation rules which makes it easier on the consumer end.

## Installation

You can install the package via composer:

```bash
composer require nestecha/laravel-json-api-validation
```

Go in `App\Exceptions\Handler.php` and change the `render` method :
``` php
/**
 * Render an exception into an HTTP response.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  \Exception  $exception
 * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
 */
public function render($request, Exception $exception)
{
    if ($exception instanceof \Nestecha\LaravelJsonApiValidation\Exception\JsonApiValidationException) {
        $responseFactory = new \Nestecha\LaravelJsonApiValidation\ResponseFactory();
        return $responseFactory->fromErrors($exception->errors()->getArrayCopy());
    }

    return parent::render($request, $exception);
}
```

Then, in your controller :
``` php
public function home(Request $request)
{
    $validator = new \Nestecha\LaravelJsonApiValidation\JsonApiValidator();
    $validator->validateAsJsonApi($request->all(), ['title' => 'required']);

    // ...
}
```

This would yield :
``` json
{
    "errors": [
        {
            "status": "422",
            "title": "Unprocessable Entity",
            "detail": "The title field is required.",
            "source": {
                "pointer": "\/data\/attributes\/title"
            },
            "meta": {
                "failed": {
                    "rule": "required"
                }
            }
        }
    ]
}
```
### For Laravel :
To add a code to the errors, use this artisan command to copy the default config file to your config folder.
```bash
php artisan vendor:publish --tag=config
```

### For Lumen :
To add a code to the errors, [a base config file is available](https://github.com/Nestecha/laravel-json-api-validation/blob/master/config/config.php), simply copy paste it into your config folder named `json-api-validation.php`.
Then in `bootstrap/app.php` add this line :

``` php
$app->configure('json-api-validation');
```

Then fill the config file with codes for the rules you need :

``` php
return [
    'required' => ['code' => 'VALIDATION_ERROR_REQUIRED']
];
```

### To customize the config filename

`json-api-validation.php` is the default config filename. You can customize the validator by passing a string in the constructor :

``` php
public function home(Request $request)
{
    $validator = new JsonApiValidator('name-of-your-config-file');
    $validator->validateAsJsonApi($request->all(), ['title' => 'required']);
}
``` 

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email steve@kang.fr instead of using the issue tracker.

## Credits

- [Chamaillard Steve](https://github.com/nestecha)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.