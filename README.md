# Laravalerors

Provides Rule Name & Parameter to Laravel REST API Validation Errors.

## Eh... What??

Well, during validation error in API, instead of getting 422 validation error response like this:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": [
            "The name field is required."
        ],
        "age": [
            "The age field must be at least 18."
        ]
    }
}
```

You will get response like below:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name" : [
            {
                "rule": "required",
                "options": [],
                "message": "The name field is required."
            }
        ],
        "age" : [
            {
                "rule": "min",
                "options": ["18"],
                "message": "The age field must be at least 18"
            }
        ]
    }
}
```

For each error, you get the rule name and it's parameters.

## Ok... Why would I need?

If your consumers/clients wants to use different message for each rule than the one Laravel provides, they can now use the rule name and paramaters to provide their own message to their customers.

# Requirement

* Laravel 10.0+

# Install (composer)

```
composer require a-h-abid/laravalerors
```

## Configure

Add below code to `app/Exceptions/Handler.php` file.

```php
use Illuminate\Validation\ValidationException;

// ...

    /**
     * @inheritDoc
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'message' => $exception->getMessage(),
            'errors' => $exception->errorsForApi(),
        ], $exception->status);
    }
```

# Usage

Now just make an API endpoint with validation rules, hit it using your favourite REST API Client (Postman, Insomnia etc.) and see the result.

## How it works

For each rule error, the matching rule name and it's paramaters are returned. We also return the rule error message so consumer/client can understand to error better.

- For Laravel built-in validations, rule names are returned in lowercase format, unless it is a class based rule.
- For Class Based validation, we take the class's short name and convert it to lower-dash-cased. However,
    - If it has `public const RULE_NAME` **(string)**, then this rule name will be taken.
    - If it has `public const RULE_PARAMS` **(string[] (array))**, then this rule parameters will be taken.
- For Closure Based validation. you will always get rule name "closure-validation-rule", as there is no way to customize it currently.

## Custom Rule Class Sample

```php
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InCategory implements ValidationRule
{
    /** @var string */
    public const RULE_NAME = 'in-category';

    /** @var array<int,string> */
    public const RULE_PARAMS = ['a', 'b', 'c'];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array($value, static::RULE_PARAMS)) {
            $fail('The :attribute you provided is not within the acceptable options.');
        }
    }
}
```

# License

See [License](/LICENSE).
