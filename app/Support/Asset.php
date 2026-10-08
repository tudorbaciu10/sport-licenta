<?php

namespace App\Support;

/** URL of a file in public/ with ?v=<last modified time>, so browsers never keep a stale CSS/JS copy. */
class Asset
{
    public static function url(string $path): string
    {
        $file = public_path($path);

        return asset($path).(is_file($file) ? '?v='.filemtime($file) : '');
    }
}
