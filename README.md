# PHP-Kit Tools
##### A toolkit of utility functions for general use

## What is PHP-Kit?

This toolkit assists the developer in writing any kind of PHP code.  
It provides missing functionality from the standard library and adds miscellaneous generic, low-level, 
foundational functions for making your code clearer and more succinct, reducing boilerplate and copy-paste.

## Why PHP-Kit?

There are some recurring functions and patterns that are needed frequently when developing any PHP project.
So, developers frequently copy-paste the same own-made utility/helper functions over and over, from project to project.
  
### The problem

* all that copy-pasted code is hard to organize, update and maintain, especially when being shared between projects;
* different developers (even in the same team and/or in the same project) create their own utility/helper functions, sometimes redundantly duplicating existing code, introducing subtle variations and/or generating confiusion between team mates;
* copy-pasted-modified code is usually badly documented, possibly bug-ridden and, being developer-specific, has no support (from other than the original developer);
* if you use the utility functions provided by a specific framework, you're limiting the reusability of your code and making it harder for you to switch to projects made with other frameworks.

### PHP-Kit is the solution

##### Usable on any PHP project

A consistent set of tools at your disposal, ready for use, on any project.  
All developers on a team/project can share the same functions and write code in a similar way.

##### Framework independant

Work with any framework, always using the same set of base functions.  
Make your code easier to reuse.  
Make it easier to switch to projects made with other frameworks, keeping the *way-you-work*.  

> Some frameworks even use PHP-Kit on their own code!

##### Documented

Most functions already have a *doc-block* (inline documentation), so your IDE should give you inline documentation and parameter validation and autocompletion.

Nevertheless, additional documentation (to be displayed on this Readme or on the project's Wiki) is still being written, whenever we find the time for it.

Are you in a hurry? You can volunteer for writing documentation. We welcome your pull requests ;-)

##### Tested

Automated tests are not implemented yet, but they're comming soon.

Nevertheless, this library is battle-tested on many real-world projects.

##### Supported

Both the project creators and the community will keep this library updated, apply bug-fixes and implement new features, when needed.

##### Evolving

New functions may be added from time to time, or existing functions may be improved, or even removed (if we reach the conclusion that they are not useful enough for the majority of developers and use cases).

> The library will **not** grow endlessly to contain every possible utility/helper function in existence! **We only want the best / most useful / most needed functions here!**

##### But still stable enough

Don't worry about the possibility of your code breaking due to changes on this library. Just make sure you require a specific version of it on your project's `composer.json` file.

> Do not use `dev-master` as your version constraint on `composer.json`!

## Usage

#### Installation

On the command-line, type:

```sh
composer require php-kit/tools
```

#### Runtime requirements

- PHP >= 5.4

#### Code organization

On the project's `src` folder you'll find a separate file for each group of functions, grouped by theme / purpose / scope.

All functions are global, i.e. they have no namespace and they are always accessible anywhere on your application.

Most functions on this library have names that make them seem just like another predefined PHP function, thereby complementing the standard API with the missing functions we wished were there.

For instance, array functions have the `array_` prefix, string functions have the `str_` prefix, and so on.

## FAQ

#### Why are all functions global?

As these are helper / utility / general purpose functions, they can (and will) be used anywhere on the host application, and very frequently.

The library is compatible with PHP 5.4, which means there is no easy way to import the functions into a file's namespace, and there is no autoloading capability (it only works for classes). 

Either we transform these functions into class static methods, which will make calling then more verbose (and destroy the illusion of them being just an extension to the standard PHP global functions), or we raise the PHP version requirement to 5.6.

Even when using PHP >= 5.6, always having to import each and every single function using `use` statements at the top of each source code file, is not the most practical / productive solution. 

#### Aren't globals bad?

If you don't want to use global functions, then this library is not for you. There are other alternatives that will meet your expectations.

Having said that, yes, globals have some problems.

The main disadvantage of using globals is that they *pollute* the global namespace and increase the probability of identifier collisions with functions from other libraries.

Nevertheless, PHP is known for having no shame in polluting the global namespace with thousands of functions, classes, variables and constants. So, we just keep the tradition, and extend it a little more. ;-)

Do note that many of our functions are named with a common prefix. For instance, array functions have the `array_` prefix, string functions have the `str_` prefix, and so on. This also reduces the probability of name collisions.

Also, some functions are wrapped in `if` blocks so that, if a function with the same name is already defined, no error will occur and the original function will be kept unmodified. Of course, in that case, you'll not be able to use the new function with the same name, but at least it will not prevent you from using other functions from this library.  

> This conditional definition is, currently, reserved to functions with very common names, though (ex. `get`).

## License

This library is open-source software licensed under the [MIT license](http://opensource.org/licenses/MIT).

Copyright &copy; 2015 Impactwave, Lda.
