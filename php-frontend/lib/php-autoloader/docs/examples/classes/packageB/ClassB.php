<?php

namespace malkusch\autoloader\example;

class ClassB implements InterfaceB
{

    use ClassConstructorTrait;

} 

echo "ClassB loaded.\n";
