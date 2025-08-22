<?php

use Concrete\Core\Package\PackageService;

define('BC_ROOT_DIR', rtrim(str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__)), '/'));

const C5_ENVIRONMENT_ONLY = true;
if (is_link('/app/packages/blocks_cloner') && readlink('/app/packages/blocks_cloner') === BC_ROOT_DIR) {
    define('DIR_BASE', '/app');
} else {
    define('DIR_BASE', rtrim(str_replace(DIRECTORY_SEPARATOR, '/', dirname(dirname(BC_ROOT_DIR))), '/'));
}

require_once DIR_BASE . '/concrete/dispatcher.php';
@stream_wrapper_restore('phar');

if (!app()->isInstalled()) {
    fwrite(STDERR, "Concrete must be installed in order to perform tests.\n");
    exit(1);
}

if (!in_array('blocks_cloner', app(PackageService::class)->getInstalledHandles(), true)) {
    fwrite(STDERR, "The Blocks Cloner package must be installed in order to perform tests.\n");
    exit(1);
}

spl_autoload_register(
    static function ($className) {
        if (strpos($className, 'BlocksCloner\Tests\\') !== 0) {
            return;
        }
        $relPath = str_replace('\\', '/', substr($className, strlen('BlocksCloner\Tests\\'))) . '.php';
        $absPath = BC_ROOT_DIR . '/test/src/' . $relPath;
        if (is_file($absPath)) {
            require_once $absPath;
        } else {
            $absPath = BC_ROOT_DIR . '/test/tests/' . $relPath;
            if (is_file($absPath)) {
                require_once $absPath;
            }
        }
    }
);
