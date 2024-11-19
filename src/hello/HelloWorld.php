<?php declare(strict_types=1);
namespace Smc\hello;

class HelloWorld {

    public static function sayHello(?string $name): string
    {
        $greeting = "Hello World!";

        if (is_string($name)) {
            $greeting = "Hello!";
            if (mb_strlen($name) > 0) {
                $greeting = "Hello $name!";
            }
        }

        return $greeting;
    }
}