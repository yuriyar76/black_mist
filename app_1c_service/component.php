<?php
$app_1c_service = $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/app_1c_service.php';
if (!file_exists($app_1c_service)) return;
require_once($app_1c_service);
if (!class_exists('app_1c_service')) return;
$app = new app_1c_service();
if (!method_exists($app, 'run')) return;
$app->run();
?>