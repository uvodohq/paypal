<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdfbfc21e28f97f5373042652c9f948b6
{
    public static $prefixLengthsPsr4 = array (
        'U' => 
        array (
            'Uvodo\\Paypal\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Uvodo\\Paypal\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitdfbfc21e28f97f5373042652c9f948b6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdfbfc21e28f97f5373042652c9f948b6::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitdfbfc21e28f97f5373042652c9f948b6::$classMap;

        }, null, ClassLoader::class);
    }
}
