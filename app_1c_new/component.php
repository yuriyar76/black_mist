<?php
$app_1c_new = $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/app_1c_new.php';
if (!file_exists($app_1c_new)) return;
require_once($app_1c_new);
if (!class_exists('app_1c_new')) return;
$app = new app_1c_new();
if (!method_exists($app, 'run')) return;
$app->run();
?>