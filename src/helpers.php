<?php declare(strict_types=1);

if(!function_exists('pre_dump')) {
    function pre_dump(mixed $var): void
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }
}
