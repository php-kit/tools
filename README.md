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

## License

This library is open-source software licensed under the [MIT license](http://opensource.org/licenses/MIT).

Copyright &copy; 2015 Impactwave, Lda.
