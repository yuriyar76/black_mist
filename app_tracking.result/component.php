<?php
$app_tracking = $_SERVER['DOCUMENT_ROOT'].'/bitrix/_black_mist/app_tracking.php';
if (!file_exists($app_tracking)) return;
require_once($app_tracking);
if (!class_exists('app_tracking')) return;
$app = new app_tracking();
if (!method_exists($app, 'show_form')) return;
$app->show_form();
if (!method_exists($app, 'show_result')) return;
$app->show_result();
?>