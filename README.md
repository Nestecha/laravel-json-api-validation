# Creates JSON API compliant responses for errors

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nestecha/laravel-json-api-validation.svg?style=flat-square)](https://packagist.org/packages/nestecha/laravel-json-api-validation)
[![Build Status](https://img.shields.io/travis/nestecha/laravel-json-api-validation/master.svg?style=flat-square)](https://travis-ci.org/nestecha/laravel-json-api-validation)
[![Quality Score](https://img.shields.io/scrutinizer/g/nestecha/laravel-json-api-validation.svg?style=flat-square)](https://scrutinizer-ci.com/g/nestecha/laravel-json-api-validation)
[![Total Downloads](https://img.shields.io/packagist/dt/nestecha/laravel-json-api-validation.svg?style=flat-square)](https://packagist.org/packages/nestecha/laravel-json-api-validation)

This package helps returning JSON API compliant errors while using the native Laravel validation logic.
Also, it lets you add unique codes to your validation rules which makes it easier on the consumer end.

## Laravel / Lumen Versions

| Laravel / Lumen | This Package |
| --- | --- |
| `^8.0` | `^3.0` |
| `^7.0` | `^2.0` |
| `^6.0` | `^1.0` |

## Installation

You can install the package via composer:

- Laravel / Lumen 6
```bash
composer require "nestecha/laravel-json-api-validation":"^1.0"
```

- Laravel / Lumen 7
```bash
composer require "nestecha/laravel-json-api-validation":"^2.0"
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
                "pointer": "\/data\/attributes\/title",
                "value": ""
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
To add a code to the errors, [a base config file is available](https://github.com/Nestecha/laravel-json-api-validation/blob/master/config/config.php), simply copy paste it into your config folder as `json-api-validation.php`.
Then in `bootstrap/app.php` add this line :

``` php
$app->configure('json-api-validation');
```

### To customize the config filename :

`json-api-validation.php` is the default config filename. You can customize the validator by passing a string in the constructor :

``` php
public function home(Request $request)
{
    $validator = new JsonApiValidator('name-of-your-config-file');
    $validator->validateAsJsonApi($request->all(), ['title' => 'required']);
}
``` 

### For custom rules :

When using Laravel [custom rules](https://laravel.com/docs/master/validation#using-rule-objects) :

``` php
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
```

To add an error code in the config, you should use the name in kebab-case format :

``` php
return [
    'uppercase-rule' => ['code' => 'VALIDATION_ERROR_UPPERCASE'],
];
```

The error will format the rule name in kebab-case in the meta field :

``` json
{
    "errors": [
        {
            "status": "422",
            "title": "Unprocessable Entity",
            "detail": "The title should be uppercase.",
            "source": {
                "pointer": "\/data\/attributes\/title",
                "value": "lowercase_title"
            },
            "meta": {
                "failed": {
                    "rule": "uppercase-rule"
                }
            }
        }
    ]
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