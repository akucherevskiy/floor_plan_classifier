<?php

namespace App;

class FileLoader
{
    /** @var string */
    const FILE_PATH = "data/images.csv";

    /**
     * @param $chunkSize
     * @param $callback
     * @return bool
     */
    static function fileGetContentsChunked($chunkSize, $callback)
    {
        try {
            $handle = fopen(self::FILE_PATH, "r");
            $i = 0;
            while (!feof($handle)) {
                call_user_func_array($callback, [fread($handle, $chunkSize), &$handle, $i]);
                $i++;
            }
            fclose($handle);
        } catch (\Exception $e) {
            trigger_error("file_get_contents_chunked::" . $e->getMessage(), E_USER_NOTICE);
            return false;
        }

        return true;
    }
}