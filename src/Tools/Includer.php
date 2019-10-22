<?php

namespace EzVpc\Tools;

class Includer
{
    /**
     * Include all PHP files within a folder and subfolders.
     *
     * @param string $directory
     * @return void
     */
    public static function recursivelyInclude(string $directory)
    {
        if(is_dir($directory)) {
            $scan = scandir($directory);
            unset($scan[0], $scan[1]); //Ignore . and ..
            foreach($scan as $file) {
                $filePath = "{$directory}/{$file}";
                if (is_dir($filePath)) {
                    self::recursivelyInclude($filePath);
                } elseif (substr($file, -4) === '.php') {
                    include_once($filePath);
                }
            }
        }
    }
}
