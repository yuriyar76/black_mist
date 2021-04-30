 <?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!empty($_GET['org_inv'])){

    $data = json_decode($_SESSION['DataInvoicePay'], true);
    //print_r($data);
    $number_inv = $data['Number_inv'];
    $number_z = $data['Number_inv_z'];
    $summ = $data['Sum'];
    if($number_inv && !$number_z){
        $number = $number_inv;
    }elseif(!$number_inv && $number_z){
        $number = $number_z;
    }elseif($number_inv && $number_z){
        $number =  $number_inv.'/'.$number_z;
    }
    $arResult['number_inv'] = $number_inv;
    $arResult['number_z'] = $number_z;
    $arResult['summ'] = $summ;
    $arResult['number'] = $number;

    $this->IncludeComponentTemplate();
}


?>

