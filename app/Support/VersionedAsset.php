<?php

namespace App\Support;

class VersionedAsset
{
    // The file's change time in the URL makes browsers fetch a fresh copy after each deploy instead of a cached one.
    public static function url(string $path): string
    {
        $file = public_path($path);

        return asset($path) . (is_file($file) ? '?v=' . filemtime($file) : '');
    }
}
