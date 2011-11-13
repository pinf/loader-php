PHP Module, Package & Program Loader
====================================

*Status: DEV* - **WORK IN PROGRESS: YOU MAY GET FRUSTRATED TRYING THIS!**

The PINF PHP Loader combines what you would traditionally call a **package installer** and 
**class loader** and is **intended to be used as the core to all your PHP applications**.

The loader allows for bootstrapping a state-of-the-art, consistent and portable modular environment
for PHP with the ability to load third party PHP packages from PEAR and other communities that follow
[typical PHP class naming conventions](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md).

The loader brings [CommonJS Packages](http://wiki.commonjs.org/wiki/Packages),
[CommonJS Package Mappings](http://wiki.commonjs.org/wiki/Packages/Mappings) and further concepts to the PHP platform.

The loader implements or is compatible with the following specs:

  * [CommonJS Packages/1.1 (draft)](http://wiki.commonjs.org/wiki/Packages/1.1)
  * [CommonJS Packages/Mappings/C (proposal)](http://wiki.commonjs.org/wiki/Packages/Mappings/C)
  * [PHP Framework Interop Group (FIG): Autoloader interoperability (PSR-0)](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)

For more information about the direction this loader is taking see the [JavaScript implementation](https://github.com/pinf/loader-js).


Install
=======

TODO


Development
===========

[PHPUnit](http://www.phpunit.de/manual/current/en/installation.html) Tests:

    cd ./tests
    
    phpunit PINF


TODO
====

**Questions**

  * How do you tell PHPUnit to disable output buffering when running tests?



Support, Feedback & News
========================

 * Mailing list: [http://groups.google.com/group/pinf-dev/](http://groups.google.com/group/pinf-dev/)
 * Twitter: [http://twitter.com/pinf](http://twitter.com/pinf)
 * Blog: [http://christophdorn.com/Blog/](http://christophdorn.com/Blog/)


Contribute
==========

Collaboration Platform: [https://github.com/pinf/loader-php/](https://github.com/pinf/loader-php/)

Collaboration Process:

  1. Discuss your change on the mailing list
  2. Write a patch on your own
  3. Send pull request on github & ping mailing list
  4. Discuss pull request on github to refine

You must explicitly license your patch by adding the following to the top of any file you modify
in order for your patch to be accepted:

    //  - <GithubUsername>, First Last <Email>, Copyright YYYY, MIT License


Author
======

This project is a part of the [PINF](http://www.christophdorn.com/Research/#PINF) project maintained by
[Christoph Dorn](http://www.christophdorn.com/).


Documentation License
=====================

[Creative Commons Attribution-NonCommercial-ShareAlike 3.0](http://creativecommons.org/licenses/by-nc-sa/3.0/)

Copyright (c) 2011+ [Christoph Dorn](http://www.christophdorn.com/)


Code License
============

[MIT License](http://www.opensource.org/licenses/mit-license.php)

Copyright (c) 2011+ [Christoph Dorn](http://www.christophdorn.com/)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
