<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc265a4601488f8b5be1bd5ff372473b4
{
    public static $classMap = array (
        'WC_Product_Etsy' => __DIR__ . '/../..' . '/app/WC_Product_Etsy.php',
        'codeable_etsy_product\\admin\\EtsyProductAdmin' => __DIR__ . '/../..' . '/app/admin/EtsyProductAdmin.php',
        'codeable_etsy_product\\etsy\\EtsyAPI' => __DIR__ . '/../..' . '/app/etsy/EtsyAPI.php',
        'codeable_etsy_product\\frontend\\EtsyProduct' => __DIR__ . '/../..' . '/app/frontend/EtsyProduct.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInitc265a4601488f8b5be1bd5ff372473b4::$classMap;

        }, null, ClassLoader::class);
    }
}
