<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

if ($path !== '/' && file_exists($file) && !is_dir($file)) {
    if (substr($file, -4) === '.php') {
        require $file;
        return true;
    }
    return false;
}

return false;
