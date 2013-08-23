<?php

namespace malkusch\autoloader\example;

class ExceptionB extends Exception
{

    use ClassConstructorTrait;

}

echo "ExceptionB loaded.\n";