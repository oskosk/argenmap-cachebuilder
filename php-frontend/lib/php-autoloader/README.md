PHP-Autoloader
==============

The Autoloader works out of the box as simple as possible. You have
nothing more to do than include the file autoloader.php. Don't bother the
time it consumes when it's called the first time. Let it build its index.
The second time it will run as fast as light.

The simplest and probably most common use case shows this example:

*./index.php*

    <?php
    require __DIR__ . "/autoloader/autoloader.php";
    $myObject = new MyClass();

*./classes/MyClass.php*

    <?php
    class MyClass extends MyParentClass
    {
    
    }

*./classes/MyParentClass.php*

    <?php
    class MyParentClass
    {
    
    }
    
    
As you can see it's only necessary to require autoloader.php once.
If this is done in the document root of your classes (*index.php* in
this case) the Autoloader is already configured. After requiring
this file you don't have to worry where your classes reside.

Another use case would have the class path outside of your document root.
As the autoloader has no knowledge of this arbitrary path you have to
tell him where your class path is:

    <?php

    use malkusch\autoloader\Autoloader;

    require __DIR__ . "/autoloader/autoloader.php";
    
    // As the guessed class path is wrong you should remove this Autoloader.
    Autoloader::getRegisteredAutoloader()->remove();
    
    // register your arbitrary class path
    $autoloader = new Autoloader($classpath);
    $autoloader->register();
    
    // You might also have more class paths
    $autoloader2 = new Autoloader($classpath2);
    $autoloader2->register();

If you have the possibility to enable PHP's tokenizer you should do
this. Otherwise the Autoloader has to use a parser based on PCRE
which is not as reliable as PHP's tokenizer.

The Autoloader assumes that a class name is unique. If you have classes with
equal names the behaviour is undefined.

Generating a portable autoloader
================================

`autoloader/bin/autoloader-build.php` can generate a portable autoloader for
your project. Specify each class path with the parameter `--classpath`. If you
have more class paths you have to use the parameter more times. The parameter
`--deploypath` tells the script where the portable autoloader and its index
files will be stored. Afterwards your project can use the generated autoloader.
In the deploy path you will find an autoloader.php. Your project must include
this file.

Example:

Your project looks like this:

* ./project/index.php
* ./project/classes/\*.php
* ./project/moreClasses/\*.php

You want to deploy a portable autoloader in project/autoloader for the class
paths project/classes and project/moreClasses:

    # autoloader-build.php \
        --classpath project/classes \
        --classpath project/moreClasses \
        --deploypath project/autoloader

This generates a portable autoloader in project/autoloader/autoloader.php. You
have to include this autoloader in your index.php:

    <?php
    include __DIR__ . "/autoloader/autoloader.php";

Now index.php find's all classes.

Note: This generated portable autoloader is a very small autoloader. It's
nothing more than a stored index off all your classes during generation time.
You have to regenerate the autoloader each time you change something in your
class path.
