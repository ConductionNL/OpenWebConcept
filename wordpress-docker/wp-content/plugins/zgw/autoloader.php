<?php

namespace OWC\ZGW;

class Autoloader
{
    /**
     * Autoloader constructor.
     * PSR autoloader.
     */
    public function __construct()
    {
        spl_autoload_register(function (string $className) {
            $baseDir    = __DIR__ . '/src/';
            $namespace  = str_replace('\\', '/', __NAMESPACE__);
            $className  = str_replace('\\', '/', $className);
            $class      = $baseDir . (empty($namespace) ? '' : $namespace . '/') . $className . '.php';
            $class      = str_replace('/OWC/ZGW/OWC/ZGW/', '/ZGW/', $class);

            if (file_exists($class)) {
                require_once $class;
            }
        });
    }
}
