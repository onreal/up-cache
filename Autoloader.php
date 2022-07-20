<?php
class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            $prefix = 'Upio\\UpCache\\';
            $len = strlen($prefix);
            // make sure that we include only our prefixed namespace
            if (strncmp($prefix, $class, $len) !== 0) return;
            $className = substr($class, $len);
            $file = UP_CACHE_INC_PATH . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
            if (file_exists($file)) { require $file;}
        });
    }
}
Autoloader::register();