<?php

namespace malkusch\autoloader\example;

trait ClassConstructorTrait
{
    
    static public function classConstructor()
    {
        echo "calling class constructor for class ", __CLASS__, ".\n";
    }
    
}

echo "ClassConstructorTrait loaded.\n";