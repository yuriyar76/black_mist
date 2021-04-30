<?php

require __DIR__ . '/vendor/autoload.php';

spl_autoload_register(function ($class) {
    $file =  $_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/black_mist/' . __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }

});

