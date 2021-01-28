<?php
$app_1c_new2 = $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/app_1c_new2.php';
if (!file_exists($app_1c_new2)) return;
require_once($app_1c_new2);
if (!class_exists('app_1c_new2')) return;
$app = new app_1c_new2();
if (!method_exists($app, 'run')) return;
$app->run();
?>