<?php

use App\Models\FormRepository;


function cRupiah($number)
{
    return number_format($number, 0, ',', '.');
}


if (!function_exists('public_path')) {
    function public_path($path = '')
    {
        return env('PUBLIC_PATH', base_path('public')) . ($path ? '/' . $path : $path);
    }
}

if (!function_exists('asset')) {
    function asset($path, $secure = null)
    {
        return app('url')->asset($path, $secure);
    }
}

function getRepoType($id)
{
    return FormRepository::find($id);
}

function CheckExtensionImage(string $filename, int $offset = 0): bool
{
    $file = strtolower($filename);

    $exclude = [
        "zip",
        "xml",
        "bat",
        "jar",
        "py",
        "css",
        "html",
        "htm",
        "js",
        "jsp",
        "xhtml",
        "script",
        "java",
        "php",
    ];

    foreach ($exclude as $needle) {
        if (strpos($file, $needle, $offset) !== false) {
            return true;
        }
    }
    return false;
}

function valEmail($email)
{
    $replace = str_replace([' ', '\r', '\n', '\t'], "", $email);
    $lower = strtolower($replace);

    return $lower;
}
