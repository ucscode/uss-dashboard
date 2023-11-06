<?php

/**
 * Autoloading Concept:
 * ClassNames Must Be Unique
 */

function recursiveIteratorFactory(string $directory)
{
    return new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator(
            UD_DIR . '/' . $directory,
            \FilesystemIterator::SKIP_DOTS
        )
    );
}

spl_autoload_register(function ($className) {
    $bundleIterator = recursiveIteratorFactory('bundles');
    foreach($bundleIterator as $item) {
        if($className === $item->getBasename('.php')) {
            return require $item->getPathname();
        }
    }
    $srcIterator = recursiveIteratorFactory('src');
    foreach($srcIterator as $item) {
        if($className === $item->getBasename('.php')) {
            return require $item->getPathname();
        }
    }
});
