<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite81f425151f147d0cce92ed10bee79cb
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite81f425151f147d0cce92ed10bee79cb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite81f425151f147d0cce92ed10bee79cb::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite81f425151f147d0cce92ed10bee79cb::$classMap;

        }, null, ClassLoader::class);
    }
}
