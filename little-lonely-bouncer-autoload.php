<?php
/**
 * Little Lonely Bouncer
 */
spl_autoload_register(function($classname) {

    if (strpos($classname, 'LittleLonelyBouncer') !== false) {

        $path = __DIR__.'/src/';

        $file = explode('\\', $classname);

        if (count($file) > 2) $path .= $file[count($file) - 2].'/';

        $file = $file[count($file) - 1].'.php';

        if (file_exists($path.$file)) require_once $path.$file;

    }

});
