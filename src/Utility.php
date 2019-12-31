<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */
namespace Laminas\ApiTools\Admin;

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
            if ($obj == '.' || $obj == '..') {
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
