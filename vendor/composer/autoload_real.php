<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInite78624dc21f8ab96641b8f0f69088389
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInite78624dc21f8ab96641b8f0f69088389', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInite78624dc21f8ab96641b8f0f69088389', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInite78624dc21f8ab96641b8f0f69088389::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
