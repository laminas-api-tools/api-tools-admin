<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin;

use function closedir;
use function opendir;
use function readdir;
use function rmdir;
use function unlink;

class Utility
{
    /**
     * Recursive delete
     *
     * @param  string $dir
     * @return bool
     */
    public static function recursiveDelete($dir)
    {
        if (false === ($dh = @opendir($dir))) {
            return false;
        }

        while (false !== ($obj = readdir($dh))) {
            if ($obj === '.' || $obj === '..') {
                continue;
            }
            if (! @unlink($dir . '/' . $obj)) {
                self::recursiveDelete($dir . '/' . $obj);
            }
        }

        closedir($dh);
        @rmdir($dir);
        return true;
    }
}
