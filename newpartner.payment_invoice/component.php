<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arResult = json_decode($_SESSION['DataInvoicePay'], true);
foreach($arResult as $key=>$value){
    $arResult[$key] = htmlspecialchars(strip_tags(trim($value)));
}
if($arResult['Number_inv'] && !$arResult['Number_inv_z']){
    $number =$arResult['Number_inv'];
}elseif(!$arResult['Number_inv'] && $arResult['Number_inv_z']){
    $number = $arResult['Number_inv_z'];
}elseif($arResult['Number_inv'] && $arResult['Number_inv_z']){
    $number =  $arResult['Number_inv'].'/'.$arResult['Number_inv_z'];
}

$arResult['number'] = $number;
//dump($arResult);
$this->IncludeComponentTemplate();?>

