# ValidationService

A package that provides validation as a service for Laravel 5.x.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/okaybueno/validation.svg?style=flat-square)](https://packagist.org/packages/okaybueno/validation)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Quality Score](https://img.shields.io/scrutinizer/g/okaybueno/validation.svg?style=flat-square)](https://scrutinizer-ci.com/g/okaybueno/validation)
[![Total Downloads](https://img.shields.io/packagist/dt/okaybueno/validation.svg?style=flat-square)](https://packagist.org/packages/okaybueno/validation)


## Disclaimer

This package was originally released [here](https://github.com/followloop/validation-service), but since the future 
of that package is not clear, it has been forked and re-worked under this repository.

## Goal

There is a lot of talk about where to perform the data validation: repositories? controllers? gateways? services? models?
Each one has its point; good things and bad things... We personally found useful to extract this logic to a service that can 
be injected into other services on the same -or higher- layer.

So the goal of this package is to provide a simple validation service that can be injected into other services, and that 
although it uses the Laravel Validation class by default, it can be extended to use other validation libraries.

## Installation

1. Install this package by adding it to your `composer.json` or by running `composer require okaybueno/validation-service` in your project's folder.
2. Ready to go! No service provider or anything else needed :).


## Usage

You just need to create a Validation class that extends the main validation class (`LaravelValidator`) and implements
their own methods to validate the data. It sounds weird, huh? Lets see an example...


## Examples

I personally like to split my Laravel code from my app code, so inside the app folder I usually create a folder that includes
ALL the business logic that is lowly coupled to the framework: app/MyWebApp. Inside that folder I like to split my files into 
different folders, based on the role of these: Models? Repositories? Helpers? Services? And so on...

Once again -and this is a matter of taste, that's all- I like to split my services into areas of responsibility: Users, Auth,
Mailing, etc. And then, in that service folders, I like to include the validators. So the folder structure inside my projects
looks pretty much like this most of the times:

```
+-- app
|   +-- MyApp
|       +-- Models
|       +-- Helpers
|       +-- Repositories
|       .
|       .
|       +-- Services
|           +-- Auth
|           +-- Mailing
|           +-- Users
|               +-- UsersServicesInterface.php
|               +-- UsersServiceProvider.php
|               .
|               .
|               +-- src
|               +-- Validation
|                   +-- UserValidatorInterface.php
|                   +-- src
|                       +-- LaravelUserValidator.php
```

Let's take a look at how I would implement the UserValidatorInterface and the LaravelUserValidator classes:

```php

<?php

namespace MyApp\Services\Users\Validation;

interface UserValidatorInterface
{
    const EXISTS_BY_ID = 'existsById';
    const EXISTS_BY_EMAIL = 'existsByEmail';
    
    public function existsById();
    public function existsByEmail();
}

```

```php

<?php

namespace MyApp\Services\Users\Validation\src;

use OkayBueno\Validation\src\LaravelValidator;

class LaravelUserValidator extends LaravelValidator implements UserValidatorInterface
{   

    public function existsById()
    {
        return [
            'id'    => 'required|exists:users,id'
        ]
    }
    
    public function existsByEmail()
    {
        return [
            'email'    => 'required|exists:users,email'
        ]
    }
}

```

The service provider for this group of services (`UsersServiceProvider`) should be responsible of providing me with the validator for the user, bind
 to the LaravelUserValidator, something like this:
 
```php

public function register()
{
    // Register other services here...
    
    // Clients validation services
    $this->app->bind( 'MyApp\Services\Users\Validation\UserValidatorInterface', function ( $app )
    {
        return $app->make( 'MyApp\Services\Users\Validation\src\LaravelUserValidator' );
    });
}

```


After this and after adding our service provide to the `config/app.php` file, we can inject the Validation service in any 
part of our app. I like to use ONLY inside other services. Something like this...


```php

<?php

namespace MyApp\Services\Users\src;

use MyApp\Services\Users\Validation\UserValidator;

class UsersService implements UsersServicesInterface 
{

    protected $usersValidator;

    public function __construct(
        UsersValidator $usersValidatorInterface
    )
    {
        $this->usersValidator = $usersValidatorInterface;
    }
   
    
    public function getUserById( $userId )
    {
        $data = [
            'id' => $userId
        ]
        
        if ( $this->usersValidator->with( $data )->passes( UserValidatorInterface::EXISTS_BY_ID ) )
        {
            // It passes the validation, so do whatever.. fetch user in $user and return it (for example).
            
            .
            .
            
            return $user;
        }
        
        // if we are at this point then the validation failed and this will return an array with the errors.
        return $this->usersValidator->errors();
    }
    
}

```

## Changelog

##### v1.0.0:
- First public official version released.

## Credits

- [okay bueno - A fully transparent digital product studio](http://okaybueno.com)
- [Jesús Espejo](https://github.com/jespejoh) ([Twitter](https://twitter.com/jespejo89))

## Bugs & contributing

* Found a bug? That's good (and bad). Let me know using the Issues on Github.
* Need a feature or have something interesting to contribute with? Great! Open a pull request.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.