<?php

$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);

if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
} else {
    if (substr($_SERVER['REQUEST_URI'], 0, 6) === '/cors/') {
        include __DIR__ . '/cors.php';
    }
    else {
        include __DIR__ . '/main.php';
    }
}
