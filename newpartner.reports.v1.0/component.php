<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
die();
}

include($_SERVER['DOCUMENT_ROOT'] . "/bitrix/components/black_mist/delivery.packages/functions.php");
include($_SERVER['DOCUMENT_ROOT'] . "/bitrix/components/black_mist/delivery.packages/ClientReports/ClientReports.php");

try {
    $result = new ClientReports($_POST, $_GET);
    $result->repoEx();
}catch (Exception $e){
    echo(json_encode(['error' => iconv('windows-1251', 'utf-8', $e->getMessage())]));
    exit();
}



  echo $result->dataJson;
  exit();



