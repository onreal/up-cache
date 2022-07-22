<?php

namespace Upio\UpCache\Helpers;

class CacheManagment
{
    public function clearCacheAdmin()
    {
        $this->clearCache();
        wp_safe_redirect(esc_url_raw(wp_get_referer()));
        exit;
    }

    public function clearCache(): bool
    {
        $uploads_directory_path = wp_get_upload_dir();

        if (file_exists($uploads_directory_path['basedir'] . '/up-cache/')) {
            $this->deleteCache($uploads_directory_path['basedir'] . '/up-cache/');
            return true;
        }

        return false;
    }

    private function deleteCache($dir)
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $this->deleteCache($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }
}