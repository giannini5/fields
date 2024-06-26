<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit78bb10fbcd53375c82cff6de08fbea47
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'D' => 
        array (
            'DAG\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'DAG\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInit78bb10fbcd53375c82cff6de08fbea47::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit78bb10fbcd53375c82cff6de08fbea47::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit78bb10fbcd53375c82cff6de08fbea47::$classMap;

        }, null, ClassLoader::class);
    }
}
