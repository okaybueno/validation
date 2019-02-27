# ValidationService

A package that provides validation as a service for Laravel 5.x.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/okaybueno/validation.svg?style=flat-square)](https://packagist.org/packages/okaybueno/validation)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Quality Score](https://img.shields.io/scrutinizer/g/okaybueno/validation.svg?style=flat-square)](https://scrutinizer-ci.com/g/okaybueno/validation)
[![Total Downloads](https://img.shields.io/packagist/dt/okaybueno/validation.svg?style=flat-square)](https://packagist.org/packages/okaybueno/validation)


## Goal

There is a lot of talk about where to perform the data validation: repositories? controllers? gateways? services? models?
Each one has its point; good things and bad things... We personally found useful to extract this logic to a service that can 
be injected into other services on the same -or higher- layer. 

So the goal of this package is to provide a simple validation service that can be injected into other services, and that 
although it uses the Laravel Validation class by default, it can be extended to use other validation libraries. This is a 
highly opinionated way of solving this issue.


## Installation

1. Install this package by adding it to your `composer.json` or by running `composer require okaybueno/validation-service` in your project's folder.
2. Publish the configuration file by running `php artisan vendor:publish --provider="OkayBueno\Validation\ValidationServiceProvider"`.
3. Configure the `config/validators.php` file according to your needs, specifying the base namespace and directories for your validators.
4. Ready to go! No service provider or anything else needed :).


## Usage

You just need to create a Validation interface with the different validation methods, and then create a new ´src´  directory 
where you will then create the validation class that extends the main validation class (`LaravelValidator`) and implements 
the previous interface. It sounds weird, huh? Lets see it with an example...


## Examples

I personally like to split my Laravel code from my app code, so inside the app folder I usually create a folder that includes
ALL the business logic that is lowly coupled to the framework: app/MyWebApp. Inside that folder I like to split my files into 
different folders, based on the role of these: Models? Repositories? Helpers? Services? Validators? And so on...

Once again -and this is a matter of taste, that's all- I like to split my services into areas of responsibility: Users, Auth,
Mailing, etc. Therefore, I like to split my validators in different folders that then contain the different interfaces,
along with the `src` folder that contains the implementation. So the folder structure inside my projects looks pretty 
much like this most of the times:

```
+-- app
|   +-- MyApp
|       +-- Models
|       +-- Helpers
|       +-- Repositories
|       .
|       .
|       +-- Validation
|           +-- Auth
|           +-- Mailing
|           +-- Users
|               +-- UserValidatorInterface.php
|               +-- src
                    +-- UsersValidator.php
```

Let's take a look at how I would implement the UserValidatorInterface and the UserValidator classes:

```php

<?php

namespace MyApp\Validators\Users;

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

namespace MyApp\Validators\Users\src;

use OkayBueno\Validation\src\LaravelValidator;

class UserValidator extends LaravelValidator implements UserValidatorInterface
{   

    public function existsById()
    {
        return [
            'id'    => 'required|exists:users,id'
        ];
    }
    
    public function existsByEmail()
    {
        return [
            'email'    => 'required|exists:users,email'
        ];
    }
}

```

Vôila! The package does the rest. Now you can inject the Validation service in any part of our app. 
I like to use ONLY inside other services, something like this...


```php

<?php

namespace MyApp\Services\Frontend\Users\src;

use MyApp\Validators\Users\UsersValidatorInterface;

class UsersService implements UsersServicesInterface 
{

    protected $usersValidator;

    public function __construct(
        UsersValidatorInterface $usersValidatorInterface
    )
    {
        $this->usersValidator = $usersValidatorInterface;
    }
   
    
    public function findUserById( $userId )
    {
        $data = [
            'id' => $userId
        ]
        
        if ( $this->usersValidator->with( $data )->passes( UsersValidatorInterface::EXISTS_BY_ID ) )
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

And that's all! Remember: The important thing is that the interface lives in the folder specified in the configuration file,
 and that the implementation for that interface lives under the `src` folder.

## Changelog

##### v2.0.0:
- New (breaking) version. Instead of having validators tied to services, now they live in their own folder and are automatically
bound when installing the package.

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
