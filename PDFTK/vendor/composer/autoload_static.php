<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc9a52af5cd1593db4c48e314f2eabc19
{
    public static $prefixLengthsPsr4 = array (
        'm' => 
        array (
            'mikehaertl\\tmp\\' => 15,
            'mikehaertl\\shellcommand\\' => 24,
            'mikehaertl\\pdftk\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'mikehaertl\\tmp\\' => 
        array (
            0 => __DIR__ . '/..' . '/mikehaertl/php-tmpfile/src',
        ),
        'mikehaertl\\shellcommand\\' => 
        array (
            0 => __DIR__ . '/..' . '/mikehaertl/php-shellcommand/src',
        ),
        'mikehaertl\\pdftk\\' => 
        array (
            0 => __DIR__ . '/..' . '/mikehaertl/php-pdftk/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc9a52af5cd1593db4c48e314f2eabc19::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc9a52af5cd1593db4c48e314f2eabc19::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitc9a52af5cd1593db4c48e314f2eabc19::$classMap;

        }, null, ClassLoader::class);
    }
}
