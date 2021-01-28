<?php
$app_1c_manifestos = $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/app_1c_manifestos.php';
if (!file_exists($app_1c_manifestos)) return;
require_once($app_1c_manifestos);
if (!class_exists('app_1c_manifestos')) return;
$app = new app_1c_manifestos();
if (!method_exists($app, 'run')) return;
$app->run();
?>