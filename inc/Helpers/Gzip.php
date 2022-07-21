<?php

namespace Upio\UpCache\Helpers;

class Gzip
{
    /**
     * GZIPs a file on disk (appending .gz to the name)
     *
     * Based on function by Kioob at:
     * http://www.php.net/manual/en/function.gzwrite.php#34955
     *
     * @param string $source Path to file that should be compressed
     * @param integer $level GZIP compression level (default: 9)
     * @return string New filename (with .gz appended) if success, or false if operation fails
     */
    function gzCompressFile(string $source, int $level = 9) {
        $dest = $source . '.gz';
        $mode = 'wb' . $level;
        $error = false;
        if ($fp_out = gzopen($dest, $mode)) {
            if ($fp_in = fopen($source,'rb')) {
                while (!feof($fp_in))
                    gzwrite($fp_out, fread($fp_in, 1024 * 512));
                fclose($fp_in);
            } else {
                $error = true;
            }
            gzclose($fp_out);
        } else {
            $error = true;
        }
        if ($error)
            return false;
        else
            return $dest;
    }

    public static function isGzipEnabled (): bool
    {
        $isGzip = true;
        // Check PHP gzip support
        if ( ( !function_exists( 'ob_gzhandler' )
            || !ini_get( 'zlib.output_compression' ) ) ) {
            $isGzip = false;

        }
        if (function_exists('apache_get_modules')) {
            if ( count( array_intersect(
                    apply_filters( 'upio_up_cache_gzip_apache_mods', ['mod_deflate', 'mod_gzip'] ),
                    apache_get_modules())) > 0 ) {
                $isGzip = true;
            }
        }
        return apply_filters( 'upio_up_cache_gzip_enable', $isGzip );
    }
}
